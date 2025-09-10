<?php declare (strict_types = 1);

Route::get('/', [
    'as'   => '.index',
    'uses' => 'DigitalTherapyController@index',
]);

Route::get('topicListing/{service}', [
    'as'   => '.topicListing',
    'uses' => 'DigitalTherapyController@topicListing',
]);

Route::get('counsellorListing/{serviceSubCategory}', [
    'as'   => '.counsellorListing',
    'uses' => 'DigitalTherapyController@counsellorListing',
]);

Route::post('realTimeScheduling/{user}', [
    'as'         => '.realTimeScheduling',
    'uses'       => 'DigitalTherapyController@realTimeScheduling',
    'middleware' => ['cronofyAuthenticate'],
]);

Route::get('appointmentList', [
    'as'   => '.appointmentList',
    'uses' => 'DigitalTherapyController@appointmentList',
]);

Route::get('appointment/details/{cronofySchedule?}', [
    'as'   => '.index',
    'uses' => 'DigitalTherapyController@appointmentDetail',
]);

Route::delete('appointment/cancel-session/{cronofySchedule?}', [
    'as'         => '.index',
    'uses'       => 'DigitalTherapyController@appointmentCancel',
    'middleware' => ['cronofyAuthenticate'],
]);

Route::put('appointment/cancel-session-portal/{cronofySchedule?}', [
    'as'         => '.index',
    'uses'       => 'DigitalTherapyController@appointmentCancel',
    'middleware' => ['cronofyAuthenticate'],
]);

Route::put('appointment/cancel-session-portal/{cronofySchedule?}', [
    'as'   => '.index',
    'uses' => 'DigitalTherapyController@appointmentCancel',
]);

Route::get('appointment/reschedule/{cronofySchedule?}', [
    'as'         => '.index',
    'uses'       => 'DigitalTherapyController@appointmentReschedule',
    'middleware' => ['cronofyAuthenticate'],
]);

Route::post('appointment/reschedule-portal/{cronofySchedule?}', [
    'as'         => '.index',
    'uses'       => 'DigitalTherapyController@appointmentReschedule',
    'middleware' => ['cronofyAuthenticate'],
]);

Route::post('addNotes/{cronofySchedule?}', [
    'as'   => '.addNotes',
    'uses' => 'DigitalTherapyController@addNotes',
]);
