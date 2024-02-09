<?php

namespace Ensi\LaravelElasticQuery\Search;

use Illuminate\Support\Collection;

class Page
{
    public function __construct(
        public int $size,
        public Collection $hits,
        public Collection|null $aggs,
        public int $offset = 0,
        public int $total = 0
    ) {
    }
}
