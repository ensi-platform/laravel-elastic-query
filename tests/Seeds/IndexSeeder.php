<?php

namespace Ensi\LaravelElasticQuery\Tests\Seeds;

use Elastic\Elasticsearch\Client;

abstract class IndexSeeder
{
    protected string $indexName = '';
    protected array $mappings = [];
    protected array $settings = [];
    protected array $fixtures = [];

    protected bool $recreate;

    protected ?Client $client;

    public function __construct()
    {
        $this->recreate = config('tests.recreate_index', true);
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public static function run(): void
    {
        SeedRunner::getInstance()->run(static::class);
    }

    public function call(): void
    {
        $exists = $this->isIndexExists();

        if ($exists && $this->recreate) {
            $this->dropIndex();
            $exists = false;
        }

        if ($exists) {
            return;
        }

        $this->createIndex();
        $this->loadFixtures();
    }

    protected function isIndexExists(): bool
    {
        return $this->client->indices()
            ->exists(['index' => $this->indexName])
            ->asBool();
    }

    protected function dropIndex(): void
    {
        $this->client
            ->indices()
            ->delete(['index' => $this->indexName]);
    }

    protected function createIndex(): void
    {
        $params = ['index' => $this->indexName];

        if (!empty($this->mappings)) {
            data_set($params, 'body.mappings', $this->mappings);
        }

        if (!empty($this->settings)) {
            data_set($params, 'body.settings', $this->settings);
        }

        $this->client
            ->indices()
            ->create($params);
    }

    protected function loadFixtures(): void
    {
        $baseDir = __DIR__.'/fixtures/';

        $hasChanges = collect($this->fixtures)
            ->reduce(
                fn (bool $carry, string $fixture) => $this->loadFixture($baseDir.$fixture) || $carry,
                false
            );

        if ($hasChanges) {
            $this->client->indices()->refresh(['index' => $this->indexName]);
        }
    }

    protected function loadFixture(string $path): bool
    {
        $documents = json_decode(file_get_contents($path), true);

        if (empty($documents)) {
            return false;
        }

        $body = collect($documents)
            ->flatMap(fn (array $document, int $index) => $this->documentToCommand($document, $index))
            ->toArray();

        $this->client->bulk(['body' => $body]);

        return true;
    }

    protected function documentToCommand(array $document, int $id): array
    {
        return [
            ['index' => ['_index' => $this->indexName, '_id' => $id]],
            $document,
        ];
    }
}
