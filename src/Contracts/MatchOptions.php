<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Webmozart\Assert\Assert;

class MatchOptions implements Arrayable
{
    public function __construct(private array $options = [])
    {
    }

    public static function make(
        ?string $operator = null,
        ?string $fuzziness = null,
        ?string $minimumShouldMatch = null
    ): static {
        Assert::nullOrOneOf($operator, ['or', 'and']);

        return new static(array_filter([
            'operator' => $operator,
            'fuzziness' => $fuzziness,
            'minimum_should_match' => $minimumShouldMatch,
        ]));
    }

    public function toArray(): array
    {
        return $this->options;
    }
}
