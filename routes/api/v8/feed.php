<?php
declare(strict_types=1);

Route::get('list/{subcategory?}', [
    'as'   => '.list',
    'uses' => 'FeedController@list',
]);

Route::get('detail/{feed}', [
    'as'   => '.detail',
    'uses' => 'FeedController@detail',
]);

Route::put('like-unlike/{feed}', [
    'as'   => '.like-unlike',
    'uses' => 'FeedController@likeUnlike',
]);

Route::put('save-unsave/{feed}', [
    'as'   => '.save-unsave',
    'uses' => 'FeedController@saveUnsave',
]);

Route::get('saved', [
    'as'   => '.saved',
    'uses' => 'FeedController@saved',
]);
