<?php

namespace App\Listeners;

use App\Events\SalsifyProductChanged;
use App\Events\SalsifyProductCreated;
use App\Events\SalsifyProductDeleted;
use App\Events\SalsifyWebhookAssetsIngested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalsifyProcessPreparedWebhook implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SalsifyWebhookAssetsIngested $event
     * @return void
     */
    public function handle(SalsifyWebhookAssetsIngested $event)
    {

        $webhook = $event->webhook;

        if(!$webhook->isPrepared())
        {
            Log::warning("refusing to process unprepared webhook:{$webhook->id}");
            return;
        }

        // parse the json file
        $data = json_decode(Storage::get($webhook->downloaded_payload_filename), true);

        if($data === null)
        {
            Log::critical("Unable to parse json from downloaded file: {$webhook->downloaded_payload_filename}");
            return;
        }

        if(!is_array($data))
        {
            Log::critical("Payload file is not an array at top level");
            return;
        }

        $products = [];

        foreach($data as $section)
        {
            if(array_key_exists('products', $section))
            {
                $products = $section['products'];
                break;
            }

        }

        if(count($products) < 1)
        {
            Log::warning("Payload file has no records");
        }

        // sort the records in sku order
        uasort($products, function($a, $b){
            if($a['Welchs SKU'] == $b['Welchs SKU']) return 0;
            return $a['Welchs SKU'] < $b['Welchs SKU'] ? -1 : 1;
        });

        $limit_sku = env('SALSIFY_LIMIT_SKU', null);
        if(!empty($limit_sku))
        {
            $products = array_filter($products, function($product) use ($limit_sku){
                return $product['Welchs SKU'] == $limit_sku;
            });
        }

        $limit = env('SALSIFY_PRODUCT_LIMIT', null);
        if(empty($limit)) $limit = null;

        if(!is_null($limit))
        {
            Log::debug("enforcing a limit of {$limit} products to be proccessed");
        }

        Log::debug("product feed contains " . count($products) . " record(s)");

        $slug_field = config('middleware.field_mappings.slug_field');
        $sku = null;
        $limit_slug = trim(env('SALSIFY_LIMIT_SLUG', false));
        if(!empty($limit_slug))
        {
            Log::debug("limiting products to {$limit_slug}");
        }

        // loop the products
        foreach($products as $product_record)
        {
//            $sku_field = config('middleware.field_mappings.product.sku', 'Welchs SKU');

            // enforce limit if set
            if(!is_null($limit) && $limit-- < 0) {
                Log::debug("limit of processed products reached, skipping");
                continue;
            }

            try
            {

                $product = new \App\Models\Salsify\Product($product_record);

                $slug = $product->getStoryblokSlug($slug_field);

                if(!empty($limit_slug))
                {
                    if($limit_slug != $slug)
                    {
                        continue;
                    }
                    else
                    {
                        Log::debug("matched limit_slug:{$slug}");
                    }
                }

                Log::debug(__METHOD__ . " processing product record slug:{$slug}, trigger_type:{$product->trigger_type}");
                Log::debug(__METHOD__ . " Salsify product_record");
                Log::debug(json_encode($product_record, JSON_PRETTY_PRINT));
                Log::debug(__METHOD__ . " end Salsify product_record");

                // trigger_type is absent from the channel feed
                switch($product->trigger_type)
                {

                    case 'remove' :
                        event(new SalsifyProductDeleted($product));
                    break;

                    case 'add' :
                        event(new SalsifyProductCreated($product));
                    break;

                    case 'change' :
                    default :
                        event(new SalsifyProductChanged($product));
                    break;

                }
            }
            catch (\Exception $e)
            {
                Log::critical(__METHOD__ . " " . $e->getMessage());
            }
        }

        // @todo Issue all above events (jobs?) as a batch and after their completion mark webhook complete.
        $webhook->markComplete()->save();
    }
}
