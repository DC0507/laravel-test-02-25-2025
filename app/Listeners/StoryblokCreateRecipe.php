<?php

namespace App\Listeners;

use App\Events\DrupalRecipeCreated;
use App\Events\DrupalRecipeUpdated;
use App\Models\Storyblok\Recipe;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;

class StoryblokCreateRecipe extends StoryblokListener implements ShouldQueue
{
    /**
     * Raw array of recipe data
     *
     * @var array
     */
    public $recipe_data = [];

    /**
     * Handle the event.
     *
     * @param  DrupalRecipeCreated  $event
     * @return void
     */
    public function handle(DrupalRecipeCreated $event)
    {

        $slug = Recipe::getSlugFromData($event->recipe_data);

        $existing = $this->getStoryblokStory( env('STORYBLOK_RECIPE_SLUG_PREFIX') . $slug, Recipe::class);

        // ALREADY exists in Storyblok
        if($existing)
        {
            Log::debug("Recipe {$slug} already exists, triggering update event instead");
            event(new DrupalRecipeUpdated($event->recipe_data));
            return;
        }

        $recipe = Recipe::fromDrupalData($event->recipe_data);
        Log::debug("storyblok payload: " . json_encode($recipe->getArrayCopy(), JSON_PRETTY_PRINT));

        try
        {
            $url = 'spaces/' . config('middleware.storyblok.space_id') . '/stories/';
            if(env('STORYBLOK_DRY_RUN', ''))
            {
                Log::debug("dry run: NOT creating in Storyblok");
            }
            else
            {
                $this->storyblok_mgmt_api->post($url, $recipe->getArrayCopy());
                Log::debug("inserted recipe at: {$slug}");
            }
        }
        catch (ApiException $exception)
        {
            Log::warning($exception->getMessage());
        }
    }
}
