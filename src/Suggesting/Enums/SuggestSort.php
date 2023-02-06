<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Enums;

final class SuggestSort
{
    public const SCORE = 'score';
    public const FREQUENCY = 'frequency';

    public static function cases(): array
    {
        return [
            self::SCORE,
            self::FREQUENCY,
        ];
    }
}
