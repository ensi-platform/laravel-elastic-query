<?php

namespace Greensight\LaravelElasticQuery\Raw\Contracts;

interface SearchIndex
{
    /**
     * Возвращает имя поля, содержащего уникальные значения в пределах индекса.
     *
     * @return string
     */
    public function tiebreaker(): string;

    /**
     * Выполняет поисковый запрос.
     *
     * @param array $dsl
     * @return array
     */
    public function search(array $dsl): array;
}
