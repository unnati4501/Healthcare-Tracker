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
    'uses' => 'CommonController@storeNPSFeedback',
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
