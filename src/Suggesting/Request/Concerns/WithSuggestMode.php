<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Request\Concerns;

use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestMode;

trait WithSuggestMode
{
    protected ?string $suggestMode = null;

    public function suggestModeMissing(): static
    {
        $this->suggestMode = SuggestMode::MISSING;

        return $this;
    }

    public function suggestModePopular(): static
    {
        $this->suggestMode = SuggestMode::POPULAR;

        return $this;
    }

    public function suggestModeAlways(): static
    {
        $this->suggestMode = SuggestMode::ALWAYS;

        return $this;
    }
}
