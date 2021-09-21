<?php

namespace Greensight\LaravelElasticQuery;

use Elasticsearch\ClientBuilder;
use Greensight\LaravelElasticQuery\ElasticClient;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElasticQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'elastic');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'elastic');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ensi-elastic-query.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/elastic'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/elastic'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/elastic'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ensi-elastic-query');

        $this->app->singleton(ElasticClient::class, fn (Application $app) => $this->createClient($app));
    }

    private function createClient(Application $app): ElasticClient
    {
        $naturalClient = (new ClientBuilder())
            ->setHosts($app['config']['ensi-elastic-query.connection.hosts'])
            ->build();

        return new ElasticClient($naturalClient);
    }
}
