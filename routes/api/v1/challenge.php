<?php
declare (strict_types = 1);

Route::get('/running', [
    'as'   => '.running',
    'uses' => 'ChallengeController@runningChallenges',
]);

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

Route::post('/get-badges', [
    'as'   => '.get-badges',
    'uses' => 'ChallengeController@getBadges',
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

Route::get('/upcoming/{createdBy}', [
    'as'   => '.upcoming',
    'uses' => 'ChallengeController@upcoming',
]);

Route::get('/history', [
    'as'   => '.history',
    'uses' => 'ChallengeController@history',
]);

Route::get('/info/{challenge}', [
    'as'   => '.info',
    'uses' => 'ChallengeController@getInfo',
]);

Route::get('/running-view-all', [
    'as'   => '.running-view-all',
    'uses' => 'ChallengeController@runningViewAll',
]);

Route::get('/challenge-users/{challenge}', [
    'as'   => '.challenge-users',
    'uses' => 'ChallengeController@getChallengeUsers',
]);

Route::get('/winners/{challenge}', [
    'as'   => '.winners',
    'uses' => 'ChallengeController@getWinnersList',
]);

Route::get('/ongoing-details-new/{challenge}', [
    'as'   => '.ongoing-details',
    'uses' => 'ChallengeController@ongoingDetailsNew',
]);
