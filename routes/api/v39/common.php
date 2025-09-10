<?php
declare (strict_types = 1);

Route::post('/users', [
    'as'   => '.users',
    'uses' => 'CommonController@getAllUsers',
]);

Route::post('/departments', [
    'as'   => '.departments',
    'uses' => 'CommonController@getAllDepartments',
]);

Route::post('/teams', [
    'as'   => '.teams',
    'uses' => 'CommonController@getAllTeams',
]);

Route::post('share-content', [
    'as'   => '.share-content',
    'uses' => 'CommonController@shareContent',
]);

Route::post('/npsfeedback', [
    'as'   => '.npsfeedback',
    'uses' => 'CommonController@storeNPSAppTabFeedback',
]);

Route::get('team-members/{team}', [
    'as'   => '.teamMembers',
    'uses' => 'CommonController@getTeamMembers',
]);

Route::get('company-teams/{company}', [
    'as'   => '.companyTeams',
    'uses' => 'CommonController@getCompanyTeams',
]);

Route::get('home-statistics', [
    'as'   => '.home-statistics',
    'uses' => 'CommonController@getHomeStatistics',
]);

Route::get('/categories', [
    'as'   => '.categories',
    'uses' => 'CommonController@categories',
]);

Route::get('/sub-categories/{category}', [
    'as'   => '.getSubCategories',
    'uses' => 'CommonController@getSubCategories',
]);

Route::post('log-tracker', [
    'as'   => '.logTracker',
    'uses' => 'CommonController@logTracker',
]);

Route::put('/view-count/{id}/{modelType}', [
    'as'   => '.setViewCount',
    'uses' => 'CommonController@setViewCount',
]);

Route::get('home-leaderboard', [
    'as'   => '.home-leaderboard',
    'uses' => 'CommonController@homeLeaderboard',
]);

Route::get('recommendation', [
    'as'   => '.recommendation',
    'uses' => 'CommonController@getRecommendation',
]);

Route::post('/wellbeing-statistics/', [
    'as'   => '.wellbeing-statistics',
    'uses' => 'CommonController@getWellbeingStatistics',
]);

Route::post('/categories-wellbeing-statistics/', [
    'as'   => '.categories-wellbeing-statistics',
    'uses' => 'CommonController@getCategoriesWellbeingStatistics',
]);

Route::get('saved-content-images', [
    'as'   => '.saved-content-images',
    'uses' => 'CommonController@getSavedContentImages',
]);
