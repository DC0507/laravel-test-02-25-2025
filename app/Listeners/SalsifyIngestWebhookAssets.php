<?php

namespace App\Listeners;

use App\Events\SalsifyWebhookAssetsIngested;
use App\Events\SalsifyWebhookPrepared;
use App\Models\Salsify\Asset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalsifyIngestWebhookAssets implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(SalsifyWebhookPrepared $event)
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

        $assets = [];

        foreach($data as $section)
        {
            if(array_key_exists('digital_assets', $section))
            {
                $assets = $section['digital_assets'];
                break;
            }

        }

        if(count($assets) < 1)
        {
            Log::warning("Payload file has no records");
        }

        // loop the products
        foreach($assets as $asset_record)
        {

            try
            {
                Log::debug(__METHOD__ . " Salsify asset_record");
                Log::debug(json_encode($asset_record, JSON_PRETTY_PRINT));
                Log::debug(__METHOD__ . " end Salsify asset_record");

                $asset_record = Asset::prepareRecord($asset_record);

                Log::debug("processing salsify asset:{$asset_record['salsify_id']}");

                $asset = Asset::updateOrCreate(
                    ['salsify_id' => $asset_record['salsify_id']],
                    $asset_record
                );

                $asset->save();

            }
            catch (\Exception $e)
            {
                Log::critical(__METHOD__ . " " . $e->getMessage());
            }
        }

        event(new SalsifyWebhookAssetsIngested($webhook));
    }
}
