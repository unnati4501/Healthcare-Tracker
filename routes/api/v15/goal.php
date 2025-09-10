<?php
declare(strict_types=1);

Route::get('list', [
    'as'         => '.list',
    'uses'       => 'GoalsController@list',
]);
