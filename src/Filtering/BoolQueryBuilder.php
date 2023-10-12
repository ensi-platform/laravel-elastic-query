<?php

namespace Ensi\LaravelElasticQuery\Filtering;

use Closure;
use Ensi\LaravelElasticQuery\Concerns\SupportsPath;
use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuery\Contracts\Criteria;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;
use Ensi\LaravelElasticQuery\Contracts\WildcardOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Exists;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MultiMatch;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Nested;
use Ensi\LaravelElasticQuery\Filtering\Criterias\OneMatch;
use Ensi\LaravelElasticQuery\Filtering\Criterias\RangeBound;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Term;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Terms;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Wildcard;
use Illuminate\Contracts\Support\Arrayable;
use stdClass;

class BoolQueryBuilder implements BoolQuery, Criteria
{
    use SupportsPath;

    protected CriteriaCollection $must;
    protected CriteriaCollection $should;
    protected CriteriaCollection $filter;
    protected CriteriaCollection $mustNot;

    public function __construct(protected string $path = '', protected bool $emptyMatchAll = true)
    {
        $this->must = new CriteriaCollection();
        $this->should = new CriteriaCollection();
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
            $this->criteriasToDSL('should', $this->should),
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
        return $this->must->count() + $this->should->count() + $this->filter->count() + $this->mustNot->count();
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

    public function whereMatch(string $field, string $query, string|MatchOptions $operator = 'or'): static
    {
        $this->must->add($this->makeMatch($field, $query, $operator));

        return $this;
    }

    public function orWhereMatch(string $field, string $query, string|MatchOptions $operator = 'or'): static
    {
        $this->should->add($this->makeMatch($field, $query, $operator));

        return $this;
    }

    protected function makeMatch(string $field, string $query, string|MatchOptions $operator = 'or'): OneMatch
    {
        $options = is_string($operator) ? MatchOptions::make($operator) : $operator;

        return new OneMatch($this->absolutePath($field), $query, $options);
    }

    public function whereMultiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): static
    {
        $this->must->add($this->makeMultiMatch($fields, $query, $type));

        return $this;
    }

    public function orWhereMultiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): static
    {
        $this->should->add($this->makeMultiMatch($fields, $query, $type));

        return $this;
    }

    protected function makeMultiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): MultiMatch
    {
        $options = is_string($type) ? MultiMatchOptions::make($type) : $type;

        $fields = array_map(
            fn (string $field) => $this->absolutePath($field),
            $fields
        );

        return new MultiMatch($fields, $query, $options ?? new MultiMatchOptions());
    }

    public function whereWildcard(string $field, string $query, ?WildcardOptions $options = null): static
    {
        $this->must->add($this->makeWildcard($field, $query, $options));

        return $this;
    }

    public function orWhereWildcard(string $field, string $query, ?WildcardOptions $options = null): static
    {
        $this->should->add($this->makeWildcard($field, $query, $options));

        return $this;
    }

    protected function makeWildcard(string $field, string $query, ?WildcardOptions $options = null): Wildcard
    {
        return new Wildcard($this->absolutePath($field), $query, $options ?: new WildcardOptions());
    }

    public function addMustBool(callable $fn): static
    {
        $this->must->add(static::make(builder: $fn));

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
