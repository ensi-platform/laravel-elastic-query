<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\MatchPhrasePrefixOptions;
use Webmozart\Assert\Assert;

class MatchPhrasePrefix implements Criteria
{
    public function __construct(
        private string $field,
        private string $query,
        private ?MatchPhrasePrefixOptions $options = null,
        private ?float $boost = null,
    ) {
        Assert::minLength($field, 1);
        Assert::minLength($query, 1);
    }

    public function toDSL(): array
    {
        $body = ['query' => $this->query];

        if ($this->options) {
            $body = array_merge($body, $this->options->toArray());
        }

        if ($this->boost !== null) {
            $body['boost'] = $this->boost;
        }

        return ['match_phrase_prefix' => [$this->field => $body]];
    }
}
