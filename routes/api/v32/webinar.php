<?php declare (strict_types = 1);

Route::get('/list/{subcategory}', [
    'as'   => '.list',
    'uses' => 'WebinarController@index',
]);

Route::get('detail/{webinar}', [
    'as'   => '.detail',
    'uses' => 'WebinarController@detail',
]);

Route::put('/like-unlike-webinar/{webinar}', [
    'as'   => '.like-unlike-webinar',
    'uses' => 'WebinarController@likeUnlikeWebinar',
]);

Route::put('save-unsave/{webinar}', [
    'as'   => '.save-unsave',
    'uses' => 'WebinarController@saveUnsave',
]);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'WebinarController@saved',
]);

Route::get('recent-webinars', [
    'as'   => '.recent-webinars',
    'uses' => 'WebinarController@recentWebinars',
]);

Route::put('/favourite-unfavourite-webinar/{webinar}', [
    'as'   => '.favourite-unfavourite-webinar',
    'uses' => 'WebinarController@favouriteUnfavouriteWebinar',
]);
