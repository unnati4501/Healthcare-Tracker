<?php
declare (strict_types = 1);

Route::post('/', [
    'as'   => '.contactus',
    'uses' => 'ContactUsController@contactUsSubmit',
]);
