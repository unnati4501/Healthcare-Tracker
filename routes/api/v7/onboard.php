<?php
declare(strict_types=1);

Route::get('sliders', [
    'as'         => '.sliders',
    'uses'       => 'OnboardController@sliders',
]);
