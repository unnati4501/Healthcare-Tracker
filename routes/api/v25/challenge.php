<?php
declare (strict_types = 1);

Route::put('/leave/{challenge}', [
    'as'   => '.leave',
    'uses' => 'ChallengeController@leaveChallenge',
]);

Route::put('/cancel/{challenge}', [
    'as'   => '.cancel',
    'uses' => 'ChallengeController@cancelChallenge',
]);

Route::post('/invite-users/{challenge}', [
    'as'   => '.invite-users',
    'uses' => 'ChallengeController@inviteUsers',
]);

Route::put('/accept-reject-invitation/{challenge}/{status}', [
    'as'   => '.accept-reject-invitation',
    'uses' => 'ChallengeController@changeInvitationStatus',
]);

Route::get('/categories', [
    'as'   => '.categories',
    'uses' => 'ChallengeController@getChallengeCategories',
]);

Route::get('/invitation-list', [
    'as'   => '.invitation-list',
    'uses' => 'ChallengeController@getInvitationsList',
]);

Route::post('/create', [
    'as'   => '.create',
    'uses' => 'ChallengeController@store',
]);

Route::post('/update/{challenge}', [
    'as'   => '.update',
    'uses' => 'ChallengeController@update',
]);

Route::get('/explore/{slug?}', [
    'as'   => '.explore',
    'uses' => 'ChallengeController@exploreChallenges',
]);

Route::get('/details/{challenge}', [
    'as'   => '.details',
    'uses' => 'ChallengeController@getDetails',
]);

Route::put('/join/{challenge}', [
    'as'   => '.join',
    'uses' => 'ChallengeController@joinToChallenge',
]);

Route::get('/ongoing-details/{challenge}', [
    'as'   => '.ongoing-details',
    'uses' => 'ChallengeController@ongoingDetails',
]);

Route::get('/leaderboard/{challenge}', [
    'as'   => '.leaderboard',
    'uses' => 'ChallengeController@leaderboard',
]);

Route::get('/history', [
    'as'   => '.history',
    'uses' => 'ChallengeController@history',
]);

Route::get('/info/{challenge}', [
    'as'   => '.info',
    'uses' => 'ChallengeController@getInfo',
]);

Route::get('/challenge-users/{challenge}', [
    'as'   => '.challenge-users',
    'uses' => 'ChallengeController@getChallengeUsers',
]);

Route::get('/leaderboard-my-teams/{challenge}', [
    'as'   => '.leaderboard-my-teams',
    'uses' => 'ChallengeController@myTeamsLeaderboard',
]);

Route::get('/company-teams/{challenge}', [
    'as'   => '.companyTeams',
    'uses' => 'ChallengeController@getCompanyTeams',
]);

Route::get('/team-members/{challenge}', [
    'as'   => '.teamMembers',
    'uses' => 'ChallengeController@getTeamMembers',
]);

Route::get('/winners/{challenge}', [
    'as'   => '.winners',
    'uses' => 'ChallengeController@getWinnersList',
]);
