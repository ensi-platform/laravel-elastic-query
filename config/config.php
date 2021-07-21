<?php

/*
 * Конфигурация.
 */
return [
    'connection' => [

        /*
         * Elasticsearch hosts in format http[s]://[user][:pass]@hostname[:9200]
         */
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS')),
    ]
];
