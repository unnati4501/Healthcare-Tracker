<?php declare (strict_types = 1);

Route::get('/questions/{survey}/{hscategory}', [
    'as'   => '.questions',
    'uses' => 'HealthScoreController@getQuestions',
]);

Route::get('/surveyflag', [
    'as'   => '.surveyflag',
    'uses' => 'HealthScoreController@getSurveyFlag',
]);

Route::get('/lastsubmitedsurvey', [
    'as'   => '.lastsubmitedsurvey',
    'uses' => 'HealthScoreController@getLastSubmitedSurvey',
]);

Route::get('/history', [
    'as'   => '.history',
    'uses' => 'HealthScoreController@getSubmitedSurveyHistory',
]);

Route::post('/survey/{survey}/{hscategory}', [
    'as'   => '.survey',
    'uses' => 'HealthScoreController@submitSurvey',
]);

Route::put('/remind', [
    'as'   => '.remind',
    'uses' => 'HealthScoreController@remindSurveyLater',
]);

Route::get('/report/{survey}', [
    'as'   => '.report',
    'uses' => 'HealthScoreController@getHealthScoreReport',
]);
