<?php

namespace App\Jobs;

use App\Models\Storyblok\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BuildAlgoliaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $storyblok_file = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($storyblok_file)
    {
        $this->storyblok_file = $storyblok_file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            if(!Storage::disk(env('QUEUE_DISK', 'local'))->exists($this->storyblok_file))
            {
                throw new \Exception("storyblok_file: {$this->storyblok_file} does not exist");
            }

            $id = uniqid();
            $now = date("c");
            $algolia_file = sprintf("algolia-feed-%s-%s.json", $id, $now);

            Log::debug("algolia file is: {$algolia_file}");

            $data = json_decode(Storage::disk(env('QUEUE_DISK', 'local'))->get($this->storyblok_file), JSON_OBJECT_AS_ARRAY);

            $out = [];
            foreach($data['stories'] as $rec)
            {
                $story = Story::factory($rec);

                // ignore certain stories
                if($story->isIgnored()) continue;

                // generate the algolia record
                $out[] = $story->toAlgoliaRecord();
            }

            Storage::disk(env('QUEUE_DISK', 'local'))->put($algolia_file, json_encode($out, JSON_PRETTY_PRINT | JSON_OBJECT_AS_ARRAY | JSON_UNESCAPED_SLASHES));
            Log::debug("wrote " . count($out) . " records to {$algolia_file}");

            Log::debug("dispatching ProcessAlgoliaFile job ({$algolia_file})");
            ProcessAlgoliaFile::dispatch($algolia_file);

            $this->cleanup();

        }
        catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
        }

    }

    protected function cleanup()
    {
        Storage::disk(env('QUEUE_DISK', 'local'))->delete($this->storyblok_file);
        Log::debug("deleted storyblok_file:{$this->storyblok_file}");
    }
}
