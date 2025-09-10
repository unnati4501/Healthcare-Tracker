<?php
declare (strict_types = 1);

Route::get('sliders/{type?}', [
    'as'   => '.sliders',
    'uses' => 'OnboardController@sliders',
]);
Route::get('survey/{surveyLog?}', [
    'as'   => '.survey',
    'uses' => 'OnboardController@survey',
]);
Route::post('submit-survey', [
    'as'   => '.survey',
    'uses' => 'OnboardController@submitSurvey',
]);
Route::post('survey/feedback', [
    'as'   => '.survey',
    'uses' => 'OnboardController@submitSurveyFeedback',
]);
Route::post('verify-company-code', [
    'as'   => '.verify-company-code',
    'uses' => 'OnboardController@verifyCompanyCode',
]);
Route::get('locations/{company?}', [
    'as'   => '.locations',
    'uses' => 'OnboardController@getLocations',
]);
Route::get('departments/{location}', [
    'as'   => '.departments',
    'uses' => 'OnboardController@getDepartments',
]);
Route::get('teams/{location}/{department}', [
    'as'   => '.teams',
    'uses' => 'OnboardController@getTemas',
]);
