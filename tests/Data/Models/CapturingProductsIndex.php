<?php

namespace Ensi\LaravelElasticQuery\Tests\Data\Models;

use Http\Promise\Promise;

class CapturingProductsIndex extends ProductsIndex
{
    public array $lastDsl = [];
    public ?string $lastSearchType = null;

    public function search(array $dsl, ?string $searchType = null): array|Promise
    {
        $this->lastDsl = $dsl;
        $this->lastSearchType = $searchType;

        return [
            'hits' => [
                'hits' => [],
            ],
        ];
    }
}
