<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EAP Calendly personal access token
    |--------------------------------------------------------------------------
    |
    | EAP personal access token for calling calendly APIs
    |
     */
    'calendly_token' => env('EAP_CALENDLY_PERSONAL_ACCESS_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Zendesk
    |--------------------------------------------------------------------------
    |
    | Creds for calling zendesk API
    |
     */
    'zd_subdomain'   => env('EAP_ZD_SUBDOMAIN', ''),
    'zd_username'    => env('EAP_ZD_USERNAME', ''),
    'zd_token'       => env('EAP_ZD_TOKEN', ''),
];
