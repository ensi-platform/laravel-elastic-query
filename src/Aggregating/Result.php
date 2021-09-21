<?php

namespace Greensight\LaravelElasticQuery\Aggregating;

class Result
{
    public static function parseValue(array $source): mixed
    {
        return self::parse($source, 'value');
    }

    public static function parseBucket(array $source): Bucket
    {
        return new Bucket(self::parse($source, 'key'), (int)($source['doc_count'] ?? 0));
    }

    public static function parse(array $source, string $key): mixed
    {
        $stringValue = $source["{$key}_as_string"] ?? null;

        if ($stringValue === null) {
            return $source[$key] ?? null;
        }

        if ($stringValue === 'true') {
            return true;
        }

        if ($stringValue === 'false') {
            return false;
        }

        return $stringValue;
    }
}
