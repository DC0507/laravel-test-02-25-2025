<?php

namespace App\Listeners;

use App\Events\SalsifyProductDeleted;
use App\Models\Storyblok\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;


class StoryblokDeleteProduct extends StoryblokListener implements ShouldQueue
{

    /**
     * @var \App\Models\Salsify\Product
     */
    public $source_product;

    /**
     * Handle the event.
     *
     * @param  SalsifyProductDeleted  $event
     * @return void
     */
    public function handle(SalsifyProductDeleted $event)
    {
        $storyblok_mgmt_api = $this->storyblok_mgmt_api;

        $this->source_product = $event->salsify_product;

        $this->storyblok_product = new Product();

        $slug = $this->source_product->getStoryblokSlug();
        $url = "https://mapi.storyblok.com/v1/spaces/" . env('STORYBLOK_SPACE_ID') . "/stories/{$slug}";

        try
        {
            if(env('STORYBLOK_DRY_RUN', ''))
            {
                Log::debug("dry run: not deleting from Storyblok: {$url}");
            }
            else
            {
                // delete the story here
                $storyblok_mgmt_api->delete($url);
                Log::debug("deleted product: {$slug}");

            }
        }
        catch (ApiException $e)
        {
            Log::warning("unable to delete storyblok product:{$slug}: " . $e->getMessage());
        }

    }


}
