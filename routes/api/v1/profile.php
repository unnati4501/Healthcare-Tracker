<?php
declare(strict_types=1);

Route::post('/edit', [
    'as'   => '.edit',
    'uses' => 'ProfileController@edit',
]);

Route::get('/', [
    'as'   => '.detail',
    'uses' => 'ProfileController@detail',
]);

Route::get('/user-settings', [
    'as'   => '.user-settings',
    'uses' => 'ProfileController@userSettings',
]);

Route::post('/user-settings', [
    'as'   => '.change-user-settings',
    'uses' => 'ProfileController@changeUserSettings',
]);

Route::get('/notification-settings', [
    'as'   => '.notification-settings',
    'uses' => 'ProfileController@notificationSettings',
]);

Route::post('/notification-settings', [
    'as'   => '.edit-notification-settings',
    'uses' => 'ProfileController@editNotificationSettings',
]);

Route::post('/change-expertise-level', [
    'as'   => '.change-expertise-level',
    'uses' => 'ProfileController@changeActivityLevel',
]);
