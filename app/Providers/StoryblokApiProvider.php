<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Storyblok\Client;
use Storyblok\ManagementClient;

class StoryblokApiProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function($app){
            $client = new Client(env('STORYBLOK_API_KEY'));
            $client->editMode(true);
            return $client;
        });

        $this->app->singleton(ManagementClient::class, function($app){
            return new ManagementClient(env('STORYBLOK_MGMT_API_KEY'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
