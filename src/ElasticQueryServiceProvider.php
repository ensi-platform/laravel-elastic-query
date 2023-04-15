<?php

namespace Ensi\LaravelElasticQuery;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
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

        $this->app->singleton(ElasticClient::class, fn (Application $app) => new ElasticClient($this->createClient($app)));
    }

    protected function createClient(Application $app): Client
    {
        return (new ClientBuilder())
            ->setHosts($app['config']['laravel-elastic-query.connection.hosts'])
            ->build();
    }
}
