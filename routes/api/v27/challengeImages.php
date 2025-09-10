<?php declare (strict_types = 1);

Route::get('/categories', [
    'as'   => '.getCategories',
    'uses' => 'ChallengeImageLibraryController@getCategories',
]);

Route::get('/images/{category}', [
    'as'   => '.getCategoryWiseImages',
    'uses' => 'ChallengeImageLibraryController@getCategoryWiseImages',
]);
