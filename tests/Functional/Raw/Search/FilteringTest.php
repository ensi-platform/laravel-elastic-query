<?php

namespace Greensight\LaravelElasticQuery\Tests\Functional\Raw\Search;

use Greensight\LaravelElasticQuery\Raw\Contracts\BoolQuery;

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
}
