<?php declare (strict_types = 1);

Route::get('/', [
    'as'   => '.index',
    'uses' => 'EAPController@index',
]);
Route::get('/details/{eap}', [
    'as'   => '.details',
    'uses' => 'EAPController@details',
]);
