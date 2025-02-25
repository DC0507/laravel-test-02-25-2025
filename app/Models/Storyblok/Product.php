<?php

namespace App\Models\Storyblok;

use App\Models\Salsify\Asset;
use App\Models\Salsify\Salsify;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class Product
 * @package App\Models\Storyblok
 */
class Product extends \ArrayObject
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $uuid;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var string
     */
    public $updated_at;

    /**
     * @var string
     */
    public $published_at;

    /**
     * @var string
     */
    public $full_slug;

    /**
     * @var string
     */
    public $content;

    /**
     * Product constructor.
     * @param array $input
     * @param int $flags
     * @param string $iterator_class
     */
    public function __construct($input = array(), int $flags = 0, string $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * Override getter
     *
     * @param string $k
     * @return mixed|null
     */
    public function __get($k)
    {
        if(property_exists($this, $k))
        {
            return $this->{$k};
        }
        if($this->offsetExists($k))
        {
            return $this->offsetGet($k);
        }
        return null;
    }

    /**
     * Get value of a property if present, otherwise default value.
     * @param $k
     * @param null $def
     * @return mixed|null
     */
    public function get($k, $def=null)
    {
        $ret = $this->{$k};
        if(is_null($ret))
        {
            $ret = $def;
        }
        return $ret;
    }

    /**
     * Get an empty array of the product template. This method is for dev use only.
     *
     * @return array
     */
    public static function storyTemplate()
    {
        return [
            'component' => config('middleware.storyblok.product_component_name'),
            'sku' => 'Identifier for the product sans-size',
            'category' => 'Primary category name',
            'flavor' => 'Secondary category name',
            'name' => 'Product name',
            'description' => 'Description copy, plain text.',
            'category_position' => 'Position in primary category',
            'subcategory_position' => 'Position in secondary category',

            'images' => [],

            // Array of size names (aligns with ingredients.*.size and nutritions.*.size)
            'sizes' => [
                [
                    'component' => 'product_size',
                    'size' => 'Size name',
                    'upc' => '',
                    'uom' => '',
                    'unit_quantity',
                    'pos' => '',
                    'icon' => 'Asset component?',

                    // Array of ingredient structures
                    'ingredients' => [
                        // ordered by relevancy
                        [
                            'component' => 'product_ingredient',
                            'ingredient' => 'Text name of a single ingredient',
                        ]
                    ],

                    'serving_size' => '',
                    'calories' => '',
                    'servings_per_container' => '',
                    'nutrition_note' => '',
                    'nutritions' => [
                        [
                            'component' => 'product_nutrition_item',
                            'name' => '',
                            'value' => '',
                            'uom' => '',
                            'dv' => '',
                            'relevant' => true,
                        ]
                    ]

                ]
            ],

            // Array of claim boolean fields
            'claims' => [
                [
                    'component' => '',

                ]
            ],

            'related_products' => [],
            'related_recipes' => [],
        ];
    }

    /**
     * Factory a Product and populate it from a Salsify\Product object.
     *
     * @param \App\Models\Salsify\Product $product
     * @return Product
     */
    public static function fromSalsifyProduct(\App\Models\Salsify\Product $product)
    {
        $config = config('middleware.field_mappings', []);

        $parts = self::_productToCatSubcatFlavor($product);

        
        return new self([
            'publish' => 1, // immediately publish
            'story' => [
                'parent_id' => env('STORYBLOK_PRODUCT_PARENT_ID'), // @todo Find or create this parent folder
                'slug' => $product->getStoryblokSlug(Arr::get($config, 'slug_field')),
                'full_slug' => config('middleware.storyblok.product_slug_prefix') . $product->getStoryblokSlug(Arr::get($config, 'slug_field')),
//                'position' => 0, // @todo What Salsify field is position

                'name' => $product->get(Arr::get($config, 'name')),

                'content' => [
                    'component' => config('middleware.storyblok.product_component_name'),
                    'category' => $parts['category'],
                    'subcategory' => $parts['subcategory'],
                    'flavor' => $parts['flavor'],
                    'name' => $product->get(Arr::get($config, 'product.name')),
                    'description' => self::toString($product->get(Arr::get($config, 'product.description'))),
//                    'category_position' => $product->get(Arr::get($config, 'product.category_position'), 0),
//                    'subcategory_position' => $product->get(Arr::get($config, 'product.subcategory_position'), 0),

                    'sizes' => [
                        self::_productDataToSize($product),
                    ],
                    'brand_architecture' => $product->get(Arr::get($config, 'product.brand_architecture'), ''),

//                    'related_products' => [],
//                    'related_recipes' => [],
                ],
            ]
        ]);
    }

    private static function toString($var)
    {
        if(!is_array($var)) $var = [$var];
        return implode(" ", Arr::flatten($var));
    }

    /**
     * Extract the category, subcategory, and flavor values from a product object.
     *
     * @param \App\Models\Salsify\Product $product
     * @return array
     */
    protected static function _productToCatSubcatFlavor(\App\Models\Salsify\Product $product)
    {
        $property = config('middleware.field_mappings.product.combined_cat_subcat_flavor', '');
        if(empty($property))
        {
            Log::error("missing config for combined_cat_subcat field");
            $property = 'Website - Category/SubCategory/Flavor';
        }

        $value = trim($product->get($property, ''));
        Log::debug("combined cat/subcat/flavor: {$value}");
        $parts = explode('/', $value);

        return [
            'category' => $parts[0],
            'subcategory' => $parts[1],
            'flavor' => $parts[2],
        ];
    }

    /**
     * Unused.
     *
     * @param array $opts
     * @return \stdClass
     */
    public function getParentProduct($opts=[])
    {
        $ret = new \stdClass();
        $data = $this->getArrayCopy();
        $slug = $data['story']['slug'];
        // @todo How do we fetch the product? This class should NOT creep out to API calls.
        return $ret;
    }

    /**
     * Unused.
     *
     * @param array $opts
     */
    public static function upsertParentProduct($opts=[])
    {
    }

    /**
     * Unused.
     *
     * @return string
     */
    public function getParentProductSlug()
    {
        $slug = '';
        //
        return $slug;
    }


    /**
     * Build the size component, and sub-components, given the Salsify product.
     *
     * @param \App\Models\Salsify\Product $product
     * @return array
     */
    protected static function _productDataToSize(\App\Models\Salsify\Product $product)
    {
        $config = config('middleware.field_mappings.product.sizes', [
            // define some defaults in case config is missing
            'size' => 'Consumer Sellable Unit Description',
            'upc' => 'GTIN',
            'pos' => 'Position',
            'ingredients' => 'Website - Ingredients',
            'serving_size' => 'Serving Size Description',
            'calories' => 'Calories',
            'servings_per_container' => 'Serving Suggestion',
            'smartcommerce_id' => '',
        ]);
        $ret = [
            'component' => 'product_size'
        ];
        try
        {

            $new_data = [
                'size' => $product->get(Arr::get($config, 'size')),
                'material' => $product->get(Arr::get($config, 'material')),
                'sku' => $product->get(Arr::get($config, 'sku')),
                'upc' => $product->get(Arr::get($config, 'upc')),
                'hero_image' => self::_productToHeroImage($product),
                'uom' => Salsify::raw2uom($product->get(Arr::get($config, 'size'))),
                'unit_quantity' => Salsify::raw2uoq($product->get(Arr::get($config, 'size'))),
//                'pos' => Arr::get($config, 'pos'),
                'icon' => '', // @todo Where will this field come from
                'ingredients' => self::_ingredientArrayToComponents($product->get(Arr::get($config, 'ingredients'))),
                'serving_size' => $product->get(Arr::get($config, 'serving_size')),
                'calories' => $product->get(Arr::get($config, 'calories')),
                'servings_per_container' => $product->get(Arr::get($config, 'servings_per_container')),
                'nutrition_note' => '',
                'nutritions' => self::_productDataToNutritionComponents($product),
                'smartcommerce_id' => $product->get(Arr::get($config, 'smartcommerce_id'), ''),
                'description' => self::toString($product->get(Arr::get($config, 'description'), '')),
            ];

            /**
             * filtering here will prevent clobbering existing data w/ empty values
             * This is potentially dangerous as it prevents ever removing values when that might be the intention
             */
            $new_data = array_filter($new_data);

            $ret = array_merge($ret, $new_data);

            // compute the insignificant compounds
            $ret['nutrition_note'] = self::computeNutritionNote($ret['nutritions']);

            // build the claims fields
            $ret = array_merge($ret, self::_productDataToClaims($product));

        }
        catch (\Exception $exception)
        {
            Log::warning(__METHOD__ . " " . $exception->getMessage());
        }

        return $ret;
    }

    /**
     * Find Salsify asset url from our cache for a product.
     *
     * @param \App\Models\Salsify\Product $product
     * @return string
     */
    private static function _productToHeroImage(\App\Models\Salsify\Product $product)
    {
        try
        {
            $hero_image = "";

            $config = config('middleware.field_mappings.product.sizes', [
                'hero_image' => 'Website - Hero Image',
            ]);

            $salsify_id = $product->get(Arr::get($config, 'hero_image'));
            if(is_array($salsify_id))
            {
                // use only the first salsify_id if multiple are provided
                $salsify_id = array_shift($salsify_id);
            }
            // find the asset in our cache
            $asset = Asset::where('salsify_id', $salsify_id)->first();
            if($asset)
            {
                $parts = explode('/', parse_url($asset->url, PHP_URL_PATH));
                $path = [];
                $path[] = array_pop($parts);
                $path[] = array_pop($parts);
                $hero_image = implode('/', array_reverse($path));
            }

        }
        catch (\Exception $exception)
        {
            Log::error("could not determine hero_image for {$product->getStoryblokSlug()}");
            Log::error($exception->getMessage());
        }

        return $hero_image;
    }

    /**
     * Get comma separated string of all insignificant nutritional ingredients found in the array of product_nutrition_item structs
     *
     * @param array $nutritions
     * @return string
     */
    public static function computeNutritionNote($nutritions=[])
    {
        $parts = [];
        foreach($nutritions as $item)
        {
            if($item['relevant'])
            {
                Log::debug(__METHOD__ . " {$item['name']} is not relevant");
                continue;
            }
            if($item['hide_when_empty'])
            {
                Log::debug(__METHOD__ . " omitting {$item['name']} from insignificant message");
                continue;
            }
            $parts[] = $item['name'];
        }
        return implode(", ", $parts);
    }


    /**
     * Given an array of ingredients delimited by line breaks, return an array of product_ingredient structs.
     *
     * @param $ary
     * @return array
     */
    protected static function _ingredientArrayToComponents($ary)
    {
        $ret = [];
        $stack = [];
        $ary = self::_assembleIngredientsArrayWithParens($ary);
        try {
            foreach($ary as $line)
            {
                $line = trim($line);
                $line = preg_replace('/[\r\n]+/', ' ', $line);
                $line = preg_replace('/[\ ]+/', ' ', $line);

                if(in_array($line, $stack)) continue;
                $ret[] = [
                    'component' => 'product_ingredient',
                    'ingredient' => $line,
                ];
                $stack[] = $line;
            }
        }
        catch (\Exception $exception)
        {
            Log::warning(__METHOD__ . " " . $exception->getMessage());
        }

        return $ret;
    }

    /**
     * Reconstruct the array of ingredients, one per line, while maintaining parenthesized groups.
     *
     * @param $ary
     * @return array
     */
    private static function _assembleIngredientsArrayWithParens($ary)
    {
        $ret = [];
        $paren = false;
        $next = "";
        foreach($ary as $line)
        {
            if(preg_match('/\([^)]*$/', $line))
            {
                $paren = true;
            }

            if(!$paren)
            {
                $ret[] = $line;
            }
            else
            {
                $next .= ($next?', ':'') . $line;
                if(preg_match('/\)[^(]*$/', $line))
                {
                    $paren = false;
                    $ret[] = $next;
                    $next = "";
                }
            }
        }
        return $ret;
    }

    /**
     * Given a string of ingredients delimited by line breaks, return an array of product_ingredient structs.
     *
     * @param $str
     * @return array
     */
    protected static function _ingredientStringToComponents($str)
    {
        $ret = [];
        try {
            foreach(preg_split('/[\r\n]+/', $str) as $line)
            {
                $ret[] = [
                    'component' => 'product_ingredient',
                    'ingredient' => trim($line),
                ];
            }
        }
        catch (\Exception $exception)
        {
            Log::warning(__METHOD__ . " " . $exception->getMessage());
        }

        return $ret;
    }

    /**
     * Build an array of product_nutrition_item structs with the `relevant` property computed for the given product.
     *
     * @param \App\Models\Salsify\Product $product
     * @return array
     */
    protected static function _productDataToNutritionComponents(\App\Models\Salsify\Product $product)
    {
        $ret = [];

        $config = config('middleware.field_mappings.nutrition.unit_fields', []);

        try
        {
            // collect each nutrition field specified in the config
            foreach($config as $name => $field_map)
            {
                $field_map = array_merge([
                    'hide_when_empty' => false,
                ], $field_map);

                // build the component
                // @todo Convert UOM with lookup table
                $next = [
                    'component' => 'product_nutrition_item',
                    'name' => $name,
                    'value' => $product->get($field_map['value']),
                    // @todo map this value via Salsify::
                    'uom' => Salsify::mapUomToReadable($product->get($field_map['uom'])),
                    'dv' => $product->get($field_map['dv']),
                ];

                // mark non-ignorable fields as always relevant, otherwise if the value is empty mark as not relevant
                $next['relevant'] = !$field_map['ignorable'] ? true : !empty($next['value']);
                $next['hide_when_empty'] = !$next['relevant'] && $field_map['hide_when_empty'];

                $ret[] = $next;
            }
        }
        catch (\Exception $exception)
        {
            Log::warning(__METHOD__ . " " . $exception->getMessage());
        }

        return $ret;
    }

    /**
     * Create array of claim boolean values from Salsify claim properties.
     *
     * @param \App\Models\Salsify\Product $product
     * @return array
     */
    protected static function _productDataToClaims(\App\Models\Salsify\Product $product)
    {
        $ret = [];
        try
        {
            /**
             * array keyed by Storyblok property name, values are Salsify property names
             * ex: [
             *  'claim_gluten_free' => 'Website - Gluten Free Claim',
             *  'claim_cane_sugar' => 'Website - Made with Real Cane Sugar Claim',
             * ]
             */
            $claim_properties = config('middleware.field_mappings.product.sizes.claims', []);

            // toggle Storyblok claim properties on/off based on Salsify properties
            foreach($claim_properties as $component_property => $salsify_property)
            {
                $ret[$component_property] = self::boolval($product->get($salsify_property, false));
                Log::debug("claim: {$component_property} {$salsify_property}:{$product->get($salsify_property, false)} result:{$ret[$component_property]}");
            }

            // store array of Storyblok property names that are marked as primary (and set true on the product)
            $primary_claims_array = $product->get(config('middleware.field_mappings.product.sizes.primary_claims'), []);
            if(is_string($primary_claims_array))
            {
                $primary_claims_array = [$primary_claims_array];
            }
            $ret['primary_claims'] = array_keys(array_filter(array_intersect($claim_properties, $primary_claims_array), function($claim_property) use ($product){
                return $product->get($claim_property, false);
            }));

            Log::debug("primary_claims: " . json_encode($ret['primary_claims']));
        }
        catch (\Exception $exception)
        {
            Log::warning(__METHOD__ . " " . $exception->getMessage());
        }
        return $ret;
    }

    /**
     * Helper method to coerce a boolean from a value.
     *
     * @param $val
     * @return bool
     */
    private static function boolval($val)
    {
        if(is_bool($val)) return $val;
        if(is_string($val))
        {
            return trim(strtolower($val)) == 'yes';
        }
        return (bool) $val;
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
     * @param Product $product
     * @return $this
     */
    public function mergeProduct(Product $product)
    {
        $ignore = ['sizes', 'claims', 'related_products', 'related_recipes', 'images'];

        // get the existing story.content data
        $old_content = Arr::get($this->getArrayCopy(), 'story.content', []);

        // get the new story.content data
        $new_content = Arr::get($product->getArrayCopy(), 'story.content', []);

        // merge top level data only, treat nested elements specially
        $old_content = array_replace($old_content, Arr::except($new_content, $ignore));

        if(Arr::has($new_content, 'sizes'))
        {
            $old_content['sizes'] = self::mergeSizes(Arr::get($old_content, 'sizes', []), $new_content['sizes']);
        }

        if(Arr::has($new_content, 'claims'))
        {
            $old_content['claims'] = self::mergeClaims(Arr::get($old_content, 'claims', []), $new_content['claims']);
        }

        if(Arr::has($new_content, 'images'))
        {
            $old_content['images'] = self::mergeImages(Arr::get($old_content, 'images', []), $new_content['images']);
        }

        if(Arr::has($new_content, 'related_products'))
        {
            $old_content['related_products'] = $new_content['related_products'];
        }

        if(Arr::has($new_content, 'related_recipes'))
        {
            $old_content['related_recipes'] = $new_content['related_recipes'];
        }

        $this['story']['content'] = $old_content;

        return $this;
    }

    public function setDefaults()
    {
        Log::debug(__METHOD__ . " setting defaults for {$this['story']['slug']}");

        // set a default_size if none exist
        if(Arr::accessible($this, 'story.content.sizes'))
        {
            $haveDefault = "";
            foreach(Arr::get($this, 'story.content.sizes') as $k => $size)
            {
                if(array_key_exists('default_size', $size) && $size['default_size'])
                {
                    $haveDefault = $size['size'];
                    Log::debug(__METHOD__ . " {$this['story']['slug']} already has default size: {$haveDefault}");
                    break;
                }
            }

            if(empty($haveDefault))
            {
                Log::debug(__METHOD__ . " attempting to determine a suitable default size for {$this['story']['slug']}");
                foreach(Arr::get($this, 'story.content.sizes') as $k => $size)
                {
                    // find first size w/ a non-empty hero_image
                    if(array_key_exists('hero_image', $size) && !empty($size['hero_image']))
                    {
                        // mark it default, break loop
                        Arr::set($this, "story.content.sizes.{$k}.default_size", 1);
                        Log::debug(__METHOD__ . " setting {$size['size']} as default");
                        $haveDefault = $size['size'];
                        break;
                    }
                }
                if(empty($haveDefault))
                {
                    Log::debug(__METHOD__ . " could not determine a suitable default size for {$this['story']['slug']}");
                }
            }
        }

    }

    /**
     * @param array $old_sizes
     * @param array $new_sizes
     * @return array
     */
    protected static function mergeSizes($old_sizes=[], $new_sizes=[])
    {

        foreach($new_sizes as $new_size)
        {
            // matched new size to an existing size
            $found = false;

            foreach($old_sizes as $k => $old_size)
            {


                $hasMaterial = !empty($new_size['material']);
                $materialMatch = $hasMaterial && $old_size['material'] == $new_size['material'];
                $sizeMatch = $old_size['size'] == $new_size['size'];

                if( $sizeMatch && ( $materialMatch || !$hasMaterial ) )
                {
                    Log::debug(__METHOD__ . " merging size: {$old_size['size']}");
                    $old_sizes[$k] = self::mergeSize($old_size, $new_size);
                    $found = true;
                }
            }

            if(!$found)
            {
                // if no match found, append the new size
                $old_sizes[] = $new_size;
                Log::debug(__METHOD__ . " appending new size");
            }
        }

        return $old_sizes;
    }

    /**
     * Merge specific properties of new_size onto old_size.
     *
     * @param array $old_size
     * @param array $new_size
     * @return array
     */
    protected static function mergeSize($old_size=[], $new_size=[])
    {
//        Log::debug(__METHOD__ . " merging a size");
        Log::debug(__METHOD__ . " merging new_size: " . json_encode($new_size, JSON_PRETTY_PRINT));
        Log::debug(__METHOD__ . " merging old_size: " . json_encode($old_size, JSON_PRETTY_PRINT));
        $ignore = ['ingredients', 'nutritions', 'primary_claims'];

        // merge everything except some fields to be handled below
        $old_size = array_replace_recursive($old_size, Arr::except($new_size, $ignore));

        if(Arr::has($new_size, 'ingredients'))
        {
//            $old_size['ingredients'] = self::mergeIngredients(Arr::get($old_size, 'ingredients', []), Arr::get($new_size, 'ingredients'));
            $old_size['ingredients'] = $new_size['ingredients'];
        }

        if(Arr::has($new_size, 'nutritions'))
        {
//            $old_size['nutritions'] = self::mergeIngredients(Arr::get($old_size, 'nutritions', []), Arr::get($new_size, 'nutritions'));
            $old_size['nutritions'] = $new_size['nutritions'];
        }

        if(Arr::has($new_size, 'primary_claims'))
        {
            $old_size['primary_claims'] = $new_size['primary_claims'];
            Log::debug("setting primary claims: " . json_encode($new_size['primary_claims']));
        }
        else
        {
            Log::debug("no primary_claims in new_size");
        }

        Log::debug(__METHOD__ . " merge result: " . json_encode($old_size, JSON_PRETTY_PRINT));

        return $old_size;
    }

    /**
     * @param array $old_nutritions
     * @param array $new_nutritions
     * @return array
     */
    protected static function mergeNutritions($old_nutritions=[], $new_nutritions=[])
    {
        return $old_nutritions;
    }

    /**
     * @param array $old_ingredients
     * @param array $new_ingredients
     * @return array
     */
    protected static function mergeIngredients($old_ingredients=[], $new_ingredients=[])
    {
        return $old_ingredients;
    }

    /**
     * @param array $old_claims
     * @param array $new_claims
     * @return array
     */
    protected static function mergeClaims($old_claims=[], $new_claims=[])
    {
        return $new_claims;
    }

    /**
     * @param array $old_images
     * @param array $new_images
     * @return array
     */
    protected static function mergeImages($old_images=[], $new_images=[])
    {
        return $old_images;
    }



}
