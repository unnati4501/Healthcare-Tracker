<?php
declare(strict_types=1);

Route::post('/log-weight', [
    'as'   => '.log-weight',
    'uses' => 'NourishController@logWeight',
]);


Route::get('/nourish-data', [
    'as'   => '.nourish-data',
    'uses' => 'NourishController@getNourishData',
]);

Route::post('/weight-statistics', [
    'as'   => '.weight-statistics',
    'uses' => 'NourishController@getWeightGraph',
]);

Route::post('/bmi-statistics', [
    'as'   => '.bmi-statistics',
    'uses' => 'NourishController@getBmiGraph',
]);

Route::get('/weight-history', [
    'as'   => '.weight-history',
    'uses' => 'NourishController@getWeightHistory',
]);

Route::get('/bmi-history', [
    'as'   => '.bmi-history',
    'uses' => 'NourishController@getBmiHistory',
]);

Route::post('/calorie-statistics', [
    'as'   => '.calorie-statistics',
    'uses' => 'NourishController@getCalorieGraph',
]);

Route::get('/calorie-history', [
    'as'   => '.calorie-history',
    'uses' => 'NourishController@getCalorieHistory',
]);
