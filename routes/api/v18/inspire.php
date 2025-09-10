<?php declare (strict_types = 1);

Route::post('/inspire-statistics/{categoryId}', [
    'as'   => '.inspire-statistics',
    'uses' => 'InspireController@getInspireStatistics',
]);

Route::get('/inspire-history/{categoryId}', [
    'as'   => '.inspire-history',
    'uses' => 'InspireController@getInspireHistory',
]);

Route::get('/inspire-data', [
    'as'   => '.inspire-data',
    'uses' => 'InspireController@getInspireData',
]);
