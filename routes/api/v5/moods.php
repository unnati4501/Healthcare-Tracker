<?php declare (strict_types = 1);

Route::get('/', [
    'as'   => '.moods',
    'uses' => 'MoodsController@getMoods',
]);

Route::post('/save', [
    'as'   => '.save',
    'uses' => 'MoodsController@submitMood',
]);
