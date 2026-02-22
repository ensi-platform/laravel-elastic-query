<?php

namespace Ensi\LaravelElasticQuery\Contracts;

use Closure;
use Ensi\LaravelElasticQuery\Filtering\Criterias\FunctionScore;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Prefix;
use Illuminate\Contracts\Support\Arrayable;

interface BoolQuery
{
    public function where(string $field, mixed $operator, mixed $value = null): static;

    public function orWhere(string $field, mixed $operator, mixed $value = null): static;

    public function whereNot(string $field, mixed $value): static;

    public function orWhereNot(string $field, mixed $value): static;

    public function whereIn(string $field, array|Arrayable $values): static;

    public function orWhereIn(string $field, array|Arrayable $values): static;

    public function whereNotIn(string $field, array|Arrayable $values): static;

    public function whereHas(string $nested, Closure $filter): static;

    public function whereDoesntHave(string $nested, Closure $filter): static;

    public function whereNull(string $field): static;

    public function whereNotNull(string $field): static;

    public function whereMatch(string $field, string $query, string|MatchOptions $operator = 'or'): static;

    public function orWhereMatch(string $field, string $query, string|MatchOptions $operator = 'or'): static;

    public function whereMultiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): static;

    public function orWhereMultiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): static;

    public function whereWildcard(string $field, string $query, ?WildcardOptions $options = null): static;

    public function orWhereWildcard(string $field, string $query, ?WildcardOptions $options = null): static;

    public function addMustBool(callable $fn): static;

    public function whereMoreLikeThis(array $fields, MoreLikeThis $likeThis, ?MoreLikeOptions $options = null): static;

    public function whereBetween(string $field, mixed $from, mixed $to): static;

    public function orFunctionScore(FunctionScore $functionScore): static;

    public function prefix(Prefix $prefix): static;

    public function orPrefix(Prefix $prefix): static;

    public function pinned(array $ids, ?DSLAware $query = null): static;

    public function whereDisMax(Closure $builder, float $tieBreaker = 0.0, ?float $boost = null): static;

    public function orWhereDisMax(Closure $builder, float $tieBreaker = 0.0, ?float $boost = null): static;
}
