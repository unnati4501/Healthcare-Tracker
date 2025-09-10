<?php declare (strict_types = 1);

Route::get('/list/{type?}', [
    'as'   => '.list',
    'uses' => 'EventController@index',
]);

Route::get('/details/{eventbookinglogs}', [
    'as'   => '.details',
    'uses' => 'EventController@detail',
]);

Route::put('/register/{eventbookinglogs}', [
    'as'   => '.register',
    'uses' => 'EventController@register',
]);

Route::put('/cancel/{eventbookinglogs}', [
    'as'   => '.cancel',
    'uses' => 'EventController@cancel',
]);
