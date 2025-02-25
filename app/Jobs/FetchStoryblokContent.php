<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Storyblok\Client;

class FetchStoryblokContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $space_id;

    /**
     * The version of content to fetch: draft or published
     * @var string
     */
    public $version = 'published';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Client $client)
    {
        try
        {

            $id = uniqid();
            $now = date("c");
            $storyblok_file = sprintf("storyblock-stories-%s-%s.json", $id, $now);

            Log::debug("writing storyblok stories to {$storyblok_file}");

            $accumulated_stories = [];

            $current_page = 1;
            $per_page = env('SEARCH_STORYBLOK_STORIES_PER_PAGE', 100);
            $total_pages = null;

            do
            {
                Log::debug("fetching page:{$current_page} of storyblok stories");

                $client->getStories([
                    'version' => $this->version,
                    'page' => $current_page,
                    'per_page' => $per_page,
                ]);


                // calculate how much farther we have to go
                if(is_null($total_pages))
                {
                    $headers = $client->getHeaders();
                    $total_pages = ceil((int) Arr::get($headers, 'Total.0') / $per_page);

                    Log::debug("storyblok pages to fetch: {$total_pages}");
                }

                // append the fetched stories to our array
                $accumulated_stories = array_merge($accumulated_stories, Arr::get($client->getBody(), 'stories'));

                // inc the page counter
                $current_page++;

            } while($current_page <= $total_pages);

            // structure and write the accumulated stories to disk
            Storage::disk(env('QUEUE_DISK', 'local'))->put($storyblok_file, json_encode(['stories' => $accumulated_stories], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));


            Log::debug("wrote " . count($accumulated_stories) . " to {$storyblok_file}");

            Log::debug("dispatching BuildAlgoliaFile job");

            // dispatch the job to process it into an Algolia feed
            BuildAlgoliaFile::dispatch($storyblok_file);
        }
        catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
        }
    }
}
