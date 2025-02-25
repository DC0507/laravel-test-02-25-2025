<?php

namespace App\Models\Storyblok;

class Component
{

    public static function factory($data)
    {
        if(!is_array($data))
        {
            return $data;
        }

        if(array_key_exists('component', $data))
        {
            // this is a component
            $ret = new self($data);
        }
        else
        {
            array_walk_recursive($data, [self, 'factory']);
            return $data;
        }
    }
}