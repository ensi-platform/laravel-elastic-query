<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request;

use Webmozart\Assert\Assert;

class PhraseSuggester implements Suggester
{
    // basic phrase suggest api parameters
    protected ?string $text = null;
    protected ?int $gramSize = null;
    protected ?float $realWordErrorLikelihood = null;
    protected ?float $confidence = null;
    protected ?float $maxErrors = null;
    protected ?string $separator = null;
    protected ?int $size = null;
    protected ?string $analyzer = null;
    protected ?int $shardSize = null;
    protected ?string $highlightPreTag = null;
    protected ?string $highlightPostTag = null;

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
                // todo collate
                // todo smoothing models
                // todo direct_generator
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

    public function gramSize(int $gramSize): self
    {
        $this->gramSize = $gramSize;

        return $this;
    }

    public function realWordErrorLikelihood(float $realWordErrorLikelihood): self
    {
        $this->realWordErrorLikelihood = $realWordErrorLikelihood;

        return $this;
    }

    public function confidence(float $confidence): self
    {
        $this->confidence = $confidence;

        return $this;
    }

    public function maxErrors(float $maxErrors): self
    {
        $this->maxErrors = $maxErrors;

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function analyzer(string $analyzer): self
    {
        $this->analyzer = $analyzer;

        return $this;
    }

    public function shardSize(int $shardSize): self
    {
        $this->shardSize = $shardSize;

        return $this;
    }

    public function highlight(string $highlightPreTag, string $highlightPostTag): self
    {
        $this->highlightPreTag = $highlightPreTag;
        $this->highlightPostTag = $highlightPostTag;

        return $this;
    }
}
