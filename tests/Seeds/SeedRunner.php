<?php

namespace Ensi\LaravelElasticQuery\Tests\Seeds;

use Ensi\LaravelElasticQuery\Adapter\ClientConfig;
use Ensi\LaravelElasticQuery\ClientAdapter;

class SeedRunner
{
    private static ?self $instance;

    private array $processed = [];

    protected function __construct(private ClientAdapter $client)
    {
    }

    public function run(string $seedClass): void
    {
        if (array_key_exists($seedClass, $this->processed)) {
            return;
        }

        /** @var IndexSeeder $seeder */
        $seeder = new $seedClass();
        $seeder->setClient($this->client);
        $seeder->call();

        $this->processed[$seedClass] = true;
    }

    public static function getInstance(): self
    {
        self::$instance ??= self::createInstance();

        return self::$instance;
    }

    private static function createInstance(): self
    {
        $config = new ClientConfig(config('laravel-elastic-query.connection'));

        $adapter = class_exists('Elastic\Elasticsearch\Client')
            ? new \Ensi\LaravelElasticQuery\Adapter\ClientAdapterV8($config)
            : new \Ensi\LaravelElasticQuery\Adapter\ClientAdapterV7($config);

        return new self($adapter);
    }
}
