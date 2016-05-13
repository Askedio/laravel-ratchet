<?php

namespace Askedio\LaravelRachet\Providers;

use Askedio\LaravelRachet\Console\Commands\RachetServerCommand;
use Illuminate\Support\ServiceProvider;

class LaravelRachetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.rachet', function () {
              return new RachetServerCommand();
        });

        $this->commands('command.rachet');

        $this->mergeConfigFrom(__DIR__.'/../config/rachet.php', 'rachet');
    }

    /**
     * Register routes, translations, views and publishers.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
             __DIR__.'/../config/rachet.php' => config_path('rachet.php'),
         ]);
    }
}
