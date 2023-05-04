<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request\Concerns;

trait WithMaxTermFreq
{
    protected ?int $maxTermFreq = null;

    public function maxTermFreq(int $maxTermFreq): static
    {
        $this->maxTermFreq = $maxTermFreq;

        return $this;
    }
}