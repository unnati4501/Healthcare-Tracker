<?php
declare(strict_types=1);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'MeditationController@saved',
]);

Route::get('categories', [
    'as'   => '.categories',
    'uses' => 'MeditationController@getCategories',
]);

Route::get('track-list/{meditationSubCategory}', [
    'as'   => '.track-list',
    'uses' => 'MeditationController@trackList',
]);

Route::get('coach-list', [
    'as'   => '.coach-list',
    'uses' => 'MeditationController@coachList',
]);

Route::put('/favourite-unfavourite-track/{meditation}', [
    'as'   => '.favourite-unfavourite-track',
    'uses' => 'MeditationController@favouriteUnfavouriteTrack',
]);

Route::put('/like-unlike-track/{meditation}', [
    'as'   => '.like-unlike-track',
    'uses' => 'MeditationController@likeUnlikeTrack',
]);

Route::put('/save-unsave-track/{meditation}', [
    'as'   => '.save-unsave-track',
    'uses' => 'MeditationController@saveUnsaveTrack',
]);

Route::post('/save-duration/{meditation}', [
    'as'   => '.save-duration',
    'uses' => 'MeditationController@saveDuration',
]);

Route::put('/mark-as-completed/{meditation}', [
    'as'   => '.mark-as-completed',
    'uses' => 'MeditationController@markAsCompleted',
]);

Route::get('coach-meditations/{user}', [
    'as'   => '.coach-meditations',
    'uses' => 'MeditationController@coachMeditations',
]);

Route::get('meditation-coach-list', [
    'as'   => '.meditation-coach-list',
    'uses' => 'MeditationController@meditationCoachList',
]);

Route::get('track-details/{meditation}', [
    'as'   => '.track-details',
    'uses' => 'MeditationController@trackDetails',
]);

Route::get('recent-meditations', [
    'as'   => '.recent-meditations',
    'uses' => 'MeditationController@recentMeditations',
]);
