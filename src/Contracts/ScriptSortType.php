<?php

namespace Ensi\LaravelElasticQuery\Contracts;

final class ScriptSortType
{
    public const NUMBER = 'number';

    public static function cases(): array
    {
        return [
            self::NUMBER,
        ];
    }
}
