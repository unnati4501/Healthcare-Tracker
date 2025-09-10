<?php declare (strict_types = 1);

Route::get('/static-data', [
    'as'   => '.static-data',
    'uses' => 'RecipeController@recipeStaticData',
]);

Route::post('/create', [
    'as'   => '.create',
    'uses' => 'RecipeController@store',
]);

Route::post('/update/{recipe}', [
    'as'   => '.update',
    'uses' => 'RecipeController@update',
]);

Route::get('/list/{subcategory}', [
    'as'   => '.list',
    'uses' => 'RecipeController@index',
]);

Route::put('/like-unlike-recipe/{recipe}', [
    'as'   => '.like-unlike-recipe',
    'uses' => 'RecipeController@likeUnlike',
]);

Route::put('/save-unsave-recipe/{recipe}', [
    'as'   => '.save-unsave-recipe',
    'uses' => 'RecipeController@saveUnsave',
]);

Route::get('/details/{recipe}', [
    'as'   => '.details',
    'uses' => 'RecipeController@details',
]);

Route::get('/saved-list', [
    'as'   => '.saved-list',
    'uses' => 'RecipeController@savedList',
]);

Route::delete('delete/{recipe}', [
    'as'   => '.delete',
    'uses' => 'RecipeController@delete',
]);

Route::put('/favourite-unfavourite-recipe/{recipe}', [
    'as'   => '.favourite-unfavourite-recipe',
    'uses' => 'RecipeController@favouriteUnfavourite',
]);

Route::post('/search', [
    'as'   => '.search',
    'uses' => 'RecipeController@search',
]);
