<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request;

use Ensi\LaravelElasticQuery\Suggesting\Request\Concerns\WithSize;
use Webmozart\Assert\Assert;

class PhraseSuggester implements Suggester
{
    use WithSize;

    // basic phrase suggest api parameters
    protected ?string $text = null;
    protected ?int $gramSize = null;
    protected ?float $realWordErrorLikelihood = null;
    protected ?float $confidence = null;
    protected ?float $maxErrors = null;
    protected ?string $separator = null;
    protected ?string $analyzer = null;
    protected ?int $shardSize = null;
    protected ?string $highlightPreTag = null;
    protected ?string $highlightPostTag = null;
    protected array $directGenerators = [];

    public function __construct(protected string $name, protected string $field)
    {
        Assert::stringNotEmpty(trim($name));
        Assert::stringNotEmpty(trim($field));
    }

    public function toDSL(): array
    {
        return [
            "text" => $this->text,
            "phrase" => array_filter([
                "field" => $this->field,

                "gram_size" => $this->gramSize,
                "real_word_error_likelihood" => $this->realWordErrorLikelihood,
                "confidence" => $this->confidence,
                "max_errors" => $this->maxErrors,
                "separator" => $this->separator,
                "size" => $this->size,
                "analyzer" => $this->analyzer,
                "shard_size" => $this->shardSize,
                "highlight" => array_filter([
                    "pre_tag" => $this->highlightPreTag,
                    "post_tag" => $this->highlightPostTag,
                ]) ?: null,
                "direct_generator" => array_map(fn (DirectGenerator $g) => $g->toDSL(), $this->directGenerators),
                // todo collate
                // todo smoothing models
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

    public function gramSize(int $gramSize): static
    {
        $this->gramSize = $gramSize;

        return $this;
    }

    public function realWordErrorLikelihood(float $realWordErrorLikelihood): static
    {
        $this->realWordErrorLikelihood = $realWordErrorLikelihood;

        return $this;
    }

    public function confidence(float $confidence): static
    {
        $this->confidence = $confidence;

        return $this;
    }

    public function maxErrors(float $maxErrors): static
    {
        $this->maxErrors = $maxErrors;

        return $this;
    }

    public function separator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    public function analyzer(string $analyzer): static
    {
        $this->analyzer = $analyzer;

        return $this;
    }

    public function shardSize(int $shardSize): static
    {
        $this->shardSize = $shardSize;

        return $this;
    }

    public function highlight(string $highlightPreTag, string $highlightPostTag): static
    {
        $this->highlightPreTag = $highlightPreTag;
        $this->highlightPostTag = $highlightPostTag;

        return $this;
    }

    public function addDirectGenerator(DirectGenerator $directGenerator): static
    {
        $this->directGenerators[] = $directGenerator;

        return $this;
    }
}
