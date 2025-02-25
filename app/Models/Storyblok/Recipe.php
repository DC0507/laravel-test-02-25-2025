<?php

namespace App\Models\Storyblok;

use App\Models\Salsify\Salsify;
use Illuminate\Support\Facades\Log;

class Recipe extends \ArrayObject
{
//    public $id;
//    public $uuid;
//    public $slug;
//    public $created_at;
//    public $updated_at;
//    public $published_at;
//    public $full_slug;
//    public $content;

    /**
     * Derive recipe slug from the drupal data.
     *
     * @param $data
     * @return string
     */
    public static function getSlugFromData(&$data)
    {
        if(
            !array_key_exists('attributes', $data) ||
            !is_array($data['attributes']) ||
            !array_key_exists('path', $data['attributes']) ||
            !is_array($data['attributes']['path']) ||
            !array_key_exists('alias', $data['attributes']['path']) ||
            !is_string($data['attributes']['path']['alias'])
        )
        {
            dd($data);
            Log::debug("attributes/path/alias key not found in \$data");
            return "";
        }
        return preg_replace('/^.*\//', '', $data['attributes']['path']['alias']);
    }

    /**
     * Merge an array into this object.
     *
     * @param array $data
     * @return $this
     */
    public function merge($data = [])
    {
        $this->exchangeArray(array_replace_recursive($this->getArrayCopy(), $data));
        return $this;
    }

    /**
     * Factory a Recipe object from drupal data.
     * @param array $data
     * @return Recipe
     */
    public static function fromDrupalData($data = [])
    {
        return new self([
            'publish' => intval($data['attributes']['status']),
            'story' => [
                'parent_id' => env('STORYBLOK_RECIPE_PARENT_ID'),
                'name' => (string) $data['attributes']['title'],
                'slug' => preg_replace('/^.*\//', '', $data['attributes']['path']['alias']),
                'full_slug' => 'recipes' . $data['attributes']['path']['alias'],
                'disable_fe_editor' => 1,

                'content' => [
                    'component' => 'recipe',
                    'drupal_id' => $data['id'],
                    'title' => (string) $data['attributes']['title'],
                    'description' => (string) $data['attributes']['field_description'],
                    'promote' => (bool) $data['attributes']['promote'],
                    'sticky' => (bool) $data['attributes']['sticky'],
                    'ingredients' => self::_ingredientsToComponents($data['attributes']['field_recipe_ingredients']['processed']),
                    'preparations' => self::_preparationToComponents($data['attributes']['field_preparation']['processed']),
                    'nutritions' => self::_nutritionToComponents($data['attributes']['field_recipe_nutritional_informa']['value']),
                    'metatags' => (string) $data['attributes']['field_metatags'],
                    'chill_time' => (string) $data['attributes']['field_chill_time'],
                    'cook_time' => (string) $data['attributes']['field_cook_time'],
                    'heart_healthy' => (bool) $data['attributes']['field_heart_healthy'],
                    'duration' => (string) $data['attributes']['field_duration'],
                    'preparation_time' => (string) $data['attributes']['field_preparation_time'],
                    'servings' => (string) $data['attributes']['field_servings'],
                    'total_time' => (string) $data['attributes']['field_total_time'],
                    'unit_of_time' => (string) $data['attributes']['field_unit_of_time'],
                    'status' => (bool) $data['attributes']['status'],

                    'featured_products' => [],
                    'related_recipes' => [],
                ]
            ]
        ]);
    }

    /**
     * Convert html ingredients string into nested component structure.
     *
     * @param $ingredients_string
     * @return array
     */
    protected static function _ingredientsToComponents($ingredients_string)
    {

        $ret = []; // build this return value

        try
        {
            // wrap string in single root element
            $xml = new \SimpleXMLElement(sprintf('<ingredients>%s</ingredients>', $ingredients_string));

            // find all h4 els
            $headers = $xml->xpath('//h4');

            // find all ul els
            $uls = $xml->xpath('//ul');


            foreach($uls as $k => $ul)
            {
                $next = [
                    'component' => 'recipe_ingredients',
                    'title' => array_key_exists($k, $headers) ? $headers[$k]->__toString() : "",
                    'ingredients' => []
                ];

                foreach($ul->xpath('li') as $li)
                {
                    $additional = "";
                    foreach($li->xpath("a") as $_a) $additional .= $_a->asXML() . " " ;
                    $next['ingredients'][] = [
                        'component' => 'recipe_ingredient',
                        'ingredient' => strip_tags($li->asXML()) . implode("\n", array_map(function($node){return strip_tags($node->asXml());}, $li->xpath('a'))),
                    ];
                }

                $ret[] = $next;
            }
        }
        catch (\Exception $e)
        {
            Log::warning("error parsing ingredients: " . $e->getMessage());
        }

        return $ret;
    }

    /**
     * Convert preparation html string into nested component structure.
     *
     * @param $preparation_string
     * @return array
     */
    protected static function _preparationToComponents($preparation_string)
    {
        $ret = [];
        try
        {
            $xml = new \SimpleXMLElement(sprintf('<preparation>%s</preparation>', $preparation_string));

            $ols = $xml->xpath('//ol');

            $ps = $xml->xpath('/preparation/p');

            $headers = $xml->xpath('//h4');

            foreach($ols as $k => $ol)
            {
                $next = [
                    'component' => 'recipe_preparations',
                    'title' => array_key_exists($k, $headers) ? $headers[$k]->__toString() : '',
                    'notes' => array_key_exists($k, $ps) ? strip_tags($ps[$k]->asXML()) : '',
                    'preparations' => [],
                ];

                foreach($ol->xpath('//li') as $li)
                {
                    $next['preparations'][] = [
                        'component' => 'recipe_preparation',
                        'preparation' => strip_tags($li->asXML()) . " " . implode("\n", array_map(function($node){return strip_tags($node->asXml());}, $li->xpath('a')))
                    ];
                }

                $ret[] = $next;
            }

        }
        catch (\Exception $e)
        {
            Log::warning("Error parsing preparation: " . $e->getMessage());
        }

        return $ret;
    }

    /**
     * Convert html of nutrition info to component structure.
     *
     * @param $nutrition_string
     * @return array
     */
    protected static function _nutritionToComponents($nutrition_string)
    {
        // the return value
        $ret = [];

        try
        {
            $xml = new \SimpleXMLElement(sprintf('<nutrition>%s</nutrition>', $nutrition_string));

            $uls = $xml->xpath('//ul');

            $ps = $xml->xpath('/nutrition/p');

            $headers = $xml->xpath('//h5');

            foreach($uls as $k => $ul)
            {
                $next = [
                    'component' => 'recipe_nutritions',
                    'serving_size' => array_key_exists($k, $headers) ? strip_tags($headers[$k]->asXML()) : '',
                    'nutrition_note' => array_key_exists($k, $ps) ? strip_tags($ps[$k]->asXML()) : '',
                    'nutritions' => [],
                ];

                foreach($ul->xpath('//li') as $li)
                {
                    $next['nutritions'][] = array_merge([
                        'component' => 'recipe_nutrition',
                    ], self::_nutritionStringToComponent(strip_tags($li->asXML())));
                }

                $ret[] = $next;
            }

        }
        catch (\Exception $e)
        {
            Log::warning("Error parsing preparation: " . $e->getMessage());
        }

        return $ret;
    }

    /**
     * Split string like "4.5g total fat" into a component structure.
     *
     * @param $str
     * @return array
     */
    protected static function _nutritionStringToComponent($str)
    {
        // replace multiple spaces with one
        $parts = explode(" ", preg_replace('/[\ ]+/', ' ', trim($str)), 2);

        return [
            'unit_of_measure' => Salsify::raw2uom($parts[0]),
            'unit_quantity' => Salsify::raw2uoq($parts[0]),
            'nutrition' => $parts[1],
        ];
    }

    protected static function _featuredProductsToComponents($data)
    {}

    protected static function _relatedRecipesToComponents($data)
    {}

    /**
     * Apply raw drupal data by first converting to Storyblok\Recipe then merging on top of $this.
     *
     * @param $data
     * @return Recipe
     */
    public function applyDrupalData($data)
    {
        return $this->merge(self::fromDrupalData($data)->getArrayCopy());
    }

    /**
     * cleanup old fields that are no longer needed or have changed.
     *
     * @return $this
     */
    public function removeCruft()
    {
        // operate on the plain array
        $data = $this->getArrayCopy();

        // sanity check that story[content] is present and an array
        if(array_key_exists('story', $data) && array_key_exists('content', $data['story']) && is_array($data['story']['content']))
        {

            // remove the old preparations field if present
            if(array_key_exists('preparations', $data['story']['content']) && is_array($data['story']['content']['preparations']))
            {
                foreach(array_keys($data['story']['content']['preparations']) as $k)
                {
                    unset($data['story']['content']['preparations'][$k]['preparation']);
                }
            }

            // remove old plain string nutrition info
            if(array_key_exists('nutrition', $data['story']['content']))
            {
                unset($data['story']['content']['nutrition']);
            }

        }

        // swap in the cleaned data array
        $this->exchangeArray($data);

        // make this method chainable
        return $this;
    }

}