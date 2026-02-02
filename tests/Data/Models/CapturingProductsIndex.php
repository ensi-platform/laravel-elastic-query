<?php

namespace Ensi\LaravelElasticQuery\Tests\Data\Models;

class CapturingProductsIndex extends ProductsIndex
{
    public array $lastDsl = [];
    public ?string $lastSearchType = null;

    public function search(array $dsl, ?string $searchType = null): array
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
