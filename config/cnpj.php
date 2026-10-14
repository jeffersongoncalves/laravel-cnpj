<?php

// config for JeffersonGoncalves/Cnpj
return [
    /*
    |--------------------------------------------------------------------------
    | Minha Receita URL
    |--------------------------------------------------------------------------
    |
    | Base URL of the Minha Receita API. Minha Receita is open source and can
    | be self-hosted (https://github.com/cuducos/minha-receita), so point this
    | to your own instance to avoid the public server rate limits.
    |
    */
    'url' => env('CNPJ_URL', 'https://minhareceita.org'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for the HTTP request.
    |
    */
    'timeout' => 10,

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Whether the TLS certificate of the API should be verified. This should
    | always remain true in production.
    |
    */
    'verify_ssl' => true,

    /*
    |--------------------------------------------------------------------------
    | Database Cache TTL
    |--------------------------------------------------------------------------
    |
    | The number of seconds a CNPJ stored in the database is considered fresh.
    | When the stored record is older than this value it will be fetched again
    | from the API. Use null to cache the records forever.
    |
    */
    'cache_ttl' => null,
];
