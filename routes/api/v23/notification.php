<?php
declare(strict_types=1);

Route::get('alert-list', [
    'as'   => '.alert-list',
    'uses' => 'NotificationController@alertList',
]);

Route::put('read-unread/{notification}', [
    'as'   => '.read-unread',
    'uses' => 'NotificationController@readUnread',
]);

Route::delete('/{notification}', [
    'as'   => '.delete',
    'uses' => 'NotificationController@delete',
]);
