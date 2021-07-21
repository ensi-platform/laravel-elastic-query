<?php

namespace Greensight\LaravelElasticQuery\Raw\Search;

use Illuminate\Support\Collection;

class CursorPage
{
    public function __construct(
        int $size,
        public Collection $hits,
        public ?string $current = null,
        public ?string $next = null,
        public ?string $previous = null
    ) {
    }
}
