<?php

namespace Ensi\LaravelElasticQuery\Tests\Seeds;

use Ensi\LaravelElasticQuery\Contracts\ClientAdapter;

abstract class IndexSeeder
{
    protected string $indexName = '';
    protected array $mappings = [];
    protected array $settings = [];
    protected array $fixtures = [];

    protected bool $recreate;

    protected ?ClientAdapter $client;

    public function __construct()
    {
        $this->recreate = config('tests.recreate_index', true);
    }

    public function setClient(ClientAdapter $client): void
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
        return $this->client->indicesExists($this->indexName);
    }

    protected function dropIndex(): void
    {
        $this->client->indicesDelete($this->indexName);
    }

    protected function createIndex(): void
    {
        $params = [];

        if (!empty($this->mappings)) {
            $params['mappings'] = $this->mappings;
        }

        if (!empty($this->settings)) {
            $params['settings'] = $this->settings;
        }

        $this->client->indicesCreate($this->indexName, $params);
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
            $this->client->indicesRefresh($this->indexName);
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

        $this->client->bulk($this->indexName, $body);

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
