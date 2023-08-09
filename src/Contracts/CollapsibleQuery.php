<?php

namespace Ensi\LaravelElasticQuery\Contracts;

interface CollapsibleQuery extends BoolQuery
{
    public function collapse(string $field): static;
}
