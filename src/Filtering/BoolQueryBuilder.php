<?php

namespace Ensi\LaravelElasticQuery\Filtering;

use Closure;
use Ensi\LaravelElasticQuery\Concerns\SupportsPath;
use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Exists;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Nested;
use Ensi\LaravelElasticQuery\Filtering\Criterias\RangeBound;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Term;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Illuminate\Contracts\Support\Arrayable;
use stdClass;

class BoolQueryBuilder implements BoolQuery, Criteria
{
    use SupportsPath;

    protected CriteriaCollection $must;
    protected CriteriaCollection $filter;
    protected CriteriaCollection $mustNot;

    public function __construct(protected string $path = '', protected bool $emptyMatchAll = true)
    {
        $this->must = new CriteriaCollection();
        $this->filter = new CriteriaCollection();
        $this->mustNot = new CriteriaCollection();
    }

    public static function make(string $path = '', ?Closure $builder = null): static
    {
        $instance = new static($path);

        if ($builder !== null) {
            $builder($instance);
        }

        return $instance;
    }

    public function isEmpty(): bool
    {
        return $this->criteriasCount() === 0;
    }

    //region Building DSL
    public function toDSL(): array
    {
        $count = $this->criteriasCount();
        if ($count === 0) {
            return $this->emptyMatchAll ? ['match_all' => new stdClass()] : [];
        }

        if ($count === 1 && $this->filter->count() === 1) {
            return head($this->filter->toDSL());
        }

        $body = array_merge(
            $this->criteriasToDSL('must', $this->must),
            $this->criteriasToDSL('filter', $this->filter),
            $this->criteriasToDSL('must_not', $this->mustNot),
        );

        return ['bool' => $body];
    }

    protected function criteriasToDSL(string $key, CriteriaCollection $criterias): array
    {
        return $criterias->isEmpty() ? [] : [$key => $criterias->toDSL()];
    }

    protected function criteriasCount(): int
    {
        return $this->must->count() + $this->filter->count() + $this->mustNot->count();
    }

    //endregion

    public function where(string $field, mixed $operator, mixed $value = null): static
    {
        if ($operator === '!=') {
            return $this->whereNot($field, $value);
        }

        if (func_num_args() === 2) {
            [$operator, $value] = ['=', $operator];
        }

        $criteria = $this->createComparisonCriteria($this->absolutePath($field), $operator, $value);
        $this->filter->add($criteria);

        return $this;
    }

    public function whereNot(string $field, mixed $value): static
    {
        $this->mustNot->add(new Term($this->absolutePath($field), $value));

        return $this;
    }

    public function whereIn(string $field, array|Arrayable $values): static
    {
        $this->filter->add(new Terms($this->absolutePath($field), $values));

        return $this;
    }

    public function whereNotIn(string $field, array|Arrayable $values): static
    {
        $this->mustNot->add(new Terms($this->absolutePath($field), $values));

        return $this;
    }

    public function whereHas(string $nested, Closure $filter): static
    {
        return $this->addNestedCriteria($nested, $filter, $this->filter);
    }

    public function whereDoesntHave(string $nested, Closure $filter): static
    {
        return $this->addNestedCriteria($nested, $filter, $this->mustNot);
    }

    public function whereNull(string $field): static
    {
        $this->mustNot->add(new Exists($this->absolutePath($field)));

        return $this;
    }

    public function whereNotNull(string $field): static
    {
        $this->filter->add(new Exists($this->absolutePath($field)));

        return $this;
    }

    protected function addNestedCriteria(string $nested, Closure $filter, CriteriaCollection $target): static
    {
        $path = $this->absolutePath($nested);
        $boolQuery = static::make($path, $filter);

        if (!$boolQuery->isEmpty()) {
            $target->add(new Nested($path, $boolQuery));
        }

        return $this;
    }

    protected function createComparisonCriteria(string $field, string $operator, mixed $value): Criteria
    {
        return $operator === '=' || $operator === '!='
            ? new Term($field, $value)
            : new RangeBound($field, $operator, $value);
    }

    protected function basePath(): string
    {
        return $this->path;
    }
}
