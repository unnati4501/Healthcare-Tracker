<?php
declare(strict_types=1);

Route::get('list/{type}/{subcategory?}', [
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

Route::get('sub-categories/{type}', [
    'as'   => '.sub-categories',
    'uses' => 'FeedController@subCategories',
]);

Route::get('recent-stories', [
    'as'   => '.recent-stories',
    'uses' => 'FeedController@recentStories',
]);

Route::get('card-listing/{type}', [
    'as'   => '.card-listing',
    'uses' => 'FeedController@cardListingStories',
]);
