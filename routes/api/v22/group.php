<?php
declare(strict_types=1);

Route::get('explore-groups/{subCategory?}', [
    'as'   => '.explore-groups',
    'uses' => 'GroupController@exploreGroups',
]);

Route::get('/details/{group}', [
    'as'   => '.details',
    'uses' => 'GroupController@details',
]);

Route::get('/group-messages/{group}', [
    'as'   => '.group-messages',
    'uses' => 'GroupController@groupMessages',
]);

Route::post('/send-group-messages/{group}', [
    'as'   => '.send-group-messages',
    'uses' => 'GroupController@sendGroupMessages',
]);


Route::post('/forward-group-messages', [
    'as'   => '.forward-group-messages',
    'uses' => 'GroupController@forwardToMultipleGroups',
]);

Route::put('favourite-unfavourite-group-message/{message}', [
    'as'   => '.favourite-unfavourite-group-message',
    'uses' => 'GroupController@favUnfavGroupMessage',
]);

Route::delete('delete-group-message/{message}', [
    'as'   => '.delete-group-message',
    'uses' => 'GroupController@deleteGroupMessage',
]);

Route::get('/group-members/{group}', [
    'as'   => '.group-members',
    'uses' => 'GroupController@groupMembers',
]);

Route::post('/join-group/{group}', [
    'as'   => '.join-group',
    'uses' => 'GroupController@joinGroup',
]);

Route::put('/mute-unmute-notification/{group}', [
    'as'   => '.mute-unmute-notification',
    'uses' => 'GroupController@muteNotification',
]);

Route::post('/report/{group}', [
    'as'   => '.report',
    'uses' => 'GroupController@report',
]);

Route::get('/starred-messages/{group}', [
    'as'   => '.starred-messages',
    'uses' => 'GroupController@starredMessages',
]);

Route::delete('leave/{group}', [
    'as'   => '.leave',
    'uses' => 'GroupController@leave',
]);

Route::get('my-groups-list/{search?}', [
    'as'   => '.my-groups-list',
    'uses' => 'GroupController@myGroupsList',
]);

Route::delete('clear-all-group-message/{group}', [
    'as'   => '.clear-all-group-message',
    'uses' => 'GroupController@clearAllGroupMessage',
]);

Route::get('get-latest-message/{group}/{messageId}', [
    'as'   => '.get-latest-message',
    'uses' => 'GroupController@getLatestMessage',
]);

Route::get('archived-groups', [
    'as'   => '.archived-groups',
    'uses' => 'GroupController@archivedGroups',
]);
