<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Non-Auth Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during non-auth for various
    | messages that we need to display to the user.
    |
     */

    /*
    |--------------------------------------------------------------------------
    | Login page constants
    |--------------------------------------------------------------------------
     */
    'login'             => [
        'form'     => [
            'placeholders' => [
                'email'    => 'E-Mail Address',
                'password' => 'Password',
                'remember' => 'Remember Me',
            ],
        ],
        'links'    => [
            'forgot' => 'Forgot Your Password?',
        ],
        'buttons'  => [
            'login'          => 'Login',
            'microsoft'      => 'Microsoft',
            'login_with_2fa' => "Login with 2FA",
        ],
        'texts'    => [
            'or' => 'OR LOGIN WITH',
        ],
        'messages' => [
            'company_status'          => 'Your company subscription has expired. Please contact your admin or the Zevo Account Manager',
            'not_registered'          => 'These credentials do not match our records.',
            'not_authorized'          => 'You are not authorized to login from here.',
            'successfully_authorized' => 'You are successfully login',
        ],
        'popup'    => [
            'verify_your_email' => 'Verify Your Email',
            '6-digit_code'      => 'A 6-digit code has been send to',
            'change'            => 'Change',
            'validate'          => 'The code is valid for 15 minutes',
            'validate1'         => 'Didn\'t receive the code?',
            'resend_again'      => 'Resend again in',
            'resend'            => 'Resend',
            'verify'            => 'Verify',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Forgot password page constants
    |--------------------------------------------------------------------------
     */
    'forgot-password'   => [
        'form'     => [
            'placeholders' => [
                'email' => 'E-Mail Address',
            ],
        ],
        'links'    => [
            'login' => 'Sign In',
        ],
        'buttons'  => [
            'reset' => 'Send Password Reset Link',
        ],
        'texts'    => [
            'remember' => 'Did you remember your password?',
        ],
        'messages' => [
            'registered_email' => 'You will receive an email if you are registered with system.',
            'user_blocked'     => 'User is blocked.',
            'not_authorized'   => 'You are not authorized to reset password from here.',
            'limit_exceed'     => 'Maximum number of send email to reset password exceed. Please try after 5 minutes'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset password page constants
    |--------------------------------------------------------------------------
     */
    'reset-password'    => [
        'form'     => [
            'placeholders' => [
                'email'            => 'E-Mail Address',
                'password'         => 'Password',
                'confirm_password' => 'Confirm Password',
            ],
        ],
        'buttons'  => [
            'reset' => 'Reset Password',
        ],
        'messages' => [
            'password_changed' => 'Password change successfully.',
            'invalid_email'    => 'Invalid email address.',
            'token_mismatch'   => 'Token missmatch.',
            'something_wrong'  => 'Something went wrong.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Set password page constants
    |--------------------------------------------------------------------------
     */
    'set-password'      => [
        'form'     => [
            'placeholders' => [
                'email'            => 'E-Mail Address',
                'password'         => 'Password',
                'confirm_password' => 'Confirm Password',
            ],
        ],
        'buttons'  => [
            'set' => 'Set Password',
        ],
        'messages' => [
            'registration_with_link_error' => 'Invalid link. Please contact your administrator.',
            'already_confirmed'            => 'You have already verified your account. Kindly login to proceed.',
            'user_blocked'                 => 'User has been blocked, Please contact your administrator.',
            'registration_with_link'       => 'You are successfully registered. Please login to continue.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect app user page constants
    |--------------------------------------------------------------------------
     */
    'redirect-app-user' => [
        'texts' => [
            'your'           => 'Your',
            'password_reset' => 'Password has been reset.',
            'or'             => 'OR',
            'login_mobile'   => 'Please login using the mobile app.',
            'can_download'   => 'You can download the',
            'app_from'       => 'app from below link.',
        ],
        'links' => [
            'login'   => 'Back to Login',
            'android' => 'Android',
            'apple'   => 'Apple',
        ],
    ],
];
