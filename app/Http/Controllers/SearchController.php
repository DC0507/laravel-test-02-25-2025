<?php

namespace App\Http\Controllers;

use App\Jobs\FetchStoryblokContent;
use App\Models\Storyblok\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Storyblok\Client;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;


class SearchController extends Controller
{
    //
    public function generateFeed()
    {
        $data = json_decode(Storage::get('stories.json'), JSON_OBJECT_AS_ARRAY);
        $out = [];
        foreach($data['stories'] as $rec)
        {
            $story = Story::factory($rec);

            // ignore certain stories
            if($story->isIgnored()) continue;

            // generate the algolia record
            $out[] = $story->toAlgoliaRecord();
        }

        Storage::put('algolia.json', json_encode($out, JSON_PRETTY_PRINT | JSON_OBJECT_AS_ARRAY));

        echo '<pre>' . json_encode($out, JSON_PRETTY_PRINT | JSON_OBJECT_AS_ARRAY) . '</pre>';
    }

    public function storyblok(Client $client)
    {
        $client->getStories([
            'page' => 3,
            'per_page' => 25,
        ]);

        $stories = $client->getBody();
        $headers = $client->getHeaders();

        dd(Arr::get($headers, 'Total.0'));
    }

    public function recipes(Client $client)
    {
        $client->getStories([
            'by_slugs' => 'recipe/*',
            'per_page' => 100,
            'page' => 1,
        ]);
        $stories = $client->getBody();

        dd($stories);
    }

    public function products(Client $client)
    {
        $client->getStories([
            'by_slugs' => 'productdb/*',
            'per_page' => 100,
            'page' => 1,
        ]);
        $stories = $client->getBody();

        dd($stories);
    }

    public function initiateSearchReindex(Request $request)
    {
        // dispatch the job for the requested version
        FetchStoryblokContent::dispatch( $request->json('dialog_values.version', 'published') );

        return response([
            'status' => 0,
            'message' => 'received'
        ], 202);
    }

    public function storyblokWebhook(Request $request)
    {
        $action = $request->json('action');
        switch($action)
        {
            case 'workflow_stage_changed' :
            case 'branch_deployed' :
            case 'published' :
            case 'unpublished' :
            case 'deleted' :
            case 'release_merged' :

        }
    }

    public function storyPublished(Request $request, Client $client)
    {
        $action = $request->json('action');
        switch($action)
        {
            case 'published' :
            case 'unpublished' :
            case 'deleted' :
        }

    }

    public function releaseMerged(Request $request, Client $client)
    {
        $release_id = $request->json('release_id');
        $client->getStories([
            'release_id' => $release_id
        ]);
        $stories = $client->getBody();

    }

    public function pipelineComplete(Request $request, Client $client)
    {
        $branch_id = $request->json('branch_id');

    }

    public function workflowStageChanged(Request $request, Client $client)
    {
        $story_id = $request->json('story_id');
        $client->getStoryByUuid($story_id);
        $story = $client->getBody();
    }
}
