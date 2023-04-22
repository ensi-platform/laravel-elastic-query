<?php

namespace Ensi\LaravelElasticQuery\Tests\Seeds;

use Ensi\LaravelElasticQuery\ElasticClient;

class SeedRunner
{
    private static ?self $instance;

    private array $processed = [];

    protected function __construct(private ElasticClient $client)
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
        $client = ElasticClient::fromConfig(config('laravel-elastic-query.connection'));

        return new self($client);
    }
}
