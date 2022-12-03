<?php

namespace Ensi\LaravelElasticQuery\Contracts;

final class MissingValuesMode
{
    public const FIRST = '_first';
    public const LAST = '_last';

    public static function cases(): array
    {
        return [
            self::FIRST,
            self::LAST,
        ];
    }
}
