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
    'bestbulksms' => [
        'send_url'     => env('BESTBULKSMS_SEND_URL', 'https://www.bestbulksms.com.ng/api/sms/send'),
        'balance_url'  => env('BESTBULKSMS_BALANCE_URL', 'https://www.bestbulksms.com.ng/api/balance.php'),
        'status_url'   => env('BESTBULKSMS_STATUS_URL', 'https://www.bestbulksms.com.ng/api/message-status.php'),
        'api_key'      => env('BESTBULKSMS_API_KEY'),
        'sender_id'    => env('BESTBULKSMS_SENDER_ID'),
        'route'        => env('BESTBULKSMS_ROUTE', 'standard'),
        'source_url'   => env('BESTBULKSMS_SOURCE_URL'),
        // Local numbers (leading 0) are expanded with this dialling code. Leave blank to disable.
        'country_code' => env('BESTBULKSMS_COUNTRY_CODE', '234'),
    ],
];
