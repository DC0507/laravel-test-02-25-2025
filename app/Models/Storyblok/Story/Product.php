<?php

namespace App\Models\Storyblok;

use Illuminate\Support\Arr;

class Story_Product extends Story
{

    public function getTitle()
    {
        return Arr::get($this->_content, 'name');
    }

    public function getDescription()
    {
        if($this->isSnack())
        {
            return Arr::get($this->_content, 'meta.description') . ' ' . self::_richTextToString(Arr::get($this->_content, 'snack_description'));
        }

        return Arr::get($this->_content, 'description');
    }

    protected static function _richTextToString($ary = [])
    {
        try
        {
            if(!is_array($ary))
            {
                throw new \Exception(__METHOD__ . ": \$ary parameter is not an array");
            }

            $ret = [];
            foreach(Arr::dot($ary) as $k => $v)
            {
                if(!preg_match('/\.text$/', $k)) continue;
                $ret[] = $v;
            }
            return implode("\n", $ret);
        }
        catch (\Exception $exception)
        {
            Log::debug($exception->getMessage());
            return "";
        }
    }

    public function getImage()
    {
        if($this->isSnack())
        {
            return Arr::get($this->_content, 'snack_hero_asset.filename');
        }

        if(Arr::has($this->_content, 'sizes'))
        {
            foreach(Arr::get($this->_content, 'sizes', []) as $size)
            {
                if(Arr::has($size, 'default_size') && $size['default_size'])
                {
                    return "https://img.web.welchs.com/{$size['hero_image']}";
                }
            }
        }

        return parent::getImage();
    }

    public function getType()
    {
        return $this->getRootComponentType();
    }

    public function toAlgoliaRecord()
    {
        return array_merge(parent::toAlgoliaRecord(), [
            'claims' => $this->getClaims(),
            'product_ingredients' => $this->getIngredients(),
            'sizes' => $this->getSizes(),
            'featured' => Arr::get($this->_content, 'featured', false),
            'subfeatured' => Arr::get($this->_content, 'subfeatured', false),
            'category' => Arr::get($this->_content, 'category'),
            'product_category' => Arr::get($this->_content, 'category'),
            'subcategory' => Arr::get($this->_content, 'subcategory'),
            'product_subcategory' => Arr::get($this->_content, 'subcategory'),
            'category_ranking' => (int) $this->getCategoryRanking(),
            'category_position' => (int) Arr::get($this->_content, 'category_position', 0),
            'subcategory_position' => (int) Arr::get($this->_content, 'subcategory_position', 0),
        ]);
    }

    /**
     * Is this a snack product
     *
     * @return boolean
     */
    public function isSnack()
    {
        return Arr::get($this->_content, 'is_snack_product', false);
    }

    /**
     * Get array of claims made using the Salsify property names.
     *
     * @return array
     */
    protected function getClaims()
    {
        $names = config('middleware.field_mappings.product.sizes.claims', []);
        $sizes = Arr::get($this->_content, 'sizes');
        $claims = [];

        // capture all the true claims
        foreach($sizes as $size)
        {
            foreach($size as $prop => $value)
            {
                if(strpos($prop, 'claim_') !== 0) continue;
                if(!$value) continue;
                $claims[$prop] = Arr::get($names, $prop, $prop);
            }
        }

        return array_values($claims);
    }

    /**
     * Get list of unique ingredient names across all sizes.
     *
     * @return array
     */
    public function getIngredients()
    {
        $ret = [];
        foreach(Arr::get($this->_content, 'sizes', []) as $size)
        {
            foreach(Arr::get($size, 'ingredients', []) as $item)
            {
                $ret[] = Arr::get($item, 'ingredient', '');
            }
        }
        return array_unique($ret);
    }

    public function getSizes()
    {
        $ret = [];
        foreach(Arr::get($this->_content, 'sizes', []) as $size)
        {
            $ret[] = Arr::get($size, 'size');
        }

        return array_unique($ret);
    }

}
