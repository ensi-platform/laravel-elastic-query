<?php

namespace Ensi\LaravelElasticQuery\Filtering\Criterias;

use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Webmozart\Assert\Assert;

class Prefix implements Criteria
{
    public function __construct(
        private string $field,
        private string $value,
        private ?string $rewrite = null,
        private ?bool $caseInsensitive = null,
        private ?float $boost = null,
    ) {
        Assert::minLength($field, 1);
        Assert::minLength($value, 1);
    }

    public function toDSL(): array
    {
        $body = [
            $this->field => [
                'value' => $this->value,
            ],
        ];

        if ($this->rewrite) {
            $body[$this->field]['rewrite'] = $this->rewrite;
        }

        if ($this->caseInsensitive) {
            $body[$this->field]['case_insensitive'] = $this->caseInsensitive;
        }

        if ($this->boost) {
            $body[$this->field]['boost'] = $this->boost;
        }

        return ['prefix' => $body];
    }
}
