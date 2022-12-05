<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Webmozart\Assert\Assert;

class MultiMatchOptions
{
    public function __construct(private array $options = [])
    {
    }

    public static function make(
        ?string $type = null,
        ?string $operator = null,
        ?string $fuzziness = null,
        ?string $minimumShouldMatch = null
    ): static {
        Assert::nullOrOneOf($type, MatchType::cases());
        Assert::nullOrOneOf($operator, ['or', 'and']);

        return new static(array_filter([
            'type' => $type,
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
