<?php declare (strict_types = 1);

Route::get('/list/{subcategory}', [
    'as'   => '.list',
    'uses' => 'ShortsController@index',
]);

Route::get('detail/{short}', [
    'as'   => '.detail',
    'uses' => 'ShortsController@detail',
]);

Route::put('/like-unlike-short/{short}', [
    'as'   => '.like-unlike-short',
    'uses' => 'ShortsController@likeUnlikeShort',
]);

Route::put('save-unsave/{short}', [
    'as'   => '.save-unsave',
    'uses' => 'ShortsController@saveUnsave',
]);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'ShortsController@saved',
]);

Route::get('recent-shorts', [
    'as'   => '.recent-webinars',
    'uses' => 'ShortsController@recentShorts',
]);

Route::put('/favourite-unfavourite-short/{short}', [
    'as'   => '.favourite-unfavourite-short',
    'uses' => 'ShortsController@favouriteUnfavouriteShort',
]);

Route::put('mark-as-completed/{webinar}', [
    'as'   => '.mark-as-completed',
    'uses' => 'ShortsController@markAsCompleted',
]);
