<?php

namespace App\Listeners;

use App\Events\DrupalRecipeCreated;
use App\Events\DrupalRecipeUpdated;
use App\Models\Storyblok\Recipe;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;

class StoryblokUpdateRecipe extends StoryblokListener
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
     * @param  DrupalRecipeUpdated  $event
     * @return void
     */
    public function handle(DrupalRecipeUpdated $event)
    {

        $slug = Recipe::getSlugFromData($event->recipe_data);

        $existing = $this->getStoryblokStory( env('STORYBLOK_RECIPE_SLUG_PREFIX') . $slug, Recipe::class);

        // does NOT exist in Storyblok
        if(!$existing)
        {
            Log::debug("Recipe {$slug} does NOT exist, triggering create event instead");
            event(new DrupalRecipeCreated($event->recipe_data));
            return;
        }

        $existing->applyDrupalData($event->recipe_data);
        $existing->removeCruft();
        $payload = $existing->getArrayCopy();

        Log::debug("storyblok payload: " . json_encode($payload, JSON_PRETTY_PRINT));

        try
        {
            $url = 'spaces/' . config('middleware.storyblok.space_id') . '/stories/' . $payload['story']['id'];
            if(env('STORYBLOK_DRY_RUN', ''))
            {
                Log::debug("dry run: NOT updating in Storyblok");
            }
            else
            {
                $this->storyblok_mgmt_api->put($url, $existing->getArrayCopy());
                Log::debug("updated recipe at: {$slug}");
            }
        }
        catch (ApiException $e)
        {
            Log::warning($e->getMessage());
        }

    }
}
