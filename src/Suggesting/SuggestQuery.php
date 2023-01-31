<?php

namespace Ensi\LaravelElasticQuery\Suggesting;

use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuery\Search\Sorting\SortBuilder;
use Ensi\LaravelElasticQuery\Search\Sorting\SortCollection;
use Illuminate\Support\Collection;

class SuggestQuery
{
    public function __construct(protected SearchIndex $index)
    {

    }

    //region Executing
    public function get(): Collection
    {
        if ($this->size === 0) {
            return new Collection();
        }

        $response = $this->execute(size: $this->size, from: $this->from);

        return $this->parseHits($response);
    }

    protected function execute(
        ?SortCollection $sorts = null,
        ?int $size = null,
        ?int $from = null,
        bool $totals = false,
        bool $source = true,
        ?Cursor $cursor = null
    ): array {
        $dsl = [
            'size' => $size,
            'from' => $from,
            'query' => $this->boolQuery->toDSL(),
            'track_total_hits' => $totals,
            '_source' => $source && !$this->fields,
            'fields' => $source && $this->fields ? $this->fields : null,
        ];

        $sorts ??= $this->sorts;
        if (!$sorts->isEmpty()) {
            $dsl['sort'] = $sorts->toDSL();
        }

        if ($cursor !== null && !$cursor->isBOF()) {
            $dsl['search_after'] = $cursor->toDSL();
        }

        return $this->index->search(array_filter($dsl));
    }

    protected function parseHits(array $response): Collection
    {
        return collect(data_get($response, 'hits.hits') ?? []);
    }

    //endregion

    //region Customization
    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null, ?string $missingValues = null): static
    {
        (new SortBuilder($this->sorts))
            ->sortBy($field, $order, $mode, $missingValues);

        return $this;
    }
    //endregion
}
