<?php

return [
    'default' => env('MPESA_DEFAULT_APP', 'c2b'),
    'paybill_account' => env('PAYBILL_ACCOUNT'),
    'paybill_name' => env('PAYBILL_NAME'),
    'apps' => [
        'c2b' => [
            'status' => env('MPESA_API_STATUS'),
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_SHORTCODE'),
            'lipa_na_mpesa_passkey' => env('MPESA_PASSKEY'),
            'initiator_name' => env('MPESA_INITIATOR_NAME', 'initiator_name'),
            'initiator_password' => env('MPESA_INITIATOR_PASSWORD', 'initiator_password'),
        ],
        'b2c' => [
            'status' => env('MPESA_API_STATUS'),
            'consumer_key' => env('MPESA_B2C_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_B2C_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_B2C_SHORTCODE'),
            'lipa_na_mpesa_passkey' => env('MPESA_B2C_PASSKEY'),
            'initiator_name' => env('MPESA_B2C_INITIATOR_NAME', 'initiator_name'),
            'initiator_password' => env('MPESA_B2C_INITIATOR_PASSWORD', 'initiator_password'),
        ],
        'b2b' => [
            'status' => env('MPESA_API_STATUS'),
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_SHORTCODE'),
            'lipa_na_mpesa_passkey' => env('MPESA_PASSKEY'),
            'initiator_name' => env('MPESA_INITIATOR_NAME', 'initiator_name'),
            'initiator_password' => env('MPESA_INITIATOR_PASSWORD', 'initiator_password'),
        ]
    ],

];