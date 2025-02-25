<?php

namespace App\Listeners;

use App\Events\SalsifyProductChanged;
use App\Events\SalsifyProductCreated;
use App\Models\Storyblok\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;


class StoryblokUpdateProduct extends StoryblokListener implements ShouldQueue
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
     * @param  SalsifyProductChanged  $event
     * @return void
     */
    public function handle(SalsifyProductChanged $event)
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

        // full path of the slug
        $full_slug = config('middleware.storyblok.product_slug_prefix') . $base_slug;

        Log::debug(__METHOD__ . " received product slug:{$full_slug}");

        // find existing product
        $this->storyblok_product = $this->getStoryblokStory($full_slug, Product::class);

        // if missing, treat as a create event
        if(!$this->storyblok_product)
        {
            Log::notice("attempting to update non-existent product at {$full_slug}, dispatching new product event");

            // trigger the create event
            event(new SalsifyProductCreated($this->source_product));

            return;
        }

        Log::notice("Found existing product at {$full_slug}, proceeding with update");
        Log::debug("existing storyblok story: " . json_encode($this->storyblok_product->getArrayCopy(), JSON_PRETTY_PRINT));

        // Load Salsify data into a new Storyblok\Product
        $new_product = Product::fromSalsifyProduct($this->source_product);

        // merge the new product data onto the existing Storyblok data
        $this->storyblok_product->mergeProduct($new_product);

        // init any defaults we can now
        $this->storyblok_product->setDefaults();

        // get the combined product data
        $payload = $this->storyblok_product->getArrayCopy();

        Log::debug(__METHOD__ . " storyblok payload: " . json_encode($payload, JSON_PRETTY_PRINT));

        // craft the url for the PUT request
        $url = 'spaces/' . config('middleware.storyblok.space_id') . '/stories/' . $payload['story']['id'];

        try
        {
            if(env('STORYBLOK_DRY_RUN', ''))
            {
                Log::debug("dry run: update Storyblok product at {$full_slug}");
            }
            else
            {
                $storyblok_mgmt_api->put($url, $payload)->getBody();
                Log::debug("updated product, responseCode:{$storyblok_mgmt_api->responseCode}");
            }
        }
        catch (ApiException $exception)
        {
            Log::warning("unable to update product at {$full_slug}.");
            Log::warning(__METHOD__ . " " . $exception->getMessage());
            return;
        }

    }
}
