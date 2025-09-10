<?php
declare (strict_types = 1);

Route::post('register', [
    'as'   => '.register',
    'uses' => 'RegisterController@register',
]);

Route::post('login', [
    'as'   => '.login',
    'uses' => 'LoginController@login',
]);

Route::post('check-email', [
    'as'   => '.check-email',
    'uses' => 'LoginController@checkEmail',
]);

Route::post('logout', [
    'as'         => '.logout',
    'uses'       => 'LoginController@logout',
    'middleware' => ['auth:api'],
]);

Route::delete('/disconnect-device', [
    'as'   => '.disconnect-device',
    'uses' => 'LoginController@disconnectDevice',
]);

Route::post('/update-push-token', [
    'as'         => '.update-push-token',
    'uses'       => 'LoginController@updatePushToken',
    'middleware' => ['api'],
]);

Route::post('/forgot-password', [
    'as'         => '.forgot-password',
    'uses'       => 'ForgotPasswordController@sendResetLinkEmail',
    'middleware' => ['guest'],
]);

Route::delete('/delete-account', [
    'as'         => '.delete-account',
    'uses'       => 'LoginController@deleteAccount',
    'middleware' => ['api'],
]);

Route::get('course-categories', [
    'as'   => '.course-categories',
    'uses' => 'LoginController@courseCategories',
]);

Route::get('app-launch/{appVersion?}', [
    'as'   => '.app-launch',
    'uses' => 'LoginController@appLaunch',
]);

Route::post('/reset-password', [
    'as'         => '.reset-password',
    'uses'       => 'ForgotPasswordController@resetPassword',
    'middleware' => ['guest'],
]);

Route::post('/resetpassword/get-email', [
    'as'         => '.get-email',
    'uses'       => 'ForgotPasswordController@getEmail',
    'middleware' => ['guest'],
]);

Route::post('send-single-use-code', [
    'as'         => '.send-single-use-code',
    'uses'       => 'LoginController@sendSingleUseCode',
    'middleware' => ['guest'],
]);

Route::post('verify-single-use-code', [
    'as'         => '.send-single-use-code',
    'uses'       => 'LoginController@verifySingleUseCode',
    'middleware' => ['guest'],
]);
