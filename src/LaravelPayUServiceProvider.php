<?php

namespace SomosGAD_\LaravelPayU;

use Illuminate\Support\ServiceProvider;

class LaravelPayUServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'somosgad');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'somosgad');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-payu.php', 'laravel-payu'
        );

        // Register the service the package provides.
        $this->app->singleton('laravel-payu', function ($app) {
            return new LaravelPayU;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-payu'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-payu.php' => config_path('laravel-payu.php'),
        ]);

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/somosgad'),
        ], 'laravel-payu.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/somosgad'),
        ], 'laravel-payu.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/somosgad'),
        ], 'laravel-payu.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
