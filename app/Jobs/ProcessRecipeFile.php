<?php

namespace App\Jobs;

use App\Events\DrupalRecipeCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessRecipeFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipeFilePath = '';

    protected $readClient;
    protected $mgmtClient;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pathToFile)
    {
        $this->recipeFilePath = $pathToFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $json = json_decode(Storage::get($this->recipeFilePath), true);
        $limit = env('DRUPAL_RECIPE_LIMIT', null);
        if(empty($limit)) $limit = null;

        foreach($json['data'] as $data)
        {
            if(!is_null($limit) && $limit-- < 0)
            {
                Log::debug("limit of recipes imports reached");
                continue;
            }
            Log::debug("triggering new recipe event");
            event(new DrupalRecipeCreated($data));
        }
    }
}
