<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional\Suggesting;

use Ensi\LaravelElasticQuery\Suggesting\SuggestQuery;
use Ensi\LaravelElasticQuery\Tests\Functional\ElasticTestCase;
use Ensi\LaravelElasticQuery\Tests\Models\ProductsIndex;
use Ensi\LaravelElasticQuery\Tests\Seeds\ProductIndexSeeder;

class SuggestQueryTest extends ElasticTestCase
{
    protected SuggestQuery $testing;

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
        $this->testing = $this->makeSuggestQuery(ProductsIndex::class);
    }

    public function testPhraseSuggesterGet(): void
    {
        $this->testing->newPhraseSuggester('s', 'name.trigram')->text('glves')->size(1)->shardSize(3);
        $results = $this->testing->get();

        $this->assertEquals('gloves', $results->get('s')?->first()?->options?->first()?->text);
    }

    public function testTermSuggesterGet(): void
    {
        $this->testing->newTermSuggester('s', 'name.trigram')->text('glves')->size(1)->shardSize(3);
        $results = $this->testing->get();

        $this->assertEquals('gloves', $results->get('s')?->first()?->options?->first()?->text);
    }

    public function testGlobalText(): void
    {
        $this->testing->globalText('glves');

        $this->testing->newPhraseSuggester('s', 'name.trigram')->size(1)->shardSize(3);
        $results = $this->testing->get();

        $this->assertEquals('gloves', $results->get('s')?->first()?->options?->first()?->text);
    }
}
