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
        'api_key' => env('ELASTICSEARCH_API_KEY', ''),

        'http_client' => null, // class implementing the \Psr\Http\Client\ClientInterface
        'http_client_options' => null, // for call_user_func_array

        'http_client_logger' => null, // for call_user_func_array, return class implementing the \Psr\Log\LoggerInterface

        'http_async_client' => null, // for call_user_func_array, return class implementing the \Http\Client\HttpAsyncClient
    ],
];
