<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cronofy Authenticate Credentials
    |--------------------------------------------------------------------------
    |
    | Cronofy Authenticate Credentials for calling cronofy APIs
    |
     */
    'client_id'              => env('CRONOFY_CLIENT_ID', ''),
    'client_secret'          => env('CRONOFY_CLIENT_SECRET', ''),
    'data_center'            => env('CRONOFY_DATA_CENTER', ''),
    'redirect_uri'           => env('APP_URL') . 'en/admin/cronofy/callback',

    /**
     * Cronofy real time scheduling params
     */
    'schedule_duration'      => 30,
    'feature_booking'        => 14,
    'eventIdPrefix'          => 'zevolife_dt_',
    'redirectUri'            => env('APP_URL'),
    'serviceName'            => 'Zevolife',
    'callbackUrl'            => env('APP_URL') . 'digitalTherapy/callback',
    'sessionUpdate'          => 20, // In minute
    'serviceName'            => 'Coaching',
    'advanceBooking'         => 0,
    'timezone'               => 'Asia/Kolkata',
    'sessionUser'            => 0,
    'sessionCompany'         => 0,
    'backend_redirect_url'   => env('APP_URL') . 'admin/cronofy/sessions/callback',
    'rescheduledCallbackUrl' => env('APP_URL') . 'digitalTherapy/rescheduledCallback',
    'dtSessionRulesMins'     => [
        15 => "15 Mins",
        30 => "30 Mins",
        45 => "45 Mins",
        50 => "50 Mins",
        60 => "60 Mins",
    ],
    'colors'                 => [
        'available'        => 'var(--green)',
        'unavailable'      => 'var(--red)',
        'buttonActive'     => 'var(--primary-color)',
        'buttonTextHover'  => '#fff',
        'buttonHover'      => 'var(--primary-color-darken)',
        'buttonText'       => '#000',
        'buttonConfirm'    => 'var(--primary-color)',
        'buttonActiveText' => '#fff',
    ],
    'availableRuleId'        => 'default',
    'buffer'                 => [
        'before' => 0,
        'after'  => 0,
    ],
    // Showing Max Result for mobile real time scheduling
    'max_result'  => 512,
];