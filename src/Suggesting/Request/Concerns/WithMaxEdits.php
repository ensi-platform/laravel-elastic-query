<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request\Concerns;

use Webmozart\Assert\Assert;

trait WithMaxEdits
{
    protected ?int $maxEdits = null;

    public function maxEdits(int $maxEdits): static
    {
        Assert::range($maxEdits, 1, 2);

        $this->maxEdits = $maxEdits;

        return $this;
    }
}
