<?php

namespace Ensi\LaravelElasticQuery\Contracts;

class WildcardOptions
{
    public function __construct(private array $options = [])
    {
    }

    public static function make(
        ?float $boost = null,
        ?bool $caseInsensitive = null,
        ?string $rewrite = null,
    ): static {
        $options = [];
        if (!is_null($boost)) {
            $options['boost'] = $boost;
        }
        if (!is_null($caseInsensitive)) {
            $options['case_insensitive'] = $caseInsensitive;
        }
        if (!is_null($rewrite)) {
            $options['rewrite'] = $rewrite;
        }
        return new static($options);
    }

    public function toArray(): array
    {
        return $this->options;
    }
}
