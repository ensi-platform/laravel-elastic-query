<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request\Concerns;

trait WithPrefixLength
{
    protected ?int $prefixLength = null;

    public function prefixLength(int $prefixLength): static
    {
        $this->prefixLength = $prefixLength;

        return $this;
    }
}
