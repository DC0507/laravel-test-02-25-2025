<?php

namespace App\Jobs;

use App\Events\SalsifyWebhookPrepared;
use App\Models\Salsify\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalsifyPrepareWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Webhook
     */
    public $webhook;

    /**
     * Create a new job instance.
     *
     * @param Webhook $webhook
     * @return void
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;

        Log::debug(__METHOD__ . " received webhook: {$webhook->id}");
        Log::debug("logging the product_export_url: {$webhook->product_feed_export_url}");

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // download the json payload
        $product_export_url = $this->webhook->product_feed_export_url;

        if(empty($product_export_url))
        {
            Log::critical("no product_export_url for webhook, cannot proceed");
            $this->webhook->markFailed()->save;
            return false;
        }
        try
        {
            // fetch the file
            $response = Http::timeout(5)->get($product_export_url);

            // check for errors
            $response->throw();

        }
        catch (ConnectionException $e)
        {
            Log::warning("connection timeout fetching webhook payload_uri:{$product_export_url}: {$e->getMessage()}");
        }
        catch (RequestException $e)
        {
            Log::warning("failed to fetch {$product_export_url}");
            Log::warning($e->getMessage());

            if($response->serverError())
            {
                // @todo retry the job later?
            }

            if($response->clientError())
            {
                // @todo retry job? what is appropriate here?
            }

            return;
        }

        // store local path to downloaded file
        $this->webhook->downloaded_payload_filename = $this->webhook->computeLocalFilename();

        // write response to disk
        Storage::put($this->webhook->downloaded_payload_filename, $response->body());

        // mark it prepared
        $this->webhook->markPrepared()->save();

        // signal that it is prepared
        event(new SalsifyWebhookPrepared($this->webhook));
    }
}
