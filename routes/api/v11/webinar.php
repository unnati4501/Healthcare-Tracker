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
