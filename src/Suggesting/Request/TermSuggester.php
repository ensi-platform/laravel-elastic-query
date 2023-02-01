<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request;

use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestMode;
use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestSort;
use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestStringDistance;
use Webmozart\Assert\Assert;

class TermSuggester implements Suggester
{
    // common suggest options
    protected ?string $text = null;
    protected ?string $analyzer = null;
    protected ?int $size = null;
    protected ?string $sort = null;
    protected ?string $suggestMode = null;

    // other term suggest options
    protected ?int $maxEdits = null;
    protected ?int $prefixLength = null;
    protected ?int $minWordLength = null;
    protected ?int $shardSize = null;
    protected ?int $maxInspections = null;
    protected ?int $minDocFreq = null;
    protected ?int $maxTermFreq = null;
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

    public function text(string $text): self
    {
        Assert::stringNotEmpty(trim($text));

        $this->text = $text;

        return $this;
    }

    public function analyzer(string $analyzer): self
    {
        Assert::stringNotEmpty(trim($analyzer));

        $this->analyzer = $analyzer;

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function sortScore(): self
    {
        $this->sort = SuggestSort::SCORE;

        return $this;
    }

    public function sortFrequency(): self
    {
        $this->sort = SuggestSort::FREQUENCY;

        return $this;
    }

    public function suggestModeMissing(): self
    {
        $this->suggestMode = SuggestMode::MISSING;

        return $this;
    }

    public function suggestModePopular(): self
    {
        $this->suggestMode = SuggestMode::POPULAR;

        return $this;
    }

    public function suggestModeAlways(): self
    {
        $this->suggestMode = SuggestMode::ALWAYS;

        return $this;
    }

    public function maxEdits(int $maxEdits): self
    {
        Assert::range($maxEdits, 1, 2);

        $this->maxEdits = $maxEdits;

        return $this;
    }

    public function prefixLength(int $prefixLength): self
    {
        $this->prefixLength = $prefixLength;

        return $this;
    }

    public function minWordLength(int $minWordLength): self
    {
        $this->minWordLength = $minWordLength;

        return $this;
    }

    public function shardSize(int $shardSize): self
    {
        $this->shardSize = $shardSize;

        return $this;
    }

    public function maxInspections(int $maxInspections): self
    {
        $this->maxInspections = $maxInspections;

        return $this;
    }

    public function minDocFreq(int $minDocFreq): self
    {
        $this->minDocFreq = $minDocFreq;

        return $this;
    }

    public function maxTermFreq(int $maxTermFreq): self
    {
        $this->maxTermFreq = $maxTermFreq;

        return $this;
    }

    public function stringDistanceInternal(): self
    {
        $this->stringDistance = SuggestStringDistance::INTERNAL;

        return $this;
    }

    public function stringDistanceDamerauLevenshtein(): self
    {
        $this->stringDistance = SuggestStringDistance::DAMERAU_LEVENSHTEIN;

        return $this;
    }

    public function stringDistanceLevenshtein(): self
    {
        $this->stringDistance = SuggestStringDistance::LEVENSHTEIN;

        return $this;
    }

    public function stringDistanceJaroWinkler(): self
    {
        $this->stringDistance = SuggestStringDistance::JARO_WINKLER;

        return $this;
    }

    public function stringDistanceNgram(): self
    {
        $this->stringDistance = SuggestStringDistance::NGRAM;

        return $this;
    }
}
