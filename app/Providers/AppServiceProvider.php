<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if(strpos(env('APP_URL'), 'https:') === 0)
        {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Paginator::useBootstrap();

    }
}
