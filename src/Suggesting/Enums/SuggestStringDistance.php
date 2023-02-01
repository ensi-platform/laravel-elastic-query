<?php

namespace Ensi\LaravelElasticQuery\Suggesting\Enums;

final class SuggestStringDistance
{
    public const INTERNAL = 'internal';
    public const DAMERAU_LEVENSHTEIN = 'damerau_levenshtein';
    public const LEVENSHTEIN = 'levenshtein';
    public const JARO_WINKLER = 'jaro_winkler';
    public const NGRAM = 'ngram';

    public static function cases(): array
    {
        return [
            self::INTERNAL,
            self::DAMERAU_LEVENSHTEIN,
            self::LEVENSHTEIN,
            self::JARO_WINKLER,
            self::NGRAM,
        ];
    }
}
