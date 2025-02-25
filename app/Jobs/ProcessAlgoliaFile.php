<?php

namespace App\Jobs;

use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAlgoliaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $algolia_file = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($algolia_file)
    {
        $this->algolia_file = $algolia_file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchClient $client)
    {
        //
        try
        {
            Log::debug(__METHOD__);

            if(!Storage::disk(env('QUEUE_DISK', 'local'))->exists($this->algolia_file))
            {
                throw new \Exception("algolia_file:{$this->algolia_file} does not exist");
            }

            $index_name = config('algolia.index_name');
            if(empty($index_name))
            {
                throw new \Exception("config is missing algolia.index_name");
            }
            Log::debug("using algolia index_name:{$index_name}");

            if(!env('ALGOLIA_DRYRUN', false))
            {
                $index = $client->initIndex($index_name);
                $objects = json_decode(Storage::disk(env('QUEUE_DISK', 'local'))->get($this->algolia_file), true);
                Log::debug("found " . count($objects) . " objects to index");

                $index->replaceAllObjects($objects);

                Log::debug("saved " . count($objects) . " objects to {$index_name}");

                $this->cleanup();
            }
            else
            {
                Log::debug("Algolia dry-run enabled, NOT updating index");
            }

        }
        catch(\Exception $exception)
        {
            Log::error($exception->getMessage());
            $this->cleanup();
        }

    }

    protected function cleanup()
    {
        Storage::disk(env('QUEUE_DISK', 'local'))->delete($this->algolia_file);
        Log::debug("deleted algolia_file:{$this->algolia_file}");
    }
}
