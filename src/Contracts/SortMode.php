<?php

namespace Greensight\LaravelElasticQuery\Contracts;

final class SortMode
{
    public const MIN = 'min';
    public const MAX = 'max';
    public const AVG = 'avg';
    public const SUM = 'sum';
    public const MEDIAN = 'median';

    public static function cases(): array
    {
        return [
            self::MAX,
            self::MIN,
            self::AVG,
            self::SUM,
            self::MEDIAN,
        ];
    }
}