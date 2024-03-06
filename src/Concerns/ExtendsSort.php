<?php

namespace Ensi\LaravelElasticQuery\Concerns;

use Ensi\LaravelElasticQuery\Contracts\SortMode;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuery\Scripts\Script;

/**
 * @psalm-require-implements \Ensi\LaravelElasticQuery\Contracts\SortableQuery
 *
 * @method static sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null)
 */
trait ExtendsSort
{
    public function minSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MIN);
    }

    public function maxSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MAX);
    }

    public function avgSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::AVG);
    }

    public function sumSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::SUM);
    }

    public function medianSortBy(string $field, string $order = SortOrder::ASC): static
    {
        return $this->sortBy($field, $order, SortMode::MEDIAN);
    }

    public function sortByCustomArray(string $field, array $items): static
    {
        $script = new Script(
            params: ['items' => $items],
            source: "
              for (int i = 0; i < params['items'].length; i++) {
                  if (params['items'][i] == doc['{$field}'].value) {
                      return i;
                  }
              }
              
              return params['items'].length;
            ",
        );

        return $this->sortByScript($script);
    }
}
