<?php declare (strict_types = 1);

Route::get('/personal', [
    'as'   => '.personal',
    'uses' => 'PersonalChallengeController@explorePersonalChallenges',
]);

Route::post('/personal/join/{personalChallenge}', [
    'as'   => '.join',
    'uses' => 'PersonalChallengeController@joinPersonalChallenge',
]);

Route::put('/personal/leave/{personalChallenge}', [
    'as'   => '.leave',
    'uses' => 'PersonalChallengeController@leavePersonalChallenge',
]);

Route::put('/personal/complete/{personalChallengeUser}', [
    'as'   => '.complete',
    'uses' => 'PersonalChallengeController@completePersonalChallenge',
]);

Route::get('/personal/history', [
    'as'   => '.history',
    'uses' => 'PersonalChallengeController@history',
]);

Route::put('/personal/{personalChallenge}/tasks/{personalChallengeUserTask}', [
    'as'   => '.tasks',
    'uses' => 'PersonalChallengeController@completeTasksNew',
]);

Route::get('/personal/{personalChallenge}/{personalChallengeUser?}', [
    'as'   => '.details',
    'uses' => 'PersonalChallengeController@details',
]);
