<?php
declare (strict_types = 1);

Route::get('/', [
    'as'   => '.active',
    'uses' => 'BadgeController@active',
]);

Route::get('/timeline/{type}/{badge}', [
    'as'   => '.timeline',
    'uses' => 'BadgeController@badgelistdetails',
]);

Route::get('/details/{badgeUserId}', [
    'as'   => '.details',
    'uses' => 'BadgeController@details',
]);
