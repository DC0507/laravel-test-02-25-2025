<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Storyblok\ApiException;
use Storyblok\Client;
use Storyblok\ManagementClient;

class StoryblokListener {

    /**
     * @var Client
     */
    protected $storyblok_read_api;

    /**
     * @var ManagementClient
     */
    protected $storyblok_mgmt_api;

    /**
     * Create the event listener.
     *
     * @param Client $storyblok_read_api
     * @param ManagementClient $storyblok_mgmt_api
     * @return void
     */
    public function __construct(Client $storyblok_read_api, ManagementClient $storyblok_mgmt_api)
    {
        $this->storyblok_read_api = $storyblok_read_api;
        $this->storyblok_mgmt_api = $storyblok_mgmt_api;
    }

    /**
     * @param $slug
     * @param string $className
     * @return \ArrayObject|bool
     */
    protected function getStoryblokStory($slug, $className = \ArrayObject::class)
    {
        try
        {
            $this->storyblok_read_api->deleteCacheBySlug($slug);
            $this->storyblok_read_api->getStoryBySlug($slug);

            $body = $this->storyblok_read_api->getBody();
            if(is_string($body))
            {
                $body = json_decode($body, true);
            }
            Log::debug(__METHOD__ . " found story at slug:{$slug}");
            return new $className ($body);

        }
        catch (ApiException $e)
        {
            Log::debug(__METHOD__ . " no story at slug:{$slug}. responseCode:{$e->getCode()}");
            Log::warning($e->getMessage());
            return false;
        }
    }
}