<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Webmozart\Assert\Assert;

class FunctionScoreFieldValueFactor extends FunctionScoreItem
{
    private const MODIFIERS = [
        'none',
        'log',
        'log1p',
        'log2p',
        'ln',
        'ln1p',
        'ln2p',
        'square',
        'sqrt',
        'reciprocal',
    ];

    public function __construct(
        protected string $field,
        protected int|float|null $factor = null,
        protected ?string $modifier = null,
        protected int|float|null $missing = null,
        ?float $weight = null,
        ?Criteria $filter = null,
    ) {
        parent::__construct(weight: $weight, filter: $filter);

        Assert::stringNotEmpty($field);
        Assert::nullOrOneOf($modifier, self::MODIFIERS);
    }

    public function toArray(): array
    {
        $dsl = parent::toArray();

        $dsl['field_value_factor'] = array_filter([
            'field' => $this->field,
            'factor' => $this->factor,
            'modifier' => $this->modifier,
            'missing' => $this->missing,
        ], fn (mixed $value) => !is_null($value));

        return $dsl;
    }
}
