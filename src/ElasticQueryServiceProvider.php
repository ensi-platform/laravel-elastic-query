<?php

namespace Ensi\LaravelElasticQuery;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Ring\Client\CurlMultiHandler;

class ElasticQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
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
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-elastic-query');

        $this->app->singleton(ElasticClient::class, fn (Application $app) => new ElasticClient($this->createClient($app)));
    }

    /**
     * Create the ElasticSearch client.
     * @param Application $app
     * @return Client
     */
    protected function createClient(Application $app): Client
    {
        return (new ClientBuilder())
            ->setHosts($this->getClientHosts($app))
            ->setRetries($this->getClientRetries($app))
            ->setHandler($this->getClientHandler())
            ->setBasicAuthentication($this->getClientUsername($app), $this->getClientPassword($app))
            ->setSSLVerification($this->getClientSSLVerification($app))
            ->build();
    }

    /**
     * Get an array of the elastic hosts from the configuration.
     * @param Application $app
     * @return array
     */
    protected function getClientHosts(Application $app): array
    {
        return $app['config']['laravel-elastic-query.connection.hosts'];
    }

    /**
     * Get the number of retries for the elastic client from the configuration.
     * @param Application $app
     * @return int
     */
    protected function getClientRetries(Application $app): int
    {
        return $app['config']['laravel-elastic-query.connection.retries'];
    }

    /**
     * Get the handler for the elastic client.
     * @return CurlMultiHandler
     */
    protected function getClientHandler(): CurlMultiHandler
    {
        return ClientBuilder::multiHandler();
    }

    /**
     * Get the username for the elastic client from the configuration.
     * @param Application $app
     * @return string
     */
    protected function getClientUsername(Application $app): string
    {
        return $app['config']['laravel-elastic-query.connection.credentials.username'];
    }

    /**
     * Get the password for the elastic client from the configuration.
     * @param Application $app
     * @return string
     */
    protected function getClientPassword(Application $app): string
    {
        return $app['config']['laravel-elastic-query.connection.credentials.password'];
    }

    /**
     * Get the SSL verification setting for the elastic client from the configuration.
     * @param Application $app
     * @return bool
     */
    protected function getClientSSLVerification(Application $app): bool
    {
        return $app['config']['laravel-elastic-query.connection.ssl_verification'];
    }
}
