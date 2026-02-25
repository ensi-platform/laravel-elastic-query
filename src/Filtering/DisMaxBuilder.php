<?php

namespace Ensi\LaravelElasticQuery\Filtering;

use Ensi\LaravelElasticQuery\Concerns\SupportsPath;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MatchPhraseOptions;
use Ensi\LaravelElasticQuery\Contracts\MatchPhrasePrefixOptions;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;
use Ensi\LaravelElasticQuery\Filtering\Criterias\FunctionScore;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MatchPhrase;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MatchPhrasePrefix;
use Ensi\LaravelElasticQuery\Filtering\Criterias\MultiMatch;
use Ensi\LaravelElasticQuery\Filtering\Criterias\OneMatch;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Prefix;

class DisMaxBuilder
{
    use SupportsPath;

    /** @var DSLAware[] */
    protected array $queries = [];

    public function __construct(protected string $path = '')
    {
    }

    public function add(DSLAware $query): static
    {
        $this->queries[] = $query;

        return $this;
    }

    public function match(string $field, string $query, string|MatchOptions $operator = 'or'): static
    {
        $options = is_string($operator) ? MatchOptions::make($operator) : $operator;

        return $this->add(new OneMatch($this->absolutePath($field), $query, $options));
    }

    public function multiMatch(array $fields, string $query, string|MultiMatchOptions|null $type = null): static
    {
        $options = is_string($type) ? MultiMatchOptions::make($type) : $type;

        $fields = array_map(fn (string $field) => $this->absolutePath($field), $fields);

        return $this->add(new MultiMatch($fields, $query, $options ?? new MultiMatchOptions()));
    }

    public function prefix(string $field, string $value): static
    {
        return $this->add(new Prefix(field: $this->absolutePath($field), value: $value));
    }

    public function functionScore(FunctionScore $functionScore): static
    {
        return $this->add($functionScore);
    }

    public function matchPhrase(
        string $field,
        string $query,
        ?MatchPhraseOptions $options = null,
        ?float $boost = null,
    ): static {
        return $this->add(new MatchPhrase(
            field: $this->absolutePath($field),
            query: $query,
            options: $options,
            boost: $boost
        ));
    }

    public function matchPhrasePrefix(
        string $field,
        string $query,
        ?MatchPhrasePrefixOptions $options = null,
        ?float $boost = null,
    ): static {
        return $this->add(new MatchPhrasePrefix(
            field: $this->absolutePath($field),
            query: $query,
            options: $options,
            boost: $boost
        ));
    }

    /**
     * @return DSLAware[]
     */
    public function queries(): array
    {
        return $this->queries;
    }

    public function isEmpty(): bool
    {
        return $this->queries === [];
    }

    protected function basePath(): string
    {
        return $this->path;
    }
}
