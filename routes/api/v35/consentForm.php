<?php declare (strict_types = 1);

Route::get('/{wellbeingSpecialist}', [
    'as'   => '.index',
    'uses' => 'ConsentFormController@index',
]);

Route::post('/submit-consent-form', [
    'as'   => '.submit-consent-form',
    'uses' => 'ConsentFormController@submitConsentForm',
]);