<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
     */

    'auth'   => [
        'wrong_credentials'  => 'These credentials do not match our records.',
        'failed'             => 'Invalid code entered. Please enter a valid code.',
        'throttle'           => 'Please request a new code.',
        'inactive'           => 'Sorry, your account has been blocked by admin.',
        'multilogin'         => 'You already logged in on another device. Do you want to disconnect that device?',
        'device_not_found'   => 'Your device has been disconnected by admin.',
        'allowed_company'    => 'Sorry, but you must use your official company email address',
        'multilogin_device'  => 'You already logged in on another device.',
        'device_access'      => 'You are already registered. Do you require mobile access?',
        'confirm'            => [
            'sent'    => 'Your account has been created. Please log in to access your account.',
            'failure' => 'Incorrect confirmation code',
            'success' => 'Your account has been confirmed!',
        ],
        'email_exist'        => 'Already have an account registered with this email? Please Login.',
        'passwords'          => [
            'password' => 'Passwords must be at least six characters and match the confirmation.',
            'reset'    => 'Your password has been reset!',
            'sent'     => 'We have e-mailed your password reset link!',
            'token'    => 'This password reset token is invalid.',
            'user'     => "We can't find a user with that e-mail address.",
            'email'    => 'Your email is invalid, Please contact to admin.',
            'getemail' => 'Email get successfully from token',
        ],
        'not_access_portal' => 'You don\'t have access to the portal, please contact the admin!',
        'not_access_app'    => 'You don\'t have access to the app, please contact the admin!',
        'portal_access'     => 'You are already registered. Do you require portal access?',
        'not_app_version'   => 'Appversion param is required!!!',
        'not_same_domain'   => 'You are trying to login with invalid domain, Please contact the admin!',
        'email_not_exists'  => 'Email does not exist',
        'sucess_otp_message'=> 'A 6 digit single use code has been sent to your email address. The code is valid only for ##VALID_TILL##',
        'invalid_otp'       => 'Invalid code entered. Please enter a valid code.',
        'otp_expired'       => 'The code you entered is expired. Please enter the most recently received code.',
        'otp_verified'      => 'Your single use code is verified.',
        'archive_user'      => 'This user account is removed by the admin. Please contact support@zevohealth.zendesk.com for further assistance.',
    ],
    'common' => [
        'something_wrong_try_again' => 'Something went wrong please try again.',
    ],
];
