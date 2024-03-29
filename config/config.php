<?php

return [
    'connection' => [
        /*
         * Elasticsearch hosts in format http[s]://[user][:pass]@hostname[:9200]
         */
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', '')),

        'retries' => env('ELASTICSEARCH_RETRIES', 1),
        'username' => env('ELASTICSEARCH_USERNAME', ''),
        'password' => env('ELASTICSEARCH_PASSWORD', ''),
        'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),

        'http_client' => null, // class implementing the \Psr\Http\Client\ClientInterface
        'http_client_options' => null, // for call_user_func_array
    ],
];
