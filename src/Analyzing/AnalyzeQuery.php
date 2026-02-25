<?php

namespace Ensi\LaravelElasticQuery\Analyzing;

use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

class AnalyzeQuery
{
    protected ?string $analyzer = null;
    protected string|array|null $text = null;

    protected ?string $field = null;
    protected ?string $tokenizer = null;

    protected array $filter = [];
    protected array $charFilter = [];

    protected ?bool $explain = null;
    protected array $attributes = [];

    public function __construct(protected SearchIndex $index)
    {
    }

    public function analyzer(string $analyzer): static
    {
        Assert::stringNotEmpty($analyzer);
        $this->analyzer = $analyzer;

        return $this;
    }

    public function text(string|array $text): static
    {
        if (is_string($text)) {
            Assert::stringNotEmpty($text);
        } else {
            Assert::allStringNotEmpty($text);
        }

        $this->text = $text;

        return $this;
    }

    public function field(string $field): static
    {
        Assert::stringNotEmpty($field);
        $this->field = $field;

        return $this;
    }

    public function tokenizer(string $tokenizer): static
    {
        Assert::stringNotEmpty($tokenizer);
        $this->tokenizer = $tokenizer;

        return $this;
    }

    public function filter(array $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function charFilter(array $charFilter): static
    {
        $this->charFilter = $charFilter;

        return $this;
    }

    public function explain(bool $explain = true): static
    {
        $this->explain = $explain;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function dsl(): array
    {
        return array_filter([
            'analyzer' => $this->analyzer,
            'text' => $this->text,
            'field' => $this->field,
            'tokenizer' => $this->tokenizer,
            'filter' => $this->filter ?: null,
            'char_filter' => $this->charFilter ?: null,
            'explain' => $this->explain,
            'attributes' => $this->attributes ?: null,
        ], fn ($v) => !is_null($v));
    }

    public function execute(): array
    {
        Assert::true(!is_null($this->text), 'AnalyzeQuery: text is required');

        return $this->index->analyze($this->dsl());
    }

    public function tokens(): Collection
    {
        return collect($this->execute())->map(fn (array $response) => collect($response['tokens'] ?? []));
    }
}
