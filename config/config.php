<?php

/*
 * Конфигурация.
 */
return [
    'connection' => [

        /*
         * Определяет хосты для подключения в формате http[s]://[user][:pass]@hostname[:9200]
         */
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS')),
    ]
];
