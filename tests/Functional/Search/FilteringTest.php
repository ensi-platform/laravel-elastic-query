<?php

namespace Ensi\LaravelElasticQuery\Tests\Functional\Search;

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;

class FilteringTest extends SearchTestCase
{
    public function testWhere(): void
    {
        $this->testing->where('code', 'tv');

        $this->assertDocumentIds([1]);
    }

    public function testWhereNot(): void
    {
        $this->testing->whereNot('active', true);

        $this->assertDocumentIds([319]);
    }

    public function testWhereHas(): void
    {
        $this->testing->whereHas('offers', function (BoolQuery $query) {
            $query->where('seller_id', 15)
                ->where('active', false);
        });

        $this->assertDocumentIds([319, 405]);
    }

    public function testWhereDoesntHave(): void
    {
        $this->testing->whereDoesntHave('offers', function (BoolQuery $query) {
            $query->where('seller_id', 10)
                ->where('active', false);
        });

        $this->assertDocumentIds([1, 328, 471]);
    }

    public function testWhereNull(): void
    {
        $this->testing->whereNull('package');

        $this->assertDocumentIds([1, 319, 328, 471]);
    }

    public function testWhereNotNull(): void
    {
        $this->testing->whereNotNull('package');

        $this->assertDocumentIds([150, 405]);
    }

    public function testWhereMatch(): void
    {
        $this->testing->whereMatch('search_name', 'black leather gloves');

        $this->assertDocumentIds([319, 471]);
    }

    public function testWhereMatchOperatorAnd(): void
    {
        $this->testing->whereMatch('search_name', 'leather gloves', 'and');

        $this->assertDocumentIds([319]);
    }

    public function testWhereMatchOptions(): void
    {
        $this->testing->whereMatch('search_name', 'leather glaves', MatchOptions::make(fuzziness: 'AUTO'));

        $this->assertDocumentIds([319, 471]);
    }

    public function testWhereMultiMatch(): void
    {
        $this->testing->whereMultiMatch(['search_name', 'description'], 'nice gloves');

        $this->assertDocumentIds([471, 328, 319]);
    }

    public function testWhereMultiMatchDefault(): void
    {
        $this->testing->whereMultiMatch([], 'nice gloves');

        $this->assertDocumentIds([471, 328, 319]);
    }

    public function testWhereMultiMatchPrioritized(): void
    {
        $this->testing->whereMultiMatch(['search_name^2', 'description'], 'water');

        $this->assertDocumentOrder([150, 405]);
    }

    public function testWhereMultiMatchOptions(): void
    {
        $this->testing->whereMultiMatch(
            ['search_name', 'description'],
            'nace gloves',
            MultiMatchOptions::make(fuzziness: 'AUTO')
        );

        $this->assertDocumentIds([471, 328, 319]);
    }
}
