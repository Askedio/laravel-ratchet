<?php

namespace Askedio\LaravelRatchet\Providers;

use Askedio\LaravelRatchet\Console\Commands\RatchetServerCommand;
use Illuminate\Support\ServiceProvider;

class LaravelRatchetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.ratchet.serve', function () {
              return new RatchetServerCommand();
        });

        $this->commands('command.ratchet.serve');

        $this->mergeConfigFrom(__DIR__.'/../config/ratchet.php', 'ratchet');
    }

    /**
     * Register routes, translations, views and publishers.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
             __DIR__.'/../config/ratchet.php' => config_path('ratchet.php'),
         ]);
    }

    /**
    * Get the services provided by the provider.
    *
    * @return array
    */
    public function provides()
    {
        return array('command.ratchet.serve');
    }
}
