<?php

namespace Ensi\LaravelElasticQuery\Contracts;

final class ScriptLang
{
    public const PAINLESS = 'painless';
    public const MUSTACHE = 'mustache';

    public static function cases(): array
    {
        return [
            self::PAINLESS,
            self::MUSTACHE,
        ];
    }
}
