<?php
declare(strict_types=1);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'PodcastController@saved',
]);

Route::get('podcast-list/{podcastSubCategory}', [
    'as'   => '.podcast-list',
    'uses' => 'PodcastController@podcastList',
]);

Route::put('/favourite-unfavourite-podcast/{podcast}', [
    'as'   => '.favourite-unfavourite-podcast',
    'uses' => 'PodcastController@favouriteUnfavouritePodcast',
]);

Route::put('/like-unlike-podcast/{podcast}', [
    'as'   => '.like-unlike-podcast',
    'uses' => 'PodcastController@likeUnlikePodcast',
]);

Route::put('/save-unsave-podcast/{podcast}', [
    'as'   => '.save-unsave-podcast',
    'uses' => 'PodcastController@saveUnsavePodcast',
]);

Route::post('/save-duration/{podcast}', [
    'as'   => '.save-duration',
    'uses' => 'PodcastController@saveDuration',
]);

Route::put('/mark-as-completed/{podcast}', [
    'as'   => '.mark-as-completed',
    'uses' => 'PodcastController@markAsCompleted',
]);


Route::get('podcast-details/{podcast}', [
    'as'   => '.podcast-details',
    'uses' => 'PodcastController@podcastDetails',
]);

Route::get('recent-podcasts', [
    'as'   => '.recent-podcasts',
    'uses' => 'PodcastController@recentPodcasts',
]);

Route::get('more-like-podcasts/{podcast}', [
    'as'   => '.more-like-podcasts',
    'uses' => 'PodcastController@moreLikePodcasts',
]);
