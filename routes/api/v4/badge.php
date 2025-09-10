<?php
declare(strict_types=1);

Route::get('/', [
    'as'   => '.active',
    'uses' => 'BadgeController@active',
]);

Route::get('/timeline', [
    'as'   => '.timeline',
    'uses' => 'BadgeController@timeline',
]);
