<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
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
Route::post('/garmin/pingcallback', '\App\Http\Controllers\GarminController@pingCallback');
Route::post('/zendesk/callback', '\App\Http\Controllers\Admin\ZendeskController@webhook');
Route::post('/calendly/callback', '\App\Http\Controllers\Admin\CalendlyController@webhook');
Route::post('/digitalTherapy/callback', '\App\Http\Controllers\Admin\CronofyController@callbackScheduling');
Route::post('/digitalTherapy/rescheduledCallback', '\App\Http\Controllers\Admin\CronofyController@callbackRescheduling');
// Route::get('info', '\App\Http\Controllers\ServerinfoController@info');

// Testing WebSocket
Route::view('websocketview', 'checkingwebsocket');

Route::group(['prefix' => 'internal'], function(){
    Route::get('sockets/serve', function(){
        \Illuminate\Support\Facades\Artisan::call('websockets:serve');
    });
});
Route::get('/', function () {
    return redirect(App::currentLocale().'/login');
});
Route::group([
    'prefix'     => App::currentLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'XssSanitization'],
], function () {

    Route::get('/', function () {
        return redirect('login');
    });

    Auth::routes();

    Route::get('/login/azure', '\App\Http\Middleware\AppAzure@azure');
    Route::get('/login/azurecallback', '\App\Http\Middleware\AppAzure@azurecallback');
    Route::get('/logout/azure', '\App\Http\Middleware\AppAzure@azurelogout');

    Route::get('/login/saml', '\App\Http\Middleware\AppSaml@loginRequest');
    Route::post('/login/samlcallback', '\App\Http\Middleware\AppSaml@loginCallback');
    Route::get('/logout/saml', '\App\Http\Middleware\AppSaml@logoutRequest');
    Route::get('/login/samlappcallback', '\App\Http\Middleware\AppSaml@loginAppCallback')->name('saml.appcallback');

    Route::post('/login/send-otp', [
        'as'   => 'send-otp',
        'uses' => 'Auth\LoginController@sendOtp',
    ]);
    Route::post('/login/verify-otp', [
        'as'   => 'verify-otp',
        'uses' => 'Auth\LoginController@verifyOtp',
    ]);

    // Route::get('/cookie-declaration', function () {
    //     return view('custom.cookie-declaration');
    // });

    Route::post('resetPassword', ['as' => 'resetPassword', 'uses' => 'Auth\ResetPasswordController@resetPassword']);

    Route::get('setnewpassword', ['as' => 'setnewpassword', 'uses' => 'Admin\UserController@getSetNewPassword']);
    Route::post('setnewpassword', ['as' => 'setnewpassword', 'uses' => 'Admin\UserController@postSetNewPassword']);

    Route::get('newpassword', 'UserController@newPassword')->name('newpassword');

    Route::get('responseSurvey/{surveyId}', ['as' => 'responseSurvey', 'uses' => 'Admin\ZcSurveyController@responseSurvey']);
    Route::get('response-survey/get-question/{questionString}/{question}', [
        'as'   => 'getSurveyQuestion',
        'uses' => 'Admin\ZcSurveyController@getSurveyQuestion',
    ]);
    Route::post('responseSurvey/{surveyId}', ['as' => 'submitSurvey', 'uses' => 'Admin\ZcSurveyController@submitSurvey']);
    Route::post('survey-review/{surveyId}', ['as' => 'storeSurveyReview', 'uses' => 'Admin\ZcSurveyController@storeSurveyReview']);

    Route::get('projectSurveyResponse/{surveyId}', ['as' => 'projectSurveyResponse', 'uses' => 'Admin\CSProjectController@projectSurveyResponse']);

    Route::post('submitProjectSurvey/{surveyId}', ['as' => 'submitProjectSurvey', 'uses' => 'Admin\CSProjectController@submitProjectSurvey']);

    Route::get('survey-submited', ['as' => 'survey-submited', 'uses' => 'Admin\CSProjectController@surveySubmited']);

    Route::get('acceptEvent/{bookingEventLogId}', ['as' => 'acceptEvent', 'uses' => 'Admin\MarketplaceController@acceptEvent']);
    Route::get('rejectEvent/{bookingEventLogId}', ['as' => 'rejectEvent', 'uses' => 'Admin\MarketplaceController@rejectEvent']);

    Route::get('verify-deeplink', ['as' => 'verifyDeepLink', 'uses' => '\App\Http\Controllers\ContentDeepLinkController@verifyDeepLink']);
    Route::group([
        'prefix'     => 'old-dashboard',
        'middleware' => 'auth',
    ], function () {
        Route::get('/', 'NewDashboardController@index')->name('old-dashboard');
    });

    Route::group([
        'prefix'     => 'dashboard',
        'middleware' => 'auth',
    ], function () {
        Route::get('/', 'DashboardController@index')->name('dashboard');
        Route::post('/appUsageTab', 'DashboardController@getAppUsageTabData')->name('dashboard.getAppUsageTabData');
        Route::post('/physicalTab', 'DashboardController@getPhysicalTabData')->name('dashboard.getPhysicalTabData');
        Route::post('/psychologicalTab', 'DashboardController@getPsychologicalTabData')->name('dashboard.getPsychologicalTabData');
        Route::post('/auditTab', 'DashboardController@getAuditTabData')->name('dashboard.getAuditTabData');
        Route::get('/question-report/{category}', 'DashboardController@questionReport')->name('dashboard.questionReport');
        Route::post('/question-report', 'DashboardController@getQuestionReportData')->name('dashboard.getQuestionReportData');
        Route::get('/question-report/details/{question}', 'DashboardController@questionReportDetails')->name('dashboard.questionReportDetails');
        Route::post('/question-report/answers/{question}', 'DashboardController@questionAnswers')->name('dashboard.questionAnswers');
        Route::post('/bookingTab', 'DashboardController@getBookingTabData')->name('dashboard.getBookingTabData');
        Route::post('/eapActivityTab', 'DashboardController@getEapActivityTabData')->name('dashboard.getEapActivityTabData');
        Route::post('/digitalTherapyTab', 'DashboardController@getDigitalTherapyTabData')->name('dashboard.getDigitalTherapyTabData');
    });

    Route::group([
        'prefix'     => 'admin',
        'as'         => 'admin',
        'namespace'  => 'Admin',
        'middleware' => ['auth'],
    ], function () {
        require_once(base_path('routes/web/admin.php'));
    });
});