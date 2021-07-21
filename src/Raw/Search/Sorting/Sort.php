<?php

namespace Greensight\LaravelElasticQuery\Raw\Search\Sorting;

use Greensight\LaravelElasticQuery\Raw\Contracts\DSLAware;
use Webmozart\Assert\Assert;

class Sort implements DSLAware
{
    public function __construct(
        private string $field,
        private string $order = 'asc',
        private ?string $mode = null,
        private ?NestedSort $nested = null
    ) {
        Assert::stringNotEmpty(trim($field));
        Assert::oneOf($order, ['asc', 'desc']);
        Assert::nullOrOneOf($mode, ['min', 'max', 'sum', 'avg', 'median']);
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

        if ($this->order !== 'asc') {
            $details['missing'] = '_first';
        }

        if (!$details) {
            return [$this->field => $this->order];
        }

        $details['order'] = $this->order;

        return [$this->field => $details];
    }

    public function __toString(): string
    {
        $order = $this->order === 'asc' ? '+' : '-';

        return "{$order}$this->field";
    }

    public function invert(): static
    {
        $order = $this->order === 'asc' ? 'desc' : 'asc';

        return new static($this->field, $order, $this->mode, $this->nested);
    }
}
