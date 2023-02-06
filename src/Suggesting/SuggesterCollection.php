<?php

namespace Ensi\LaravelElasticQuery\Suggesting;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Suggesting\Request\Suggester;
use Ensi\LaravelElasticQuery\Suggesting\Response\SuggestData;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SuggesterCollection implements DSLAware
{
    private Collection $suggesters;

    public function __construct()
    {
        $this->suggesters = new Collection();
    }

    public function add(Suggester $suggester): void
    {
        if ($this->suggesters->has($suggester->name())) {
            throw new InvalidArgumentException("Suggester {$suggester->name()} already exists in collection");
        }

        $this->suggesters->put($suggester->name(), $suggester);
    }

    public function isEmpty(): bool
    {
        return $this->suggesters->isEmpty();
    }

    public function count(): int
    {
        return $this->suggesters->count();
    }

    public function merge(SuggesterCollection $source): void
    {
        $source->suggesters->each(fn (Suggester $s) => $this->add($s));
    }

    public function toDSL(): array
    {
        return $this->suggesters
            ->mapWithKeys(fn (Suggester $s) => [$s->name() => $s->toDSL()])
            ->all();
    }

    public function parseResults(array $response): Collection
    {
        return $this->suggesters
            ->mapWithKeys(fn (Suggester $s) => [
                $s->name() => collect($response[$s->name()])->transform(fn (array $i) => SuggestData::makeFromArray($i)),
            ]);
    }
}
