<?php

namespace App\Models\Storyblok;

use Illuminate\Support\Arr;

class Story_Page extends Story
{

    public function getTitle()
    {
        return Arr::get($this->_content, 'meta.og_title');
    }

    public function getDescription()
    {
        return Arr::get($this->_content, 'meta.og_description');
    }

    public function getImage()
    {
        return Arr::get($this->_content, 'meta.og_image');
    }

    public function toAlgoliaRecord()
    {
        return array_merge(parent::toAlgoliaRecord(), $this->_content);
    }
}