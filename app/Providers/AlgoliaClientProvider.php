<?php

namespace App\Providers;

use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Support\ServiceProvider;

class AlgoliaClientProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(SearchClient::class, function($app){

            $client = SearchClient::create(
                config('algolia.application_id_mcm'),
                config('algolia.admin_key_mcm')
            );

            $index = config('algolia.index_name', null);
            if(is_string($index))
            {
                $client->initIndex($index);
            }

            return $client;
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
