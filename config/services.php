<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun'  => [
        'domain' => '',
        'secret' => '',
    ],
    'mandrill' => [
        'secret' => '',
    ],
    'ses'      => [
        'key'    => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],
    'stripe'   => [
        'model'  => 'User',
        'secret' => '',
    ],

    // WebSocket server for Coyote.
    // ------------------------------------------------------

    'ws'        => [
        'host'   => env('WS_HOST'),
        'port'   => env('WS_PORT')
    ],

    // Elasticsearch host and port. In most cases default values will be suitable.
    // ---------------------------------------------------------------------------

    'elasticsearch' => [
        'host'   => env('ELASTICSEARCH_HOST', 'localhost'),
        'port'   => env('ELASTICSEARCH_PORT', 9200)
    ],

    // OAuth clients.
    // ------------------------------------------------------

    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_SECRET_ID'),
        'redirect'      => env('GITHUB_REDIRECT'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_SECRET_ID'),
        'redirect'      => env('GOOGLE_REDIRECT')
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_SECRET_ID'),
        'redirect'      => env('FACEBOOK_REDIRECT'),
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

    'braintree' => [
        'env'           => env('BRAINTREE_ENV'),
        'merchant_id'   => env('BRAINTREE_MERCHANT_ID'),
        'public_key'    => env('BRAINTREE_PUBLIC_KEY'),
        'private_key'   => env('BRAINTREE_PRIVATE_KEY')
    ],

    'p24' => [
        'client_id'     => env('P24_CLIENT_ID'),
        'payment_url'   => env('P24_PAYMENT_URL', 'https://sandbox.przelewy24.pl/trnDirect'),
        'verify_url'    => env('P24_VERIFY_URL', 'https://sandbox.przelewy24.pl/trnVerify'),
        'salt'          => env('P24_SALT')
    ],

    'recaptcha' => [
        'key'           => env('RECAPTCHA_KEY'),
        'secret'        => env('RECAPTCHA_SECRET')
    ]
];
