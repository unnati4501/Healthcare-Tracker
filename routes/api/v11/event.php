<?php declare (strict_types = 1);

Route::get('/list/{type?}', [
    'as'   => '.list',
    'uses' => 'EventController@index',
]);

Route::get('/details/{event}', [
    'as'   => '.details',
    'uses' => 'EventController@detail',
]);

Route::put('/register/{event}', [
    'as'   => '.register',
    'uses' => 'EventController@register',
]);

Route::put('/cancel/{event}', [
    'as'   => '.cancel',
    'uses' => 'EventController@cancel',
]);
