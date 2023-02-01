<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Response;

use Illuminate\Support\Collection;

class SuggestData
{
    /** @var Collection<SuggestOptionData> */
    public Collection $options;

    public function __construct(
        public string $text,
        public int $offset,
        public int $length,
        Collection $options,
    ) {
        $this->options = $options;
    }

    public static function makeFromArray(array $suggest): static
    {
        return new static(
            text: $suggest['text'],
            offset: $suggest['offset'],
            length: $suggest['length'],
            options: collect($suggest['options'])->transform(fn (array $o) => new SuggestOptionData(
                text: $o['text'],
                score: $o['score'] ?? null,
                freq: $o['freq'] ?? null,
                highlighted: $o['highlighted'] ?? null,
            ))
        );
    }
}
