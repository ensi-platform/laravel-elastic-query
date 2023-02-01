<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Response;

class SuggestOptionData
{
    public function __construct(
        public string $text,
        public ?string $score,
        public ?string $freq = null,
        public ?string $highlighted = null,
    ) {
    }
}
