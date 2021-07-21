<?php

namespace Greensight\LaravelElasticQuery\Raw\Concerns;

trait SupportsPath
{
    protected function basePath(): string
    {
        return '';
    }

    protected function absolutePath(string $path): string
    {
        $basePath = trim($this->basePath());
        $path = trim($path);

        return match (true) {
            strlen($basePath) === 0 => $path,
            strlen($path) === 0 => $basePath,
            default => "$basePath.$path"
        };
    }
}
