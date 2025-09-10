<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAML Callback URL
    |--------------------------------------------------------------------------
    |
    | SAML callback url for fetching SAMLResponse
    |
     */
    'callback_url'    => env('SAML_CALLBACK_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | SAML Destination URL
    |--------------------------------------------------------------------------
    |
    | SAML destination url for sending SAMLRequest to IDP
    |
     */
    'destination_url' => env('SAML_DESTINATION_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | SAML Entity ID
    |--------------------------------------------------------------------------
    |
    | SAML unique entity ID
    |
     */
    'entity_id'       => env('SAML_ENTITY_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | SAML Logout Url
    |--------------------------------------------------------------------------
    |
    | SAML logout url to logout from AD
    |
     */
    'logout_url'      => env('SAML_LOGOUT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | SAML subdomains
    |--------------------------------------------------------------------------
    |
    | List of subdomains where saml flow is allowed
    |
     */
    'subdomains'      => env('SAML_SUBDOMAINS', ''),
];
