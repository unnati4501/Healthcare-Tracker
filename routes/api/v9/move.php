<?php
declare(strict_types=1);

Route::post('/steps', [
    'as'   => '.steps',
    'uses' => 'MoveController@getSteps',
]);

Route::post('/steps-me-company', [
    'as'   => '.steps-me-company',
    'uses' => 'MoveController@getStepsMeCompany',
]);

Route::get('/exercises', [
    'as'   => '.exercises',
    'uses' => 'MoveController@getExercises',
]);

Route::post('/exercise-history', [
    'as'   => '.exercise-history',
    'uses' => 'MoveController@getExerciseHistory',
]);

Route::post('/track-exercise/{exercise}', [
    'as'   => '.track-exercise',
    'uses' => 'MoveController@trackExercise',
]);

Route::delete('/untrack-exercise/{userExercise}', [
    'as'   => '.untrack-exercise',
    'uses' => 'MoveController@unTrackExercise',
]);

Route::post('/sync-exercise/{startDate}', [
    'as'   => '.sync-exercise',
    'uses' => 'MoveController@syncExercise',
]);

Route::post('/sync-steps', [
    'as'   => '.sync-steps',
    'uses' => 'MoveController@syncSteps',
]);
