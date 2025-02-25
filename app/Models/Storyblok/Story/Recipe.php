<?php

namespace App\Models\Storyblok;

use Illuminate\Support\Arr;

class Story_Recipe extends Story
{

    public function getTitle()
    {
        return Arr::get($this->_content, 'title');
    }

    public function getDescription()
    {
        return Arr::get($this->_content, 'description');
    }

    public function getImage()
    {
        return Arr::get($this->_content, 'hero_image.filename');
    }

    public function getType()
    {
        return $this->getRootComponentType();
    }

    public function toAlgoliaRecord()
    {
        return array_merge(parent::toAlgoliaRecord(), [
            'total_time' => $this->getTotalTime(),
            'category' => $this->getCategory(),
            'recipe_category' => $this->getCategory(),
            'preparation' => $this->getPreparation(),
            'recipe_ingredients' => $this->getIngredients(),
        ]);
    }

    public function getTotalTime()
    {
        return Arr::get($this->_content, 'total_time', null);
    }

    public function getCategory()
    {
        return Arr::get($this->_content, 'category');
    }


    public function getPreparation()
    {
        $ret = [];

        foreach(Arr::get($this->_content, 'preparations', []) as $set)
        {
            foreach(Arr::get($set, 'preparations', []) as $item)
            {
                $ret[] = Arr::get($item, 'preparation', '');
            }
        }

        return array_values($ret);
    }

    public function getIngredients()
    {
        $ret = [];
        foreach(Arr::get($this->_content, 'ingredients', []) as $set)
        {
            foreach(Arr::get($set, 'ingredients', []) as $item)
            {
                $ret[] = Arr::get($item, 'ingredient', '');
            }
        }
        return array_values(array_unique($ret));
    }
}