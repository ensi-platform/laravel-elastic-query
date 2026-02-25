<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Webmozart\Assert\Assert;

class MatchPhraseOptions implements Arrayable
{
    public function __construct(private array $options = [])
    {
    }

    public static function make(
        ?int $slop = null,
        ?string $analyzer = null,
        ?string $zeroTermsQuery = null,
    ): self {
        Assert::nullOrOneOf($zeroTermsQuery, ['none', 'all']);

        return new static(array_filter([
            'slop' => $slop,
            'analyzer' => $analyzer,
            'zero_terms_query' => $zeroTermsQuery,
        ], static fn ($v) => $v !== null));
    }

    public function toArray(): array
    {
        return $this->options;
    }
}
