<?php

namespace Greensight\LaravelElasticQuery\Search;

use Greensight\LaravelElasticQuery\Contracts\DSLAware;

class Cursor implements DSLAware
{
    public function __construct(private array $parts)
    {
    }

    public function isBOF(): bool
    {
        return !$this->parts;
    }

    public function toDSL(): array
    {
        return array_values($this->parts);
    }

    public function encode(): string
    {
        return base64_encode(json_encode($this->parts));
    }

    public function keys(): array
    {
        return array_keys($this->parts);
    }

    public static function decode(?string $source): ?static
    {
        return blank($source)
            ? null
            : new Cursor(json_decode(base64_decode($source), true));
    }

    public static function BOF(): static
    {
        return new static([]);
    }
}
