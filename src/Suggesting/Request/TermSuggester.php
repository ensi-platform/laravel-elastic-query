<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request;

use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestSort;
use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestStringDistance;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithMaxEdits;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithMaxInspections;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithMaxTermFreq;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithMinDocFreq;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithMinWordLength;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithPrefixLength;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithSize;
use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithSuggestMode;
use Webmozart\Assert\Assert;

class TermSuggester implements Suggester
{
    use WithSuggestMode;
    use WithSize;
    use WithMaxEdits;
    use WithPrefixLength;
    use WithMinWordLength;
    use WithMaxInspections;
    use WithMinDocFreq;
    use WithMaxTermFreq;

    // common suggest options
    protected ?string $text = null;
    protected ?string $analyzer = null;
    protected ?string $sort = null;

    // other term suggest options
    protected ?int $shardSize = null;
    protected ?string $stringDistance = null;

    public function __construct(protected string $name, protected string $field)
    {
        Assert::stringNotEmpty(trim($name));
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        return [
            "text" => $this->text,
            "term" => array_filter([
                "field" => $this->field,

                "analyzer" => $this->analyzer,
                "size" => $this->size,
                "sort" => $this->sort,
                "suggest_mode" => $this->suggestMode,

                "max_edits" => $this->maxEdits,
                "prefix_length" => $this->prefixLength,
                "min_word_length" => $this->minWordLength,
                "shard_size" => $this->shardSize,
                "max_inspections" => $this->maxInspections,
                "min_doc_freq" => $this->minDocFreq,
                "max_term_freq" => $this->maxTermFreq,
                "string_distance" => $this->stringDistance,
            ]),
        ];
    }

    public function name(): string
    {
        return $this->name;
    }

    public function text(string $text): static
    {
        Assert::stringNotEmpty(trim($text));

        $this->text = $text;

        return $this;
    }

    public function analyzer(string $analyzer): static
    {
        Assert::stringNotEmpty(trim($analyzer));

        $this->analyzer = $analyzer;

        return $this;
    }

    public function sortScore(): static
    {
        $this->sort = SuggestSort::SCORE;

        return $this;
    }

    public function sortFrequency(): static
    {
        $this->sort = SuggestSort::FREQUENCY;

        return $this;
    }

    public function shardSize(int $shardSize): static
    {
        $this->shardSize = $shardSize;

        return $this;
    }

    public function stringDistanceInternal(): static
    {
        $this->stringDistance = SuggestStringDistance::INTERNAL;

        return $this;
    }

    public function stringDistanceDamerauLevenshtein(): static
    {
        $this->stringDistance = SuggestStringDistance::DAMERAU_LEVENSHTEIN;

        return $this;
    }

    public function stringDistanceLevenshtein(): static
    {
        $this->stringDistance = SuggestStringDistance::LEVENSHTEIN;

        return $this;
    }

    public function stringDistanceJaroWinkler(): static
    {
        $this->stringDistance = SuggestStringDistance::JARO_WINKLER;

        return $this;
    }

    public function stringDistanceNgram(): static
    {
        $this->stringDistance = SuggestStringDistance::NGRAM;

        return $this;
    }
}
