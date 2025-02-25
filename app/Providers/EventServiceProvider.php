<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // read in the file and iterate over the products, queueing the processing of each one
        'App\Events\SalsifyWebhookPrepared' => [
            'App\Listeners\SalsifyIngestWebhookAssets',
            /**
             * @todo move this listener back under this event, but make sure they are executed in succession
             */
            //'App\Listeners\SalsifyProcessPreparedWebhook',
        ],

        'App\Events\SalsifyWebhookAssetsIngested' => [
            'App\Listeners\SalsifyProcessPreparedWebhook',
        ],

        'App\Events\SalsifyProductCreated' => [
            'App\Listeners\StoryblokCreateProduct'
        ],
        'App\Events\SalsifyProductDeleted' => [
            'App\Listeners\StoryblokDeleteProduct'
        ],
        'App\Events\SalsifyProductChanged' => [
            'App\Listeners\StoryblokUpdateProduct'
        ],

        'App\Events\DrupalRecipeCreated' => [
            'App\Listeners\StoryblokCreateRecipe',
        ],
        'App\Events\DrupalRecipeUpdated' => [
            'App\Listeners\StoryblokUpdateRecipe',
        ]

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
