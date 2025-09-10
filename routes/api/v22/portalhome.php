<?php
declare (strict_types = 1);

Route::get('wellbeing-score', [
    'as'   => '.wellbeing-score',
    'uses' => 'PortalHomeController@wellbeingScore',
]);

Route::get('home', [
    'as'   => '.home',
    'uses' => 'PortalHomeController@portalHome',
]);

Route::get('wellbeing-recommendation', [
    'as'   => '.wellbeing-recommendation',
    'uses' => 'PortalHomeController@wellbeingRecommendation',
]);
