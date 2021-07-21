<?php

namespace Greensight\LaravelElasticQuery\Raw\Search\Sorting;

use Greensight\LaravelElasticQuery\Raw\Contracts\DSLAware;
use Greensight\LaravelElasticQuery\Raw\Search\Cursor;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class SortCollection implements DSLAware
{
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function toDSL(): array
    {
        return $this->items
            ->map(fn (Sort $sort) => $sort->toDSL())
            ->values()
            ->all();
    }

    public function add(Sort $sort): void
    {
        $field = $sort->field();
        Assert::false($this->items->has($field), "По полю \"$field\" уже задана сортировка");

        $this->items->put($field, $sort);
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function keys(): array
    {
        return $this->items
            ->map(fn (Sort $sort) => (string)$sort)
            ->values()
            ->all();
    }

    public function invert(): static
    {
        $result = new static();
        $result->items = $this->items->map(fn (Sort $sort) => $sort->invert());

        return $result;
    }

    public function withTiebreaker(string $field): static
    {
        $result = new static();
        $result->items = Collection::wrap($this->items);

        if (!$result->items->has($field)) {
            $result->add(new Sort($field));
        }

        return $result;
    }

    public function createCursor(?array $hit): ?Cursor
    {
        if ($hit === null) {
            return null;
        }

        $values = $hit['sort'] ?? [];

        if (count($values) !== $this->items->count()) {
            throw new InvalidArgumentException('Количество полей сортировки не совпадает с курсором');
        }

        return new Cursor(
            array_combine($this->keys(), $values)
        );
    }

    public function matchCursor(Cursor $cursor): bool
    {
        return $cursor->isBOF() || $this->keys() === $cursor->keys();
    }
}
