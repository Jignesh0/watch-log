<?php

namespace nineBrainz\logs;

use Illuminate\Support\ServiceProvider;

class WatchLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('nineBrainz\logs\LogController');
        $this->loadViewsFrom(__DIR__.'/views', 'logs');
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
