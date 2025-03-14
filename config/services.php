<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'guardian' => [
        'api_key' => env('GUARDIAN_API_KEY'),
        'api_endpoint' =>  'https://content.guardianapis.com/search'
    ],

    'newsapi' => [
        'api_key' => env('NEWSAPI_KEY'),
        'api_endpoint' =>  'https://newsapi.org/v2/top-headlines'
    ],

    'nytimes' => [
    'api_key' => env('NYTIMES_API_KEY'),
    'api_secret' => env('NYTIMES_API_SECRET_KEY'),
    'api_endpoint' =>  'https://api.nytimes.com/svc/search/v2/articlesearch.json'
    ],

];
