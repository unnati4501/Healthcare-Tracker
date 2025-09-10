<?php
declare (strict_types = 1);

Route::get('list/{subcategory}', [
    'as'   => '.list',
    'uses' => 'MasterClassController@categoryMasterClass',
]);

Route::get('enrolled', [
    'as'   => '.enrolled',
    'uses' => 'MasterClassController@enrolledMasterClass',
]);

Route::get('completed', [
    'as'   => '.completed',
    'uses' => 'MasterClassController@completedMasterClass',
]);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'MasterClassController@savedMasterClass',
]);

Route::put('like-unlike/{course}', [
    'as'   => '.like-unlike',
    'uses' => 'MasterClassController@likeUnlikeMasterClass',
]);

Route::put('save-unsave/{course}', [
    'as'   => '.save-unsave',
    'uses' => 'MasterClassController@saveUnsaveMasterClass',
]);

Route::get('survey/{type}/{course}', [
    'as'   => '.survey',
    'uses' => 'MasterClassController@surveyMasterClass',
]);

Route::post('survey-submit/{survey}', [
    'as'   => '.survey-submit',
    'uses' => 'MasterClassController@submitMasterClassSurvey',
]);

Route::get('detail/{course}', [
    'as'   => '.detail',
    'uses' => 'MasterClassController@masterClassDetail',
]);

Route::get('lessons/{course}', [
    'as'   => '.lessons',
    'uses' => 'MasterClassController@getLessonsList',
]);

Route::put('lesson-status', [
    'as'   => '.lesson-status',
    'uses' => 'MasterClassController@mlessonStatusChange',
]);

Route::post('/csat', [
    'as'   => '.submitCsat',
    'uses' => 'MasterClassController@submitCsat',
]);
