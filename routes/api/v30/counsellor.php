<?php declare (strict_types = 1);

Route::get('/onboard/', [
    'as'   => '.index',
    'uses' => 'CounsellorController@index',
]);
Route::get('/session/list', [
    'as'   => '.index',
    'uses' => 'CounsellorController@sessionList',
]);
Route::get('/session/details/{calendly?}', [
    'as'   => '.index',
    'uses' => 'CounsellorController@sessionDetail',
]);
Route::post('/session/csat', [
    'as'   => '.submitCounsellorCsat',
    'uses' => 'CounsellorController@submitCounsellorCsat',
]);
