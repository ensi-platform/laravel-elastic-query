<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Illuminate\Contracts\Support\Arrayable;

class MatchPhrasePrefixOptions implements Arrayable
{
    public function __construct(private array $options = [])
    {
    }

    public static function make(
        ?int $slop = null,
        ?int $maxExpansions = null,
        ?string $analyzer = null,
        ?string $zeroTermsQuery = null,
    ): self {
        return new static(array_filter([
            'slop' => $slop,
            'max_expansions' => $maxExpansions,
            'analyzer' => $analyzer,
            'zero_terms_query' => $zeroTermsQuery,
        ], static fn ($v) => $v !== null));
    }

    public function toArray(): array
    {
        return $this->options;
    }
}
