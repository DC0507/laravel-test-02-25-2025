<?php

namespace App\Listeners;

use App\Events\SalsifyProductChanged;
use App\Events\SalsifyProductCreated;
use App\Models\Storyblok\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;


/**
 * Class StoryblokCreateProduct
 * @package App\Listeners
 */
class StoryblokCreateProduct extends StoryblokListener implements ShouldQueue
{

    /**
     * @var \App\Models\Salsify\Product
     */
    public $source_product;

    /**
     * @var \App\Models\Storyblok\Product
     */
    public $storyblok_product;

    /**
     * Handle the event.
     *
     * @param  SalsifyProductCreated  $event
     * @return void
     */
    public function handle(SalsifyProductCreated $event)
    {
        $slug_field = config('middleware.field_mappings.slug_field');
        $storyblok_mgmt_api = $this->storyblok_mgmt_api;

        $this->source_product = $event->salsify_product;

        try
        {
            // the product part of the slug
            $base_slug = $this->source_product->getStoryblokSlug($slug_field);
        }
        catch (\Exception $exception)
        {
            Log::critical(__METHOD__ . "Cannot proceed without slug");
            Log::critical($exception->getMessage());
        }

        Log::debug( __METHOD__ . " received salsify product: {$base_slug}");

        // full path of the slug
        $full_slug = config('middleware.storyblok.product_slug_prefix') . $base_slug;

        Log::debug("determined slug to be: {$full_slug}");

        // find an existing product
        $existing = $this->getStoryblokStory($full_slug, Product::class);

        // if it exists, treat as an update event
        if($existing)
        {
            Log::warning("Product already exists at {$full_slug}, dispatching update event instead");

            // trigger the change event to capture updates
            event(new SalsifyProductChanged($this->source_product));

            return;
        }

        Log::debug("No product found at {$full_slug}. Proceeding with creation.");

        // factory a fresh Storyblok\Product from the Salsify\Product
        $this->storyblok_product = Product::fromSalsifyProduct($this->source_product);

        // init any defaults we can now
        $this->storyblok_product->setDefaults();

        // use plain array as a payload
        $payload = $this->storyblok_product->getArrayCopy();

        // form the POST url (no slugs)
        $url = 'spaces/' . config('middleware.storyblok.space_id') . '/stories/';

        Log::debug(__METHOD__ . " storyblok payload: " . json_encode($payload, JSON_PRETTY_PRINT));

        try
        {
            if(env('STORYBLOK_DRY_RUN', ''))
            {
                Log::debug("dry run: create Storyblok product at {$full_slug}");
            }
            else
            {
                $storyblok_mgmt_api->post($url, $payload)->getBody();
                Log::debug("created product, responseCode:{$storyblok_mgmt_api->responseCode}");
            }
        }
        catch (ApiException $exception)
        {
            Log::warning("unable to create product at {$full_slug}.");
            Log::warning(__METHOD__ . " " . $exception->getMessage());
            return;
        }


    }
}