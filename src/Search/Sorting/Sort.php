<?php

namespace Ensi\LaravelElasticQuery\Search\Sorting;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Contracts\MissingValuesMode;
use Ensi\LaravelElasticQuery\Contracts\SortMode;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Webmozart\Assert\Assert;

class Sort implements DSLAware
{
    public function __construct(
        private string $field,
        private string $order = SortOrder::ASC,
        private ?string $mode = null,
        private ?NestedSort $nested = null,
        private ?string $missingValues = null
    ) {
        Assert::stringNotEmpty(trim($field));
        Assert::oneOf($order, SortOrder::cases());
        Assert::nullOrOneOf($mode, SortMode::cases());
    }

    public function field(): string
    {
        return $this->field;
    }

    public function toDSL(): array
    {
        $details = [];

        if ($this->mode !== null) {
            $details['mode'] = $this->mode;
        }

        if ($this->nested !== null) {
            $details['nested'] = $this->nested->toDSL();
        }

        if ($this->missingValues !== null) {
            $details['missing'] = $this->missingValues;
        }

        if (!$details) {
            return [$this->field => $this->order];
        }

        $details['order'] = $this->order;

        return [$this->field => $details];
    }

    public function __toString(): string
    {
        $order = $this->order === SortOrder::ASC ? '+' : '-';

        return "{$order}$this->field";
    }

    public function invert(): static
    {
        $order = $this->order === SortOrder::ASC ? SortOrder::DESC : SortOrder::ASC;
        $missingValues = $this->missingValues === MissingValuesMode::FIRST ? null : MissingValuesMode::FIRST;

        return new static($this->field, $order, $this->mode, $this->nested, $missingValues);
    }
}
