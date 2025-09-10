<?php declare (strict_types = 1);

Route::get('/', [
    'as'   => '.moods',
    'uses' => 'MoodsController@getMoods',
]);

Route::get('/tags', [
    'as'   => '.tags',
    'uses' => 'MoodsController@getTags',
]);

Route::post('/save', [
    'as'   => '.save',
    'uses' => 'MoodsController@submitMood',
]);

Route::get('/survey/{range}', [
    'as'   => '.graph',
    'uses' => 'MoodsController@getGraphData',
]);

Route::get('/history/{year}/{month}', [
    'as'   => '.history',
    'uses' => 'MoodsController@getHistory',
]);
