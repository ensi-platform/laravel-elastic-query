<?php

return [
    'connection' => [
        /*
         * Elasticsearch hosts in format http[s]://[user][:pass]@hostname[:9200]
         */
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'https://localhost:9200')),
        'retries' => env('ELASTICSEARCH_RETRIES', 2),
        'credentials' => [
            'username' => env('ELASTICSEARCH_USERNAME', 'admin'),
            'password' => env('ELASTICSEARCH_PASSWORD', 'admin'),
        ],
        'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),
    ]
];
