<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get("/", function () {
    ob_start();
    require(app_path() . "/mThumb/mthumb.php");
    return ob_get_clean();
})->name('thumb-generation');