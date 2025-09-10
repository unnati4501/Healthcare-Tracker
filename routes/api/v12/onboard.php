<?php
declare(strict_types=1);

Route::get('sliders', [
    'as'         => '.sliders',
    'uses'       => 'OnboardController@sliders',
]);

Route::get('survey', [
    'as'         => '.survey',
    'uses'       => 'OnboardController@survey',
]);

Route::post('submit-survey', [
    'as'         => '.survey',
    'uses'       => 'OnboardController@submitSurvey',
]);
