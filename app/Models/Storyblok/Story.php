<?php

namespace App\Models\Storyblok;

use Illuminate\Support\Arr;

class Story
{

    public $name;
    public $id;
    public $uuid;
    public $full_slug;
    public $parent_id;
    public $slug;
    public $path;

    public $created_at;
    public $updated_at;
    public $first_published_at;

    public $alternates = [];

    public $default_full_slug;
    public $position;
    public $sort_by_date;
    public $tag_list;
    public $is_startpage;

    public $meta_data;
    public $group_id;
    public $release_id;
    public $lang;
    public $translated_slugs;

    public $content;
    public $_content;

    public function __construct($data)
    {

        $keys = [
            'name' => '',
            'id' => null,
            'uuid' => null,
            'full_slug' => '',
            'parent_id' => null,
            'slug' => '',
            'path' => '',

            'created_at' => null,
            'updated_at' => null,
            'first_published_at' => null,

            'alternates' => [],

            'default_full_slug' => '',
            'position' => 0,
            'sort_by_date' => false,
            'tag_list' => [],
            'is_startpage' => false,

            'meta_data' => null,
            'group_id' => null,
            'release_id' => null,
            'lang' => null,
            'translated_slugs' => '',
        ];

        foreach($keys as $k => $def)
        {
            $this->{$k} = Arr::get($data, $k, $def);
        }

        $this->_content = Arr::get($data, 'content');
        $this->content = Arr::flatten($this->_content);
    }

    public static function factory($data)
    {
        $component = Arr::get($data, 'content.component', 'page');

        switch ($component)
        {
            case 'product' : return new Story_Product($data);
            case 'recipe' : return new Story_Recipe($data);
            case 'page' : return new Story_Page($data);
        }

        return new self($data);
    }

    public function getTitle()
    {
        return Arr::get($this->content, 'title');
    }

    public function getDescription()
    {
        return Arr::get($this->content, 'meta.og_description');
    }

    public function getImage()
    {
        return Arr::get($this->content, 'meta.og_image');
    }

    public function getUrl()
    {
        return $this->full_slug;
    }

    public function getType()
    {
        return 'page';
    }

    public function getRootComponentType()
    {
        return Arr::get($this->_content, 'component');
    }

    public function isIgnored()
    {
        return in_array('ignore', Arr::get($this->_content, 'tag_list', []));
    }

    public function toAlgoliaRecord()
    {
        return [
            'objectID' => $this->getUrl(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'image' => $this->getImage(),
            'url' => $this->getUrl(),
            'search_ranking' => (float) Arr::get($this->_content, 'search_ranking', 0.0),
            'tags' => $this->tag_list,
        ];
    }

    public function getCategoryRanking()
    {
        $config = config('algolia.rankings.category_ranking', []);
        return Arr::get($this->_content, 'category_ranking', array_search(Arr::get($this->_content, 'category'), $config)+1);
    }

    public function getSearchRanking()
    {
        return (float) Arr::get($this->_content, 'search_ranking', 0.0);
    }

}