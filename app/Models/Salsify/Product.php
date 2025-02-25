<?php

namespace App\Models\Salsify;

/**
 * Class Product
 * @package App\Models\Salsify
 */
class Product extends \ArrayObject
{

    /**
     * @var string
     */
    public $salsify_id;

    /**
     * @var \DateTime
     */
    public $salsify_updated_at;

    /**
     * @var \DateTime
     */
    public $salsify_created_at;

    /**
     * @var string
     */
    public $salsify_version;

    /**
     * @var string
     */
    public $salsify_relations_updated_at;

    /**
     * @var string
     */
    public $salsify_profile_asset_id;

    /**
     * @var string
     */
    public $salsify_parent_id;

    /**
     * @var string
     */
    public $salsify_system_id;

    /**
     * Products that have been destroyed will have salsify:destroyed_at property with the date of their delete.
     *
     * @var \DateTime
     */
    public $salsify_destroyed_at;

    /**
     * array includes parent products for all products in the products section.
     * The salsify:parent_id in product payload can be used to determine corresponding parent product.
     *
     * @var array
     */
    public $salsify_parent_products;

    /**
     * trigger_type received will be add, change, or remove and correspond with the subscription types.
     *
     * @var string
     */
    public $trigger_type;

    // more attributes to come

    /**
     * Product constructor.
     * @param array $input
     * @param int $flags
     * @param string $iterator_class
     */
    public function __construct($input = array(), int $flags = 0, string $iterator_class = "ArrayIterator")
    {
        foreach(array_keys($input) as $key)
        {
            // prepare the salsify keys
            $att = preg_replace('/:+/', '_', $key);
            if($att != $key)
            {
                $input[$att] = $input[$key];
                unset($input[$key]);
            }
        }

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
     * @param $key
     * @param null $def
     * @return mixed|null
     */
    public function get($key, $def=null)
    {
        return !empty($key) && $this->offsetExists($key) ? $this->offsetGet($key) : $def;
    }

    /**
     * Generate and return a slug based on an attribute of the product.
     *
     * @param mixed $key
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function getStoryblokSlug($key, $params=[])
    {
        try
        {
            // make it an array if a single field was given
            if(is_string($key))
            {
                $key = [$key];
            }

            // we're expecting an array
            if(!is_array($key))
            {
                throw new \Exception("unknown parameter type for \$key: " . gettype($key));
            }

            // sanitize all keys
            array_walk($key, function(&$v){
                $v = (!is_string($v) ? false : trim($v)) ?: false;
            });

            // remove empty values
            $key = array_filter($key);

            // ensure at least 1 valid key remains
            if(count($key) < 1)
            {
                throw new \Exception("no key given for slug field");
            }

            // setup default params
            $params = array_merge([
                'delimiter' => '-',
                'accept-regex' => '/[^a-z0-9_-]+/i',
                'replacement-char' => '-',
            ], $params);

            // holds all raw parts of the slug
            $raw = [];

            // get the values
            foreach($key as $k)
            {
                $raw[] = $this->get($k, false);
            }

            // sanitize the values
            array_walk($raw, function(&$v){
                $v = (!is_string($v) ? false : trim($v)) ?: false;
            });

            // remove empty values
            $raw = array_filter($raw);

            // ensure something is left
            if(empty($raw))
            {
                throw new \Exception("no values found when generating slug");
            }

            // join all the parts together
            $str = implode($params['delimiter'], $raw);

            // replace any unwanted chars, lcase, and trim it
            return strtolower(trim(preg_replace($params['accept-regex'], $params['replacement-char'], $str)));
        }
        catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
            throw new \Exception("unable to derive product slug");
        }
    }

}
