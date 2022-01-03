<?php

namespace Ensi\LaravelElasticQuery\Contracts;

final class MatchType
{
    public const BEST_FIELDS = 'best_fields';
    public const MOST_FIELDS = 'most_fields';
    public const CROSS_FIELDS = 'cross_fields';
    public const PHRASE = 'phrase';
    public const PHRASE_PREFIX = 'phrase_prefix';
    public const BOOL_PREFIX = 'bool_prefix';

    public static function cases(): array
    {
        return [
            self::BEST_FIELDS,
            self::MOST_FIELDS,
            self::CROSS_FIELDS,
            self::PHRASE,
            self::PHRASE_PREFIX,
            self::BOOL_PREFIX,
        ];
    }
}