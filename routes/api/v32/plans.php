<?php
declare(strict_types=1);

Route::get('/', [
    'as'   => '.plans',
    'uses' => 'PlanController@plans',
]);

Route::post('/unlock-plan/{plan}', [
    'as'   => '.unlock-plan',
    'uses' => 'PlanController@unlockPlan',
]);

Route::post('/verify-coupon/{plan}', [
    'as'   => '.verify-coupon',
    'uses' => 'PlanController@verifyCoupon',
]);
