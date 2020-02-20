<?php

return [

    // WebSocket server for Coyote.
    // ------------------------------------------------------

    'ws'        => [
        'host'          => env('WS_HOST'),
        'port'          => env('WS_PORT')
    ],

    // Elasticsearch host and port. In most cases default values will be suitable.
    // ---------------------------------------------------------------------------

    'elasticsearch' => [
        'host'          => env('ELASTICSEARCH_HOST', 'localhost'),
        'port'          => env('ELASTICSEARCH_PORT', 9200)
    ],

    // OAuth clients.
    // ------------------------------------------------------

    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID', docker_secret('GITHUB_CLIENT_ID_FILE')),
        'client_secret' => env('GITHUB_SECRET_ID', docker_secret('GITHUB_SECRET_ID_FILE')),
        'redirect'      => env('GITHUB_REDIRECT'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID', docker_secret('GOOGLE_CLIENT_ID_FILE')),
        'client_secret' => env('GOOGLE_SECRET_ID', docker_secret('GOOGLE_SECRET_ID_FILE')),
        'redirect'      => env('GOOGLE_REDIRECT')
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID', docker_secret('FACEBOOK_CLIENT_ID_FILE')),
        'client_secret' => env('FACEBOOK_SECRET_ID', docker_secret('FACEBOOK_SECRET_ID_FILE')),
        'redirect'      => env('FACEBOOK_REDIRECT'),
    ],

    'mailgun' => [
        'domain'        => env('MAILGUN_DOMAIN'),
        'secret'        => env('MAILGUN_SECRET', docker_secret('MAILGUN_SECRET_FILE')),
    ],

    // Google maps key to show jobs locations.
    // -----------------------------------------------------------

    'google-maps' => [
        'key'           => 'AIzaSyCjPih0Ay15fPj2j6KOqqNn2Af902apRz8'
    ],

    // Host and port to geo-ip.pl microservice to geocode IP and city name.
    // ------------------------------------------------------------------------

    'geo-ip' => [
        'host'          => 'geo-ip.pl',
        'port'          => ''
    ],

    'paylane' => [
        'username'      => env('PAYLANE_USERNAME', docker_secret('PAYLANE_USERNAME_FILE')),
        'password'      => env('PAYLANE_PASSWORD', docker_secret('PAYLANE_PASSWORD_FILE')),
        'salt'          => env('PAYLANE_SALT', docker_secret('PAYLANE_SALT_FILE'))
    ],

    'p24' => [
        'client_id'     => env('P24_CLIENT_ID', docker_secret('P24_CLIENT_ID_FILE')),
        'payment_url'   => env('P24_PAYMENT_URL', 'https://sandbox.przelewy24.pl/trnDirect'),
        'verify_url'    => env('P24_VERIFY_URL', 'https://sandbox.przelewy24.pl/trnVerify'),
        'salt'          => env('P24_SALT', docker_secret('P24_SALT_FILE'))
    ],

    'recaptcha' => [
        'key'           => env('RECAPTCHA_KEY', docker_secret('RECAPTCHA_KEY_FILE')),
        'secret'        => env('RECAPTCHA_SECRET', docker_secret('RECAPTCHA_SECRET_FILE'))
    ],

    'api' => [
        'host'          => env('API_HOST', docker_secret('API_HOST_FILE'))
    ]
];
