<?php

namespace Ensi\LaravelElasticQuery;

use Ensi\LaravelElasticQuery\Adapter\ClientConfig;
use Ensi\LaravelElasticQuery\Contracts\ClientAdapter;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElasticQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-elastic-query.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-elastic-query');

        $this->app->singleton(
            ElasticClient::class,
            fn (Application $app) => new ElasticClient($this->createAdapter($app))
        );
    }

    protected function createAdapter(Application $app): ClientAdapter
    {
        $config = new ClientConfig($app['config']['laravel-elastic-query.connection']);

        return class_exists('Elastic\Elasticsearch\Client')
            ? new Adapter\ClientAdapterV8($config)
            : new Adapter\ClientAdapterV7($config);
    }
}
