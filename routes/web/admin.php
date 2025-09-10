<?php
declare (strict_types = 1);

use Illuminate\Support\Facades\Route;

// categories module
Route::group([
    'as'     => '.categories',
    'prefix' => 'categories',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'CategoriesController@index',
    ]);
    Route::get('/getCategories', [
        'as'   => '.getCategories',
        'uses' => 'CategoriesController@getCategories',
    ]);
});

Route::group([
    'as'     => '.subcategories',
    'prefix' => 'subcategories',
], function () {
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'CategoriesController@createSub',
    ]);
    Route::post('/', [
        'as'   => '.store',
        'uses' => 'CategoriesController@storeSub',
    ]);
    Route::get('/{category}/edit', [
        'as'   => '.edit',
        'uses' => 'CategoriesController@editSub',
    ]);
    Route::patch('/{category}', [
        'as'   => '.update',
        'uses' => 'CategoriesController@updateSub',
    ]);
    Route::get('/getSubCategories', [
        'as'   => '.getSubCategories',
        'uses' => 'CategoriesController@getSubCategories',
    ]);
    Route::delete('/{category}', [
        'as'   => '.delete',
        'uses' => 'CategoriesController@deleteSub',
    ]);
    Route::get('/{category}', [
        'as'   => '.index',
        'uses' => 'CategoriesController@indexSub',
    ]);
});

// Role Module
Route::group([
    'as'     => '.roles',
    'prefix' => 'roles',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'RolesController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'RolesController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'RolesController@store',
    ]);
    Route::get('/{role}/edit', [
        'as'   => '.edit',
        'uses' => 'RolesController@edit',
    ]);
    Route::patch('/{role}/update', [
        'as'   => '.update',
        'uses' => 'RolesController@update',
    ]);
    Route::get('/getRoles', [
        'as'   => '.getRoles',
        'uses' => 'RolesController@getRoles',
    ]);
    Route::delete('/delete/{role}', [
        'as'   => '.delete',
        'uses' => 'RolesController@delete',
    ]);
});

// AppSettings Module
Route::group([
    'as'     => '.appsettings',
    'prefix' => 'appsettings',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'AppsettingsController@index',
    ]);
    Route::get('/changeSettings', [
        'as'   => '.changeSettings',
        'uses' => 'AppsettingsController@changeSettings',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'AppsettingsController@store',
    ]);

    Route::get('/getAppSettings', [
        'as'   => '.getAppSettings',
        'uses' => 'AppsettingsController@getAppSettings',
    ]);
});

// Onboard Slides management

Route::group([
    'as'     => '.appslides',
    'prefix' => 'appslides',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'AppslidesController@index',
    ]);
    Route::get('/create/{type}', [
        'as'   => '.create',
        'uses' => 'AppslidesController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'AppslidesController@store',
    ]);
    Route::get('/{slide}/edit', [
        'as'   => '.edit',
        'uses' => 'AppslidesController@edit',
    ]);
    Route::patch('/{slide}/update', [
        'as'   => '.update',
        'uses' => 'AppslidesController@update',
    ]);
    Route::get('/getSlides', [
        'as'   => '.getSlides',
        'uses' => 'AppslidesController@getSlides',
    ]);
    Route::delete('/delete/{slide}', [
        'as'   => '.delete',
        'uses' => 'AppslidesController@delete',
    ]);
    Route::post('/reorderingScreen/', [
        'as'   => '.reorderingScreen',
        'uses' => 'AppslidesController@reorderingScreen',
    ]);
});

Route::group([
    'as'     => '.companies',
    'prefix' => 'companies',
], function () {
    Route::delete('/delete/{company}', [
        'as'   => '.delete',
        'uses' => 'CompaniesController@delete',
    ]);
    Route::get('/getCompanies', [
        'as'   => '.getCompanies',
        'uses' => 'CompaniesController@getCompanies',
    ]);
    Route::get('/{company}/getCompanyTeams', [
        'as'   => '.getCompanyTeams',
        'uses' => 'CompaniesController@getCompanyTeams',
    ]);
    Route::get('/{company}/getCompanyModerators', [
        'as'   => '.getCompanyModerators',
        'uses' => 'CompaniesController@getCompanyModerators',
    ]);
    Route::get('/{company}/getLimitsList', [
        'as'   => '.getLimitsList',
        'uses' => 'CompaniesController@getLimitsList',
    ]);
    Route::post('/{company}/set-default-limits', [
        'as'   => '.setDefaultLimits',
        'uses' => 'CompaniesController@setDefaultLimits',
    ]);
    Route::post('/changeAppSettingStoreUpdate', [
        'as'   => '.changeAppSettingStoreUpdate',
        'uses' => 'CompaniesController@changeAppSettingStoreUpdate',
    ]);
    Route::get('/{company}/changeToDefaultSettings', [
        'as'   => '.changeToDefaultSettings',
        'uses' => 'CompaniesController@changeToDefaultSettings',
    ]);
    Route::get('/get-survey-details/{company}/{type}', [
        'as'   => '.get-survey-details',
        'uses' => 'CompaniesController@getSurveyDetails',
    ]);
    Route::post('/export-survey-report/{company}/{type}', [
        'as'   => '.export-survey-report',
        'uses' => 'CompaniesController@exportSurveyReport',
    ]);
    Route::post('/survey-configuration/{company}', [
        'as'   => '.set-survey-configuration',
        'uses' => 'CompaniesController@setSurveyConfiguration',
    ]);
    Route::get('/reseller-details', [
        'as'   => '.resellerDetails',
        'uses' => 'CompaniesController@resellerDetails',
    ]);
    Route::post('/getUpcomingSurveyDetails', [
        'as'   => '.getUpcomingSurveyDetails',
        'uses' => 'CompaniesController@getUpcomingSurveyDetails',
    ]);
    Route::post('/getStaffServices', [
        'as'   => '.getStaffServices',
        'uses' => 'CompaniesController@getStaffServices',
    ]);
    
    Route::post('/save-locationwise-slots-temp', [
        'as'   => '.save-locationwise-slots-temp',
        'uses' => 'CompaniesController@saveLocationWiseSlotsTemp',
    ]);

    Route::delete('/delete-temp-slots/{tempId}', [
        'as'   => '.delete-temp-slots',
        'uses' => 'CompaniesController@deletetempSlots',
    ]);
    Route::post('/get-specific-slot', [
        'as'   => '.get-specific-slot',
        'uses' => 'CompaniesController@getSpecificSlots',
    ]);
    Route::delete('/deleteBanner/{companyDigitalTherapyBanner}', [
        'as'   => '.deleteBanner',
        'uses' => 'CompaniesController@deleteBanner',
    ]);
    Route::post('{company}/reorderingScreen', [
        'as'   => '.reorderingScreen',
        'uses' => 'CompaniesController@reorderingScreen',
    ]);

    Route::group([
        'prefix' => '{companyType}',
    ], function () {
        Route::get('/', [
            'as'   => '.index',
            'uses' => 'CompaniesController@index',
        ]);
        Route::get('/create', [
            'as'   => '.create',
            'uses' => 'CompaniesController@create',
        ]);
        Route::post('/store', [
            'as'   => '.store',
            'uses' => 'CompaniesController@store',
        ]);
        Route::get('/{company}/edit', [
            'as'   => '.edit',
            'uses' => 'CompaniesController@edit',
        ]);
        Route::patch('/{company}/update', [
            'as'   => '.update',
            'uses' => 'CompaniesController@update',
        ]);
        Route::get('/{company}/createModerator', [
            'as'   => '.createModerator',
            'uses' => 'CompaniesController@createModerator',
        ]);
        Route::patch('/{company}/storeModerator', [
            'as'   => '.storeModerator',
            'uses' => 'CompaniesController@storeModerator',
        ]);
        Route::get('/{company}/moderators', [
            'as'   => '.moderators',
            'uses' => 'CompaniesController@moderators',
        ]);
        Route::get('/{company}/teams', [
            'as'   => '.teams',
            'uses' => 'CompaniesController@teams',
        ]);
        Route::get('/{company}/getLimits', [
            'as'   => '.getLimits',
            'uses' => 'CompaniesController@getLimits',
        ]);
        Route::get('/{company}/editLimits', [
            'as'   => '.editLimits',
            'uses' => 'CompaniesController@editLimits',
        ]);
        Route::patch('/{company}/updateLimits', [
            'as'   => '.updateLimits',
            'uses' => 'CompaniesController@updateLimits',
        ]);
        Route::get('/{company}/changeAppSettingIndex', [
            'as'   => '.changeAppSettingIndex',
            'uses' => 'CompaniesController@changeAppSettingIndex',
        ]);
        Route::get('/{company}/changeToDefaultSettings', [
            'as'   => '.changeToDefaultSettings',
            'uses' => 'CompaniesController@changeToDefaultSettings',
        ]);
        Route::get('/getCompanyAppSettings', [
            'as'   => '.getCompanyAppSettings',
            'uses' => 'CompaniesController@getCompanyAppSettings',
        ]);
        Route::get('/{company}/changeAppSettingCreateEdit', [
            'as'   => '.changeAppSettingCreateEdit',
            'uses' => 'CompaniesController@changeAppSettingCreateEdit',
        ]);
        Route::get('/survey-configuration/{company}', [
            'as'   => '.survey-configuration',
            'uses' => 'CompaniesController@surveyConfiguration',
        ]);
        Route::get('/{company}/portalFooter', [
            'as'   => '.portalFooter',
            'uses' => 'CompaniesController@portalFooter',
        ]);
        Route::patch('/{company}/storePortalFooterDetails', [
            'as'   => '.storePortalFooterDetails',
            'uses' => 'CompaniesController@storePortalFooterDetails',
        ]);
        Route::get('/{company}/manageCredits', [
            'as'   => '.manageCredits',
            'uses' => 'CompaniesController@manageCredits',
        ]);
        Route::patch('/{company}/storeCredits', [
            'as'   => '.storeCredits',
            'uses' => 'CompaniesController@storeCredits',
        ]);
        Route::get('/{company}/digitalTherapyBanners', [
            'as'   => '.digitalTherapyBanners',
            'uses' => 'CompaniesController@digitalTherapyBanners',
        ]);
        Route::get('/getDigitalTherapyBanners', [
            'as'   => '.getDigitalTherapyBanners',
            'uses' => 'CompaniesController@getDigitalTherapyBanners',
        ]);
        Route::get('{company}/editBanner/{companyDigitalTherapyBanner}', [
            'as'   => '.editBanner',
            'uses' => 'CompaniesController@editBanner',
        ]);
        Route::patch('/{companyDigitalTherapyBanner}/updateBanner', [
            'as'   => '.updateBanner',
            'uses' => 'CompaniesController@updateBanner',
        ]);
        Route::get('{company}/createBanner', [
            'as'   => '.createBanner',
            'uses' => 'CompaniesController@createBanner',
        ]);
        Route::post('/{company}/storeBanner', [
            'as'   => '.storeBanner',
            'uses' => 'CompaniesController@storeBanner',
        ]);
        Route::post('credit-history/{company}', [
            'as'   => '.credit-history',
            'uses' => 'CompaniesController@getCreditHistory',
        ]);
        Route::post('/export-credit-history', [
            'as'   => '.export-credit-history',
            'uses' => 'CompaniesController@exportCreditHistory',
        ]);
    });
});

// Ajax calls
Route::group([
    'as'     => '.ajax',
    'prefix' => 'ajax',
], function () {
    Route::get('/states/{country}', [
        'as'      => '.states',
        'uses'    => 'AjaxController@getStates',
        'laroute' => \true,
    ]);
    Route::get('/timezones/{country}', [
        'as'      => '.timezones',
        'uses'    => 'AjaxController@getTimezones',
        'laroute' => \true,
    ]);
    Route::get('/companyDepartment/{company}', [
        'as'      => '.companyDepartment',
        'uses'    => 'AjaxController@getDepartments',
        'laroute' => \true,
    ]);
    Route::get('/teams/{department}/{team?}', [
        'as'      => '.get-teams',
        'uses'    => 'AjaxController@getLimitWiseTeams',
        'laroute' => \true,
    ]);
    Route::get('/departmentTeams/{department}', [
        'as'      => '.departmentTeams',
        'uses'    => 'AjaxController@getTeams',
        'laroute' => \true,
    ]);
    Route::get('/departmentLocation/{department}', [
        'as'      => '.departmentLocation',
        'uses'    => 'AjaxController@getDepartmentLocations',
        'laroute' => \true,
    ]);
    Route::get('/companyLocation/{company}', [
        'as'      => '.companyLocation',
        'uses'    => 'AjaxController@getCompanyLocations',
        'laroute' => \true,
    ]);
    Route::get('/locationFromDepartments/{location}', [
        'as'      => '.locationFromDepartments',
        'uses'    => 'AjaxController@getLocationFromDepartments',
        'laroute' => \true,
    ]);
    Route::get('/roles/{group}', [
        'as'      => '.roles',
        'uses'    => 'AjaxController@getRoles',
        'laroute' => \true,
    ]);
    Route::get('/getBadges', [
        'as'      => '.getBadges',
        'uses'    => 'AjaxController@getBadges',
        'laroute' => \true,
    ]);
    Route::get('/permissions/{group}', [
        'as'      => '.permissions',
        'uses'    => 'AjaxController@getPermissions',
        'laroute' => \true,
    ]);
    Route::get('/hsSubCategories/{hsCategories}', [
        'as'      => '.hsSubCategories',
        'uses'    => 'AjaxController@getHsSubCategories',
        'laroute' => \true,
    ]);
    Route::get('/companyTeams/{company}', [
        'as'      => '.companyTeams',
        'uses'    => 'AjaxController@getCompanyTeams',
        'laroute' => \true,
    ]);
    Route::get('/teamMembers/{team}', [
        'as'      => '.teamMembers',
        'uses'    => 'AjaxController@getTeamMembers',
        'laroute' => \true,
    ]);
    Route::get('/zcSubCategories/{surveyCategory}', [
        'as'      => '.zcSubCategories',
        'uses'    => 'AjaxController@getZcSubCategories',
        'laroute' => \true,
    ]);
    Route::get('/surveys', [
        'as'      => '.getSurveys',
        'uses'    => 'AjaxController@getSurveys',
        'laroute' => \true,
    ]);
    Route::get('/industryCompany/{industry?}', [
        'as'      => '.industryCompany',
        'uses'    => 'AjaxController@getCompany',
        'laroute' => \true,
    ]);
    Route::get('/showmeditationhours/{company?}', [
        'as'      => '.showmeditationhours',
        'uses'    => 'AjaxController@showMeditationHours',
        'laroute' => \true,
    ]);
    Route::post('/check-companies-content-validate', [
        'as'   => '.checkcompaniescontentvalidate',
        'uses' => 'AjaxController@checkContentValidation',
    ]);
    Route::post('/categorywise-masterclasses/{subcategory?}/{course?}', [
        'as'   => '.categorywise-masterclasses',
        'uses' => 'AjaxController@getCategorywiseMasterclasses',
    ]);
    Route::post('/check-email-exists', [
        'as'   => '.checkEmailExists',
        'uses' => 'AjaxController@checkEmailExists',
    ]);
    Route::post('/portal-domain-exists', [
        'as'   => '.portalDomainExists',
        'uses' => 'AjaxController@portalDomainExists',
    ]);
    Route::get('/cp-features/{group}', [
        'as'   => '.cp-features',
        'uses' => 'AjaxController@getCompanyPlanFeatures',
    ]);
    Route::get('/check-dt-exists', [
        'as'   => '.checkdtexists',
        'uses' => 'AjaxController@checkDTIncluded',
    ]);
    Route::post('/dt-location-slots', [
        'as'   => '.dt-location-slots',
        'uses' => 'AjaxController@dtLocationSlots',
    ]);

    Route::post('/get-wbs-list', [
        'as'   => '.get-wbs-list',
        'uses' => 'AjaxController@dtWbsList',
    ]);
    Route::post('/get-location-general-avabilities', [
        'as'   => '.get-location-general-avabilities',
        'uses' => 'AjaxController@getLocationGeneralAvabilities',
    ]);
});

// Company Module
Route::group([
    'as'     => '.users',
    'prefix' => 'users',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'UserController@index',
    ]);
    Route::get('/roles-wise-data', [
        'as'   => '.getRoleWiseCompanies',
        'uses' => 'UserController@getRoleWiseCompanies',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'UserController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'UserController@store',
    ]);
    Route::get('/{user}/edit', [
        'as'   => '.edit',
        'uses' => 'UserController@edit',
    ]);
    Route::patch('/{user}/update', [
        'as'   => '.update',
        'uses' => 'UserController@update',
    ]);
    Route::get('/getUsers', [
        'as'   => '.getUsers',
        'uses' => 'UserController@getUsers',
    ]);
    Route::delete('/delete/{user}', [
        'as'   => '.delete',
        'uses' => 'UserController@delete',
    ]);
    Route::get('/resetpassword/{user}', [
        'as'   => '.resetpassword',
        'uses' => 'UserController@resetPassword',
    ]);
    Route::get('/changepassword', [
        'as'   => '.changepassword',
        'uses' => 'UserController@changePasswordForm',
    ]);
    Route::post('/changepasswordprocess', [
        'as'   => '.changepasswordprocess',
        'uses' => 'UserController@changePassword',

    ]);
    Route::get('/disconnect/{user}', [
        'as'   => '.disconnect',
        'uses' => 'UserController@disconnectUser',
    ]);

    Route::get('/editProfile', [
        'as'   => '.editProfile',
        'uses' => 'UserController@editProfile',
    ]);

    Route::patch('/updateProfile', [
        'as'   => '.updateProfile',
        'uses' => 'UserController@updateProfile',
    ]);

    Route::get('/{user}', [
        'as'   => '.status',
        'uses' => 'UserController@markStatus',
    ]);
    Route::get('/changeuserpassword/{user}', [
        'as'   => '.changeuserpassword',
        'uses' => 'UserController@changeUserPasswordForm',
    ]);
    Route::post('/changeuserpasswordprocess/{user}', [
        'as'   => '.changeuserpasswordprocess',
        'uses' => 'UserController@changeUserPassword',

    ]);

    Route::get('/getUserCourseData/{user}', [
        'as'   => '.getUserCourseData',
        'uses' => 'UserController@getUserCourseData',
    ]);

    Route::get('/getUserChallangeData/{user}', [
        'as'   => '.getUserChallangeData',
        'uses' => 'UserController@getUserChallangeData',
    ]);

    Route::get('/trackerhistory/{user}', [
        'as'   => '.tracker-history',
        'uses' => 'UserController@trackerhistory',
    ]);

    Route::post('/gettrackerhistory/{user}', [
        'as'   => '.gettrackerhistory',
        'uses' => 'UserController@gettrackerhistory',
    ]);
    Route::post('/exportTrackerHistoryReport/{user}', [
        'as'   => '.exportTrackerHistoryReport',
        'uses' => 'UserController@exportTrackerHistoryReport',
    ]);

    Route::delete('/delete-custom-leave/{id}', [
        'as'   => '.delete-custom-leave',
        'uses' => 'UserController@deleteCustomLeave',
    ]);
    Route::delete('/archive/{user}', [
        'as'   => '.archive',
        'uses' => 'UserController@archive',
    ]);
    Route::get('/find-session/{user}', [
        'as'   => '.find-session',
        'uses' => 'UserController@findSession',
    ]);
});

// Team Module
Route::group([
    'as'     => '.teams',
    'prefix' => 'teams',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'TeamController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'TeamController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'TeamController@store',
    ]);
    Route::get('/{team}/edit', [
        'as'   => '.edit',
        'uses' => 'TeamController@edit',
    ]);
    Route::patch('/{team}/update', [
        'as'   => '.update',
        'uses' => 'TeamController@update',
    ]);
    Route::get('/getTeams', [
        'as'   => '.getTeams',
        'uses' => 'TeamController@getTeams',
    ]);
    Route::delete('/delete/{team}', [
        'as'   => '.delete',
        'uses' => 'TeamController@delete',
    ]);
    Route::get('/set-limit', [
        'as'   => '.setTeamLimit',
        'uses' => 'TeamController@setTeamLimit',
    ]);
    Route::post('/set-limit', [
        'as'   => '.updateTeamLimit',
        'uses' => 'TeamController@updateTeamLimit',
    ]);
    Route::post('exportTeams', [
        'as'   => '.exportTeams',
        'uses' => 'TeamController@exportTeams',
    ]);
});

// Team Assignment
// old module
Route::group([
    'as'     => '.old-team-assignment',
    'prefix' => 'old-team-assignment',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'TeamController@oldteamAssignmentIndex',
    ]);
    Route::get('/get-team-members/{teams}', [
        'as'   => '.getAssignmentTeamMembers',
        'uses' => 'TeamController@oldgetAssignmentTeamMembers',
    ]);
    Route::post('/update', [
        'as'   => '.update',
        'uses' => 'TeamController@oldupdateTeamAssignment',
    ]);
});
// current module
Route::group([
    'as'     => '.team-assignment',
    'prefix' => 'team-assignment',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'TeamController@teamAssignmentIndex',
    ]);
    Route::get('/get-team-members/{teams}', [
        'as'   => '.getAssignmentTeamMembers',
        'uses' => 'TeamController@getAssignmentTeamMembers',
    ]);
    Route::post('/update', [
        'as'   => '.update',
        'uses' => 'TeamController@updateTeamAssignment',
    ]);
});

// Department Module
Route::group([
    'as'     => '.departments',
    'prefix' => 'departments',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'DepartmentController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'DepartmentController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'DepartmentController@store',
    ]);
    Route::get('/{department}/edit', [
        'as'   => '.edit',
        'uses' => 'DepartmentController@edit',
    ]);
    Route::patch('/{department}/update', [
        'as'   => '.update',
        'uses' => 'DepartmentController@update',
    ]);
    Route::get('/getDepartments', [
        'as'   => '.getDepartments',
        'uses' => 'DepartmentController@getDepartments',
    ]);
    Route::delete('/delete/{department}', [
        'as'   => '.delete',
        'uses' => 'DepartmentController@delete',
    ]);
    Route::get('/{department}/locationList', [
        'as'   => '.locationList',
        'uses' => 'DepartmentController@locationList',
    ]);
    Route::get('/{department}/getLocationList', [
        'as'   => '.getLocationList',
        'uses' => 'DepartmentController@getLocationList',
    ]);
    Route::post('exportDepartments', [
        'as'   => '.exportDepartments',
        'uses' => 'DepartmentController@exportDepartments',
    ]);
});

// Location Module
Route::group([
    'as'     => '.locations',
    'prefix' => 'locations',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'LocationController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'LocationController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'LocationController@store',
    ]);
    Route::get('/{location}/edit', [
        'as'   => '.edit',
        'uses' => 'LocationController@edit',
    ]);
    Route::patch('/{location}/update', [
        'as'   => '.update',
        'uses' => 'LocationController@update',
    ]);
    Route::get('/getLocations', [
        'as'   => '.getLocations',
        'uses' => 'LocationController@getLocations',
    ]);
    Route::delete('/delete/{location}', [
        'as'   => '.delete',
        'uses' => 'LocationController@delete',
    ]);
    Route::post('exportLocations', [
        'as'   => '.exportLocations',
        'uses' => 'LocationController@exportLocations',
    ]);
});

// Excercise Module
Route::group([
    'as'     => '.exercises',
    'prefix' => 'exercises',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ExerciseController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ExerciseController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ExerciseController@store',
    ]);
    Route::get('/{excercise}/edit', [
        'as'   => '.edit',
        'uses' => 'ExerciseController@edit',
    ]);
    Route::patch('/{excercise}/update', [
        'as'   => '.update',
        'uses' => 'ExerciseController@update',
    ]);
    Route::get('/getExercises', [
        'as'   => '.getExercises',
        'uses' => 'ExerciseController@getExercises',
    ]);
    Route::delete('/delete/{excercise}', [
        'as'   => '.delete',
        'uses' => 'ExerciseController@delete',
    ]);
});

// Feed Module
Route::group([
    'as'     => '.feeds',
    'prefix' => 'stories',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'FeedController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'FeedController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'FeedController@store',
    ]);
    Route::get('/{feed}/edit', [
        'as'   => '.edit',
        'uses' => 'FeedController@edit',
    ]);
    Route::patch('/{feed}/update', [
        'as'   => '.update',
        'uses' => 'FeedController@update',
    ]);
    Route::post('/getFeeds', [
        'as'   => '.getFeeds',
        'uses' => 'FeedController@getFeeds',
    ]);
    Route::get('/{feed}/details', [
        'as'   => '.details',
        'uses' => 'FeedController@getDetails',
    ]);
    Route::delete('/delete/{feed}', [
        'as'   => '.delete',
        'uses' => 'FeedController@delete',
    ]);
    Route::delete('/deleteFeedMedia/{feed}/{type?}', [
        'as'   => '.deleteFeedMedia',
        'uses' => 'FeedController@deleteFeedMedia',
    ]);
    Route::post('/stickUnstick/{feed}', [
        'as'   => '.stickUnstick',
        'uses' => 'FeedController@stickUnstick',
    ]);
    Route::get('/{feed}/clone', [
        'as'   => '.clone',
        'uses' => 'FeedController@clone',
    ]);
    Route::patch('/{feed}/storeClone', [
        'as'   => '.storeClone',
        'uses' => 'FeedController@storeClone',
    ]);
});

// Date Import Module
Route::group([
    'as'     => '.imports',
    'prefix' => 'imports',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'FileImportsController@index',
    ]);
    Route::get('/getImports', [
        'as'   => '.getImports',
        'uses' => 'FileImportsController@getImports',
    ]);
    Route::post('/', [
        'as'   => '.store',
        'uses' => 'FileImportsController@store',
    ]);
    Route::delete('/{fileImport}', [
        'as'   => '.delete',
        'uses' => 'FileImportsController@delete',
    ]);
});

// Groups Module
Route::group([
    'as'     => '.groups',
    'prefix' => 'groups',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'GroupController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'GroupController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'GroupController@store',
    ]);
    Route::get('/{group}/edit', [
        'as'   => '.edit',
        'uses' => 'GroupController@edit',
    ]);
    Route::patch('/{group}/update', [
        'as'   => '.update',
        'uses' => 'GroupController@update',
    ]);
    Route::get('/getGroups', [
        'as'   => '.getGroups',
        'uses' => 'GroupController@getGroups',
    ]);
    Route::get('/{group}/details', [
        'as'   => '.details',
        'uses' => 'GroupController@getDetails',
    ]);
    Route::delete('/delete/{group}', [
        'as'   => '.delete',
        'uses' => 'GroupController@delete',
    ]);
    Route::get('/{group}/getMembersList', [
        'as'   => '.getMembersList',
        'uses' => 'GroupController@getMembersList',
    ]);
    Route::get('/{group}/reportAbuse', [
        'as'   => '.reportAbuse',
        'uses' => 'GroupController@reportAbuse',
    ]);
    Route::get('/{group}/getReportAbuseList', [
        'as'   => '.getReportAbuseList',
        'uses' => 'GroupController@getReportAbuseList',
    ]);
});

// Courses Module
Route::group([
    'as'     => '.masterclass',
    'prefix' => 'masterclass',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'CourseController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'CourseController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'CourseController@store',
    ]);
    Route::get('/{course}/view', [
        'as'   => '.view',
        'uses' => 'CourseController@view',
    ]);
    Route::get('/{course}/edit', [
        'as'   => '.edit',
        'uses' => 'CourseController@edit',
    ]);
    Route::patch('/{course}/update', [
        'as'   => '.update',
        'uses' => 'CourseController@update',
    ]);
    Route::post('/getCourses', [
        'as'   => '.getCourses',
        'uses' => 'CourseController@getCourses',
    ]);
    Route::get('/{course}/details', [
        'as'   => '.details',
        'uses' => 'CourseController@getDetails',
    ]);
    Route::delete('/delete/{course}', [
        'as'   => '.delete',
        'uses' => 'CourseController@delete',
    ]);
    Route::post('/publish/{course}', [
        'as'   => '.publish',
        'uses' => 'CourseController@publishCourse',
    ]);

    // course lessions
    Route::get('/{course}/manageLessions', [
        'as'   => '.manageLessions',
        'uses' => 'CourseController@manageLessions',
    ]);
    Route::get('/{course}/getLessions', [
        'as'   => '.getLessions',
        'uses' => 'CourseController@getLessions',
    ]);
    Route::get('/{course}/createLession', [
        'as'   => '.createLession',
        'uses' => 'CourseController@createLession',
    ]);
    Route::post('/{course}/storeLession', [
        'as'   => '.storeLession',
        'uses' => 'CourseController@storeLession',
    ]);
    Route::get('/{courseLession}/editLession', [
        'as'   => '.editLession',
        'uses' => 'CourseController@editLession',
    ]);
    Route::post('/{courseLession}/updateLession', [
        'as'   => '.updateLession',
        'uses' => 'CourseController@updateLession',
    ]);
    Route::delete('/deleteLession/{courseLession}', [
        'as'   => '.deleteLession',
        'uses' => 'CourseController@deleteLession',
    ]);
    Route::delete('/deleteCourseLessionMedia/{courseLession}/{type?}', [
        'as'   => '.deleteCourseLessionMedia',
        'uses' => 'CourseController@deleteCourseLessionMedia',
    ]);
    Route::post('/publishLesson/{courseLession}/', [
        'as'   => '.publishLesson',
        'uses' => 'CourseController@publishLesson',
    ]);
    Route::post('/reorderingLesson/{course}/', [
        'as'   => '.reorderingLesson',
        'uses' => 'CourseController@reorderingLesson',
    ]);

    // course surveys
    Route::get('/{course}/getServeys', [
        'as'   => '.getServeys',
        'uses' => 'CourseController@getServeys',
    ]);
    Route::get('/{course}/addSurveys', [
        'as'   => '.addSurveys',
        'uses' => 'CourseController@addSurveys',
    ]);
    Route::get('/{coursesurvey}/editSurvey', [
        'as'   => '.editSurvey',
        'uses' => 'CourseController@editSurvey',
    ]);
    Route::patch('/{coursesurvey}/updateSurvey', [
        'as'   => '.updateSurvey',
        'uses' => 'CourseController@updateSurvey',
    ]);
    Route::get('/{coursesurvey}/surveydetails', [
        'as'   => '.viewSurvey',
        'uses' => 'CourseController@viewSurvey',
    ]);
    Route::post('{coursesurvey}/publish/', [
        'as'   => '.publishSurvey',
        'uses' => 'CourseController@publishSurvey',
    ]);
    Route::delete('/{course}/deleteSurveys', [
        'as'   => '.deleteSurveys',
        'uses' => 'CourseController@deleteSurveys',
    ]);
});

// Badge Module
Route::group([
    'as'     => '.badges',
    'prefix' => 'badges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'BadgeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'BadgeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'BadgeController@store',
    ]);
    Route::get('/{badge}/edit', [
        'as'   => '.edit',
        'uses' => 'BadgeController@edit',
    ]);
    Route::patch('/{badge}/update', [
        'as'   => '.update',
        'uses' => 'BadgeController@update',
    ]);
    Route::get('/getBadges', [
        'as'   => '.getBadges',
        'uses' => 'BadgeController@getBadges',
    ]);
    Route::get('/{badge}/details', [
        'as'   => '.details',
        'uses' => 'BadgeController@getDetails',
    ]);
    Route::delete('/delete/{badge}', [
        'as'   => '.delete',
        'uses' => 'BadgeController@delete',
    ]);
    Route::get('/{badge}/getMembersList', [
        'as'   => '.getMembersList',
        'uses' => 'BadgeController@getMembersList',
    ]);
    Route::get('/masterclassbadgelist', [
        'as'   => '.masterclassbadgelist',
        'uses' => 'BadgeController@masterclassbadgeList',
    ]);
    Route::get('/getmasterclasslist', [
        'as'   => '.getmasterclasslist',
        'uses' => 'BadgeController@getmasterclasslist',
    ]);
});

// Meditation Tracks Module
Route::group([
    'as'     => '.meditationtracks',
    'prefix' => 'meditationtracks',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'MeditationtrackController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'MeditationtrackController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'MeditationtrackController@store',
    ]);
    Route::get('/{track}/edit', [
        'as'   => '.edit',
        'uses' => 'MeditationtrackController@edit',
    ]);
    Route::patch('/{track}/update', [
        'as'   => '.update',
        'uses' => 'MeditationtrackController@update',
    ]);
    Route::post('/getMeditationTrack', [
        'as'   => '.getMeditationTrack',
        'uses' => 'MeditationtrackController@getMeditationTrack',
    ]);
    Route::get('/{track}/details', [
        'as'   => '.details',
        'uses' => 'MeditationtrackController@getDetails',
    ]);
    Route::delete('/delete/{track}', [
        'as'   => '.delete',
        'uses' => 'MeditationtrackController@delete',
    ]);
    Route::get('/{track}/getMembersList', [
        'as'   => '.getMembersList',
        'uses' => 'MeditationtrackController@getMembersList',
    ]);
});

// Notifications Module
Route::group([
    'as'     => '.notifications',
    'prefix' => 'notifications',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'NotificationsController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'NotificationsController@create',
    ]);
    Route::post('/', [
        'as'   => '.store',
        'uses' => 'NotificationsController@store',
    ]);
    Route::get('/getNotifications', [
        'as'   => '.getNotifications',
        'uses' => 'NotificationsController@getNotifications',
    ]);
    Route::get('/{notification}', [
        'as'   => '.show',
        'uses' => 'NotificationsController@show',
    ]);
    Route::delete('/{notification}', [
        'as'   => '.delete',
        'uses' => 'NotificationsController@delete',
    ]);
    Route::get('/{notification}/getRecipientsList', [
        'as'   => '.getRecipientsList',
        'uses' => 'NotificationsController@getRecipientsList',
    ]);
});

// Challenges Module
Route::group([
    'as'     => '.challenges',
    'prefix' => 'challenges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeController@store',
    ]);
    Route::get('/{challenge}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeController@edit',
    ]);
    Route::patch('/{challenge}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeController@update',
    ]);
    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'ChallengeController@getChallenges',
    ]);
    Route::get('/{challenge}/details', [
        'as'   => '.details',
        'uses' => 'ChallengeController@getDetails',
    ]);
    Route::delete('/delete/{challenge}', [
        'as'   => '.delete',
        'uses' => 'ChallengeController@delete',
    ]);
    Route::get('/{challenge}/addPoints', [
        'as'   => '.addPoints',
        'uses' => 'ChallengeController@addPoints',
    ]);
    Route::patch('/{challenge}/managePoints', [
        'as'   => '.managePoints',
        'uses' => 'ChallengeController@managePoints',
    ]);

    Route::get('/{challenge}/getMembersList', [
        'as'   => '.getMembersList',
        'uses' => 'ChallengeController@getMembersList',
    ]);
    Route::get('/getOngoingBadge/{type}', [
        'as'   => '.getOngoingBadge',
        'uses' => 'ChallengeController@getOngoingBadge',
    ]);
    Route::get('/{challenge}/getMembersListOther', [
        'as'   => '.getMembersListOther',
        'uses' => 'ChallengeController@getMembersListOther',
    ]);
    Route::post('/cancel/{challenge}', [
        'as'   => '.cancel',
        'uses' => 'ChallengeController@cancel',
    ]);
    Route::post('/getDepartments', [
        'as'   => '.getDepartments',
        'uses' => 'ChallengeController@getDepartments',
    ]);
    Route::post('/getMemberData', [
        'as'   => '.getMemberData',
        'uses' => 'ChallengeController@getMemberData',
    ]);
    Route::post('exportChallengeDetails', [
        'as'   => '.exportChallengeDetails',
        'uses' => 'ChallengeController@exportChallengeDetails',
    ]);
});

// report module
Route::group([
    'as'     => '.reports',
    'prefix' => 'reports',
], function () {
    Route::get('/users-activities', [
        'as'   => '.users-activities',
        'uses' => 'ReportController@index',
    ]);
    Route::post('exportUserActivityReport', [
        'as'   => '.exportUserActivityReport',
        'uses' => 'ReportController@exportUserActivityReport',
    ]);
    Route::get('/getUserStepsData', [
        'as'   => '.getUserStepsData',
        'uses' => 'ReportController@getUserStepsData',
    ]);
    Route::get('/getUserExercisesData', [
        'as'   => '.getUserExercisesData',
        'uses' => 'ReportController@getUserExercisesData',
    ]);
    Route::get('/getUserMeditationsData', [
        'as'   => '.getUserMeditationsData',
        'uses' => 'ReportController@getUserMeditationsData',
    ]);
    Route::get('/nps', [
        'as'   => '.nps',
        'uses' => 'ReportController@getNPSFeedBack',
    ]);
    Route::get('/getNpsData', [
        'as'   => '.getNpsData',
        'uses' => 'ReportController@getNpsData',
    ]);
    Route::post('exportNpsReport', [
        'as'   => '.exportNpsReport',
        'uses' => 'ReportController@exportNpsReport',
    ]);
    Route::get('/inter-company', [
        'as'   => '.intercompanyreport',
        'uses' => 'ReportController@interCompanyReport',
    ]);
    Route::post('exportIntercompanyReport', [
        'as'   => '.exportIntercompanyReport',
        'uses' => 'ReportController@exportIntercompanyReport',
    ]);
    Route::get('/getICReportChallengeData', [
        'as'   => '.getICReportChallengeData',
        'uses' => 'ReportController@getICReportChallengeData',
    ]);
    Route::get('/getICReportChallengeComapnies', [
        'as'   => '.getICReportChallengeComapnies',
        'uses' => 'ReportController@getICReportChallengeComapnies',
    ]);

    Route::get('/challenge-activity', [
        'as'   => '.challengeactivityreport',
        'uses' => 'ReportController@challengeActivityReport',
    ]);

    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'ReportController@getChallenges',
    ]);

    Route::get('/getChallengeParticipant', [
        'as'   => '.getChallengeParticipant',
        'uses' => 'ReportController@getChallengeParticipant',
    ]);

    Route::post('/getChallengeSummaryData', [
        'as'   => '.getChallengeSummaryData',
        'uses' => 'ReportController@getChallengeSummaryData',
    ]);

    Route::post('/getChallengeDetailsData', [
        'as'   => '.getChallengeDetailsData',
        'uses' => 'ReportController@getChallengeDetailsData',
    ]);

    Route::post('/getChallengeDailySummaryData', [
        'as'   => '.getChallengeDailySummaryData',
        'uses' => 'ReportController@getChallengeDailySummaryData',
    ]);

    Route::post('exportChallengeActivityReport', [
        'as'   => '.exportChallengeActivityReport',
        'uses' => 'ReportController@exportChallengeActivityReport',
    ]);

    Route::get('/getUserDailyHistoryData', [
        'as'   => '.getUserDailyHistoryData',
        'uses' => 'ReportController@getUserDailyHistoryData',
    ]);

    Route::get('/getUserDailyHistoryTableData', [
        'as'   => '.getUserDailyHistoryTableData',
        'uses' => 'ReportController@getUserDailyHistoryTableData',
    ]);

    Route::post('/export-challenge-user-activity-report', [
        'as'   => '.export-challenge-user-activity-report',
        'uses' => 'ReportController@exportChallengeUserActivityReport',
    ]);

    // event booking report
    Route::get('/booking-report', [
        'as'   => '.booking-report',
        'uses' => 'BookingReportController@index',
    ]);
    Route::post('/detailed-report', [
        'as'   => '.detailed-report',
        'uses' => 'BookingReportController@detailedReport',
    ]);
    Route::post('/summary-report', [
        'as'   => '.summary-report',
        'uses' => 'BookingReportController@summaryReport',
    ]);
    Route::get('/booking-report/{company}/', [
        'as'   => '.booking-report-comapny-wise',
        'uses' => 'BookingReportController@bookingReportComapnyWise',
    ]);
    Route::post('/booking-report/{company}/', [
        'as'   => '.booking-report-comapny-wise',
        'uses' => 'BookingReportController@getBookingReportComapnyWise',
    ]);
    Route::get('/booking-report/calendar-booking-details/{eventBookingId}', [
        'as'   => '.calendar-booking-details',
        'uses' => 'BookingReportController@calendarBookingDetails',
    ]);
    Route::get('/file-unlink', [
        'as'   => '.file-unlink',
        'uses' => 'BookingReportController@fileUnlink',
    ]);
    Route::get('/calendar-report', [
        'as'   => '.calendar-report',
        'uses' => 'BookingReportController@calendarReport',
    ]);
    Route::post('exportBookingDetailReport', [
        'as'   => '.exportBookingDetailReport',
        'uses' => 'BookingReportController@exportBookingDetailReport',
    ]);
    Route::post('/exportBookingReportCompanyWise/{company}', [
        'as'   => '.exportBookingReportCompanyWise',
        'uses' => 'BookingReportController@exportBookingReportCompanyWise',
    ]);

    // masterclass feedback report
    Route::get('/masterclass-feedback', [
        'as'   => '.masterclass-feedback',
        'uses' => 'ReportController@masterclassFeedbackIndex',
    ]);
    Route::post('/masterclass-feedback', [
        'as'   => '.masterclass-feedback',
        'uses' => 'ReportController@getMasterclassFeedback',
    ]);
    Route::post('exportMasterclassFeedbackReport', [
        'as'   => '.exportMasterclassFeedbackReport',
        'uses' => 'ReportController@exportMasterclassFeedbackReport',
    ]);

    // Get Content Report
    Route::get('/content-report', [
        'as'   => '.content-report',
        'uses' => 'ContentReportController@index',
    ]);
    Route::get('/get-category-list/{category}', [
        'as'   => '.get-category-list',
        'uses' => 'ContentReportController@getCategoryList',
    ]);
    Route::get('/get-content-report', [
        'as'   => '.get-content-report',
        'uses' => 'ContentReportController@getContentReport',
    ]);
    Route::post('/export-content-report', [
        'as'   => '.export-content-report',
        'uses' => 'ContentReportController@exportContentReport',
    ]);

    // EAP feedback report
    Route::get('/eap-feedback', [
        'as'   => '.eap-feedback',
        'uses' => 'ReportController@eapFeedbackIndex',
    ]);
    Route::post('/eap-feedback', [
        'as'   => '.eap-feedback',
        'uses' => 'ReportController@getEapFeedback',
    ]);
    Route::post('exportCounsellorFeedbackReport', [
        'as'   => '.exportCounsellorFeedbackReport',
        'uses' => 'ReportController@exportCounsellorFeedbackReport',
    ]);

    // user registration report
    Route::get('/user-registration', [
        'as'   => '.user-registration',
        'uses' => 'ReportController@userRegistrationIndex',
    ]);
    Route::get('/get-user-registration', [
        'as'   => '.get-user-registration',
        'uses' => 'ReportController@getUserRegistration',
    ]);
    Route::post('exportUserRegistrationReport', [
        'as'   => '.exportUserRegistrationReport',
        'uses' => 'ReportController@exportUserRegistrationReport',
    ]);

    // Digital Therapy Reports
    Route::get('/digital-therapy', [
        'as'   => '.digital-therapy',
        'uses' => 'ReportController@digitalTherapyIndex',
    ]);
    Route::POST('/get-digital-therapy-report', [
        'as'   => '.get-digital-therapy-report',
        'uses' => 'ReportController@getDigitalTherapyReport',
    ]);
    Route::post('exportDigitalTherapyReport', [
        'as'   => '.exportDigitalTherapyReport',
        'uses' => 'ReportController@exportDigitalTherapyReport',
    ]);

    // Occupational Health Report
    Route::get('/occupational-health', [
        'as'   => '.occupational-health',
        'uses' => 'ReportController@occupationalHealthIndex',
    ]);
    Route::POST('/get-occupational-health-report', [
        'as'   => '.get-occupational-health-report',
        'uses' => 'ReportController@getOccupationalHealthReport',
    ]);
    Route::post('export-occupational-health-report', [
        'as'   => '.export-occupational-health-report',
        'uses' => 'ReportController@exportOccupationalHealthReport',
    ]);

    // Usage Report 
    Route::get('/usage-report', [
        'as'   => '.usage-report',
        'uses' => 'ReportController@usageReportIndex',
    ]);
    Route::POST('/get-usage-report', [
        'as'   => '.get-usage-report',
        'uses' => 'ReportController@getUsageReport',
    ]);
    Route::post('export-usage-report', [
        'as'   => '.export-usage-report',
        'uses' => 'ReportController@exportUsageReport',
    ]);
    Route::get('realtime-availability', [
        'as'   => '.realtime-availability',
        'uses' => 'ReportController@realtimeWbsAvailability',
    ]);
    Route::get('getLocationList/{company}', [
        'as'   => '.getLocationList',
        'uses' => 'ReportController@getLocationList',
    ]);
    Route::get('getWellbeingSpecialist/{company}', [
        'as'   => '.getWellbeingSpecialist',
        'uses' => 'ReportController@getWellbeingSpecialist',
    ]);
    Route::get('getWellbeingSpecialistLocation/{company}/{location}', [
        'as'   => '.getWellbeingSpecialistLocation',
        'uses' => 'ReportController@getWellbeingSpecialistLocation',
    ]);
    Route::POST('/generate-realtime-availability', [
        'as'   => '.generate-realtime-availability',
        'uses' => 'ReportController@generateRealtimeWbsAvailability',
    ]);
});

// CompanyDomain Module
Route::group([
    'as'     => '.domains',
    'prefix' => 'domains',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'DomainController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'DomainController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'DomainController@store',
    ]);
    Route::get('/{domain}/edit', [
        'as'   => '.edit',
        'uses' => 'DomainController@edit',
    ]);
    Route::patch('/{domain}/update', [
        'as'   => '.update',
        'uses' => 'DomainController@update',
    ]);
    Route::get('/getDomains', [
        'as'   => '.getDomains',
        'uses' => 'DomainController@getDomains',
    ]);
    Route::delete('/delete/{domain}', [
        'as'   => '.delete',
        'uses' => 'DomainController@delete',
    ]);
});

// Team challenges Module
Route::group([
    'as'     => '.teamChallenges',
    'prefix' => 'teamChallenges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeController@store',
    ]);
    Route::get('/{challenge}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeController@edit',
    ]);
    Route::patch('/{challenge}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeController@update',
    ]);
    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'ChallengeController@getChallenges',
    ]);
    Route::get('/{challenge}/details', [
        'as'   => '.details',
        'uses' => 'ChallengeController@getDetails',
    ]);
    Route::delete('/delete/{challenge}', [
        'as'   => '.delete',
        'uses' => 'ChallengeController@delete',
    ]);
    Route::get('/{challenge}/addPoints', [
        'as'   => '.addPoints',
        'uses' => 'ChallengeController@addPoints',
    ]);
    Route::patch('/{challenge}/managePoints', [
        'as'   => '.managePoints',
        'uses' => 'ChallengeController@managePoints',
    ]);
    Route::get('/{challenge}/getTeamMembersList', [
        'as'   => '.getTeamMembersList',
        'uses' => 'ChallengeController@getTeamMembersList',
    ]);
    Route::post('exportChallengeDetails', [
        'as'   => '.exportChallengeDetails',
        'uses' => 'ChallengeController@exportChallengeDetails',
    ]);
});

// Company goal challenges Module
Route::group([
    'as'     => '.companyGoalChallenges',
    'prefix' => 'companyGoalChallenges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeController@store',
    ]);
    Route::get('/{challenge}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeController@edit',
    ]);
    Route::patch('/{challenge}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeController@update',
    ]);
    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'ChallengeController@getChallenges',
    ]);
    Route::get('/{challenge}/details', [
        'as'   => '.details',
        'uses' => 'ChallengeController@getDetails',
    ]);
    Route::delete('/delete/{challenge}', [
        'as'   => '.delete',
        'uses' => 'ChallengeController@delete',
    ]);
    Route::get('/{challenge}/addPoints', [
        'as'   => '.addPoints',
        'uses' => 'ChallengeController@addPoints',
    ]);
    Route::patch('/{challenge}/managePoints', [
        'as'   => '.managePoints',
        'uses' => 'ChallengeController@managePoints',
    ]);
    Route::get('/{challenge}/getTeamMembersList', [
        'as'   => '.getTeamMembersList',
        'uses' => 'ChallengeController@getTeamMembersList',
    ]);
    Route::post('exportChallengeDetails', [
        'as'   => '.exportChallengeDetails',
        'uses' => 'ChallengeController@exportChallengeDetails',
    ]);
});

// Health score questions module
Route::group([
    'as'     => '.questions',
    'prefix' => 'questions',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'HealthScoreController@index',
    ]);
    Route::get('/getQuestions', [
        'as'   => '.getQuestions',
        'uses' => 'HealthScoreController@getQuestions',
    ]);
    Route::get('/{question}', [
        'as'   => '.show',
        'uses' => 'HealthScoreController@show',
    ]);
});

Route::group([
    'as'     => '.wellbeingSurveyBoard',
    'prefix' => 'wellbeing-survey-board',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'HealthScoreController@wellbeingSurveyBoardIndex',
    ]);
    Route::post('/get-chart-data', [
        'as'   => '.getChartData',
        'uses' => 'HealthScoreController@wellbeingSurveyChartData',
    ]);
});

// Recipe Module
Route::group([
    'as'     => '.recipe',
    'prefix' => 'recipe',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'RecipeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'RecipeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'RecipeController@store',
    ]);
    Route::get('/{recipe}/edit', [
        'as'   => '.edit',
        'uses' => 'RecipeController@edit',
    ]);
    Route::patch('/{recipe}/update', [
        'as'   => '.update',
        'uses' => 'RecipeController@update',
    ]);
    Route::get('/getRecipes', [
        'as'   => '.getRecipes',
        'uses' => 'RecipeController@getRecipes',
    ]);
    Route::delete('/delete/{recipe}', [
        'as'   => '.delete',
        'uses' => 'RecipeController@delete',
    ]);
    Route::get('/{recipe}/details', [
        'as'   => '.details',
        'uses' => 'RecipeController@getDetails',
    ]);
    Route::post('/approve/{recipe}', [
        'as'   => '.approve',
        'uses' => 'RecipeController@approve',
    ]);
});

// Inter-company challenges Module
Route::group([
    'as'     => '.interCompanyChallenges',
    'prefix' => 'interCompanyChallenges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeController@store',
    ]);
    Route::get('/{challenge}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeController@edit',
    ]);
    Route::patch('/{challenge}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeController@update',
    ]);
    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'ChallengeController@getChallenges',
    ]);
    Route::get('/{challenge}/details', [
        'as'   => '.details',
        'uses' => 'ChallengeController@getDetails',
    ]);
    Route::delete('/delete/{challenge}', [
        'as'   => '.delete',
        'uses' => 'ChallengeController@delete',
    ]);
    Route::get('/{challenge}/addPoints', [
        'as'   => '.addPoints',
        'uses' => 'ChallengeController@addPoints',
    ]);
    Route::patch('/{challenge}/managePoints', [
        'as'   => '.managePoints',
        'uses' => 'ChallengeController@managePoints',
    ]);
    Route::get('/{challenge}/getTeamMembersList', [
        'as'   => '.getTeamMembersList',
        'uses' => 'ChallengeController@getTeamMembersList',
    ]);
    Route::get('/{challenge}/getCompanyMembersList', [
        'as'   => '.getCompanyMembersList',
        'uses' => 'ChallengeController@getCompanyMembersList',
    ]);
    Route::post('exportReport', [
        'as'   => '.exportReport',
        'uses' => 'ChallengeController@exportReport',
    ]);
    Route::get('getexporthistory/{challenge}', [
        'as'   => '.getexporthistory',
        'uses' => 'ChallengeController@getexporthistory',
    ]);
    Route::get('setaccuratedata/{challenge}', [
        'as'   => '.setaccuratedata',
        'uses' => 'ChallengeController@setAccurateData',
    ]);
    Route::post('exportChallengeDetails', [
        'as'   => '.exportChallengeDetails',
        'uses' => 'ChallengeController@exportChallengeDetails',
    ]);
});

// Personal challenges Module
Route::group([
    'as'     => '.personalChallenges',
    'prefix' => 'personalChallenges',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'PersonalChallengeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'PersonalChallengeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'PersonalChallengeController@store',
    ]);
    Route::get('/{personalChallenge}/edit', [
        'as'   => '.edit',
        'uses' => 'PersonalChallengeController@edit',
    ]);
    Route::patch('/{personalChallenge}/update', [
        'as'   => '.update',
        'uses' => 'PersonalChallengeController@update',
    ]);
    Route::get('/getChallenges', [
        'as'   => '.getChallenges',
        'uses' => 'PersonalChallengeController@getChallenges',
    ]);
    Route::delete('/delete/{personalChallenge}', [
        'as'   => '.delete',
        'uses' => 'PersonalChallengeController@delete',
    ]);
});

// EAP Module
Route::group([
    'as'     => '.support',
    'prefix' => 'support',
], function () {
    Route::get('/introduction', [
        'as'   => '.introduction',
        'uses' => 'EAPController@introductionIndex',
    ]);
    Route::post('/introduction', [
        'as'   => '.introduction',
        'uses' => 'EAPController@storeIntroduction',
    ]);
    Route::get('/list', [
        'as'   => '.list',
        'uses' => 'EAPController@listIndex',
    ]);
    Route::get('/getEaps', [
        'as'   => '.getEaps',
        'uses' => 'EAPController@getEaps',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'EAPController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'EAPController@store',
    ]);
    Route::get('/{eap}/edit', [
        'as'   => '.edit',
        'uses' => 'EAPController@edit',
    ]);
    Route::patch('/{eap}/update', [
        'as'   => '.update',
        'uses' => 'EAPController@update',
    ]);
    Route::delete('/delete/{eap}', [
        'as'   => '.delete',
        'uses' => 'EAPController@delete',
    ]);
    Route::post('/reordering', [
        'as'   => '.reorderingEap',
        'uses' => 'EAPController@reorderingEap',
    ]);
    Route::post('/getDepartments', [
        'as'   => '.getDepartments',
        'uses' => 'EAPController@getDepartments',
    ]);
    Route::post('/stickUnstick/{eap}', [
        'as'   => '.stickUnstick',
        'uses' => 'EAPController@stickUnstick',
    ]);
});

// Moods Module
Route::group([
    'as'     => '.moods',
    'prefix' => 'moods',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'MoodsController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'MoodsController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'MoodsController@store',
    ]);
    Route::get('/{mood}/edit', [
        'as'   => '.edit',
        'uses' => 'MoodsController@edit',
    ]);
    Route::patch('/{mood}/update', [
        'as'   => '.update',
        'uses' => 'MoodsController@update',
    ]);
    Route::get('/getMoods', [
        'as'   => '.getMoods',
        'uses' => 'MoodsController@getMoods',
    ]);
    Route::delete('/delete/{mood}', [
        'as'   => '.delete',
        'uses' => 'MoodsController@delete',
    ]);
});

// Mood Tags Module
Route::group([
    'as'     => '.moodTags',
    'prefix' => 'moodTags',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'MoodTagsController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'MoodTagsController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'MoodTagsController@store',
    ]);
    Route::get('/{moodTag}/edit', [
        'as'   => '.edit',
        'uses' => 'MoodTagsController@edit',
    ]);
    Route::patch('/{moodTag}/update', [
        'as'   => '.update',
        'uses' => 'MoodTagsController@update',
    ]);
    Route::get('/getMoodTags', [
        'as'   => '.getMoodTags',
        'uses' => 'MoodTagsController@getMoodTags',
    ]);
    Route::delete('/delete/{moodTag}', [
        'as'   => '.delete',
        'uses' => 'MoodTagsController@delete',
    ]);
});

// Moods Analysis
Route::group([
    'as'     => '.moodAnalysis',
    'prefix' => 'moodAnalysis',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'MoodsAnalysisController@index',
    ]);
    Route::get('/usersData', [
        'as'   => '.usersData',
        'uses' => 'MoodsAnalysisController@getUsersData',
    ]);
    Route::get('/moodsData', [
        'as'   => '.moodsData',
        'uses' => 'MoodsAnalysisController@getMoodsData',
    ]);
    Route::get('/tagsData', [
        'as'   => '.tagsData',
        'uses' => 'MoodsAnalysisController@getTagsData',
    ]);
});

Route::group([
    'as'     => '.ckeditor-upload',
    'prefix' => 'ckeditor-upload',
], function () {
    Route::post('/masterclass-lesson', [
        'as'   => '.masterclass-lesson',
        'uses' => 'CommonController@storeArticleEditorFiles',
    ]);
    Route::post('/feed-description', [
        'as'   => '.feed-description',
        'uses' => 'CommonController@storeArticleEditorFiles',
    ]);
    Route::post('/session-description', [
        'as'   => '.session-description',
        'uses' => 'CommonController@storeArticleEditorFiles',
    ]);
    Route::post('/consentform-description', [
        'as'   => '.consentform-description',
        'uses' => 'CommonController@storeArticleEditorFiles',
    ]);
    Route::post('/shorts-description', [
        'as'   => '.shorts-description',
        'uses' => 'CommonController@storeArticleEditorFiles',
    ]);
});

// zc categories module
Route::group([
    'as'     => '.surveycategories',
    'prefix' => 'surveycategories',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcCategoriesController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ZcCategoriesController@create',
    ]);
    Route::post('/', [
        'as'   => '.store',
        'uses' => 'ZcCategoriesController@store',
    ]);

    Route::get('/{surveycategory}/edit', [
        'as'   => '.edit',
        'uses' => 'ZcCategoriesController@edit',
    ]);

    Route::patch('/{surveycategory}', [
        'as'   => '.update',
        'uses' => 'ZcCategoriesController@update',
    ]);

    Route::get('/getCategories', [
        'as'   => '.getCategories',
        'uses' => 'ZcCategoriesController@getCategories',
    ]);
    Route::delete('/delete/{surveycategory}', [
        'as'   => '.delete',
        'uses' => 'ZcCategoriesController@delete',
    ]);
});

Route::group([
    'as'     => '.surveysubcategories',
    'prefix' => 'surveysubcategories',
], function () {
    Route::get('/{surveycategory}/create', [
        'as'   => '.create',
        'uses' => 'ZcCategoriesController@createSub',
    ]);
    Route::post('/{surveycategory}/store', [
        'as'   => '.store',
        'uses' => 'ZcCategoriesController@storeSub',
    ]);
    Route::get('/{surveycategory}/{surveysubcategory}/edit', [
        'as'   => '.edit',
        'uses' => 'ZcCategoriesController@editSub',
    ]);
    Route::patch('/{surveycategory}/{surveysubcategory}/update', [
        'as'   => '.update',
        'uses' => 'ZcCategoriesController@updateSub',
    ]);
    Route::get('/getSubCategories', [
        'as'   => '.getSubCategories',
        'uses' => 'ZcCategoriesController@getSubCategories',
    ]);
    Route::delete('/delete/{surveysubcategory}', [
        'as'   => '.delete',
        'uses' => 'ZcCategoriesController@deleteSub',
    ]);
    Route::get('/{surveycategory}', [
        'as'   => '.index',
        'uses' => 'ZcCategoriesController@indexSub',
    ]);
});

// zc question bank module
Route::group([
    'as'     => '.zcquestionbank',
    'prefix' => 'zcquestionbank',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcQuestionBankController@index',
    ]);
    Route::get('/getQuestions', [
        'as'   => '.getQuestions',
        'uses' => 'ZcQuestionBankController@getQuestions',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ZcQuestionBankController@create',
    ]);
    Route::post('/create/short-question/{id?}', [
        'as'   => '.store.free-text-question',
        'uses' => 'ZcQuestionBankController@freeTextQuestionStoreUpdate',
    ]);
    Route::post('/create/choice-question/{id?}', [
        'as'   => '.store.choice-question',
        'uses' => 'ZcQuestionBankController@choiceQuestionStoreUpdate',
    ]);
    Route::get('/edit/{zcQuestion}', [
        'as'   => '.edit',
        'uses' => 'ZcQuestionBankController@edit',
    ]);
    Route::post('/publish/{zcQuestion}', [
        'as'   => '.publish',
        'uses' => 'ZcQuestionBankController@publish',
    ]);
    Route::delete('/delete/{zcQuestion}', [
        'as'   => '.delete',
        'uses' => 'ZcQuestionBankController@delete',
    ]);
    Route::get('/show/{zcQuestion}', [
        'as'   => '.show',
        'uses' => 'ZcQuestionBankController@show',
    ]);
    Route::get('/showQuestion/{zcQuestion}', [
        'as'   => '.showQuestion',
        'uses' => 'ZcQuestionBankController@getViewQuestionRecords',
    ]);
});

// zc survey module
Route::group([
    'as'     => '.zcsurvey',
    'prefix' => 'zcsurvey',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcSurveyController@index',
    ]);
    Route::get('/getSurveys', [
        'as'   => '.getSurveys',
        'uses' => 'ZcSurveyController@getSurveys',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ZcSurveyController@create',
    ]);
    Route::get('/getSurveySubCategories/{SurveyCategory}', [
        'as'      => '.getSurveySubCategories',
        'uses'    => 'ZcSurveyController@getSurveySubCategories',
        'laroute' => true,
    ]);
    Route::get('/getQuestions/{Survey?}', [
        'as'   => '.getQuestions',
        'uses' => 'ZcSurveyController@getQuestions',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ZcSurveyController@store',
    ]);
    Route::get('/edit/{zcSurvey}', [
        'as'   => '.edit',
        'uses' => 'ZcSurveyController@edit',
    ]);
    Route::post('/{zcSurvey}/update', [
        'as'   => '.update',
        'uses' => 'ZcSurveyController@update',
    ]);
    Route::post('/publish/{zcSurvey}', [
        'as'   => '.publish',
        'uses' => 'ZcSurveyController@publish',
    ]);
    Route::get('/copy/{zcSurvey}', [
        'as'   => '.copy',
        'uses' => 'ZcSurveyController@copy',
    ]);
    Route::delete('/delete/{zcSurvey}', [
        'as'   => '.delete',
        'uses' => 'ZcSurveyController@delete',
    ]);
    Route::get('/view/{zcSurvey}', [
        'as'   => '.view',
        'uses' => 'ZcSurveyController@view',
    ]);
    Route::get('/view-question/{zcSurvey}', [
        'as'   => '.view-question',
        'uses' => 'ZcSurveyController@viewQuestion',
    ]);
});

// zc survey review/suggestion
Route::group([
    'as'     => '.reviewSuggestion',
    'prefix' => 'review-suggestion',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcSurveyController@reviewSuggestion',
    ]);
    Route::get('/get-suggestions', [
        'as'   => '.getSuggestions',
        'uses' => 'ZcSurveyController@getSuggestions',
    ]);
    Route::get('/action/{suggestionId}', [
        'as'   => '.suggestionAction',
        'uses' => 'ZcSurveyController@suggestionAction',
    ]);
});

// zc survey insight
Route::group([
    'as'     => '.surveyInsights',
    'prefix' => 'survey-insights',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcSurveyController@surveyInsights',
    ]);
    Route::get('/get-survey-insights', [
        'as'   => '.getSurveyInsights',
        'uses' => 'ZcSurveyController@getSurveyInsights',
    ]);
    Route::get('/details/{surveyLogId}', [
        'as'   => '.getSurveyInsight',
        'uses' => 'ZcSurveyController@getSurveyInsight',
    ]);
    Route::get('/details/{surveyLogId}/questions/{categoryId}', [
        'as'   => '.getSurveyInsightQuestionsTableData',
        'uses' => 'ZcSurveyController@getSurveyInsightQuestionsTableData',
    ]);
});

// HR Report
Route::group([
    'as'     => '.hrReport',
    'prefix' => 'hr-report',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZcSurveyController@hrReport',
    ]);
    Route::get('/get-company-category/{company?}', [
        'as'   => '.getCompanyWiseCategoryForReviewText',
        'uses' => 'ZcSurveyController@getCompanyWiseCategoryForReviewText',
    ]);
    Route::post('/get-reports', [
        'as'   => '.gethrReportsData',
        'uses' => 'ZcSurveyController@gethrReportsData',
    ]);
    Route::get('/details/{companyId}/{departmentId}/{categoryId}', [
        'as'   => '.getHrReporDetails',
        'uses' => 'ZcSurveyController@getHrReporDetails',
    ]);
    Route::get('/review-free-text', [
        'as'   => '.reviewFreeText',
        'uses' => 'ZcSurveyController@reviewFreeText',
    ]);
    Route::get('/get-free-text-answers/{question}', [
        'as'   => '.getFreeTextAnswers',
        'uses' => 'ZcSurveyController@getFreeTextAnswers',
    ]);
});

// project survey module
Route::group([
    'as'     => '.projectsurvey',
    'prefix' => 'projectsurvey',
], function () {

    Route::post('/getProjectData', [
        'as'   => '.getProjectData',
        'uses' => 'CSProjectController@getProjectData',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'CSProjectController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'CSProjectController@store',
    ]);
    Route::get('/edit/{npsProject}', [
        'as'   => '.edit',
        'uses' => 'CSProjectController@edit',
    ]);
    Route::post('/{npsProject}/update', [
        'as'   => '.update',
        'uses' => 'CSProjectController@update',
    ]);
    Route::delete('/delete/{npsProject}', [
        'as'   => '.delete',
        'uses' => 'CSProjectController@delete',
    ]);
    Route::get('/view/{npsProject}', [
        'as'   => '.view',
        'uses' => 'CSProjectController@view',
    ]);

    Route::get('/getNpsProjectUserFeedBackTableData/{npsProject}', [
        'as'   => '.getNpsProjectUserFeedBackTableData',
        'uses' => 'CSProjectController@getNpsProjectUserFeedBackTableData',
    ]);

    Route::get('/getGraphData/{npsProject}', [
        'as'   => '.getGraphData',
        'uses' => 'CSProjectController@getGraphData',
    ]);

    Route::post('exportNpsProjectData', [
        'as'   => '.exportNpsProjectData',
        'uses' => 'CSProjectController@exportNpsProjectData',
    ]);
});

// Goals Module
Route::group([
    'as'     => '.goals',
    'prefix' => 'goals',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'GoalsController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'GoalsController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'GoalsController@store',
    ]);
    Route::get('/{goal}/view', [
        'as'   => '.view',
        'uses' => 'GoalsController@show',
    ]);
    Route::get('/{goal}/edit', [
        'as'   => '.edit',
        'uses' => 'GoalsController@edit',
    ]);
    Route::patch('/{goal}/update', [
        'as'   => '.update',
        'uses' => 'GoalsController@update',
    ]);
    Route::get('/getGoals', [
        'as'   => '.getGoals',
        'uses' => 'GoalsController@getGoals',
    ]);
    Route::get('/getGoalTags', [
        'as'   => '.getGoalTags',
        'uses' => 'GoalsController@getGoalsTags',
    ]);
    Route::delete('/delete/{goal}', [
        'as'   => '.delete',
        'uses' => 'GoalsController@delete',
    ]);
    Route::delete('/deletetag/{id}/{type?}', [
        'as'   => '.deletetag',
        'uses' => 'GoalsController@deletetag',
    ]);
});

// challenge image library
Route::group([
    'as'     => '.challengeImageLibrary',
    'prefix' => 'challenge-image-library',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeImageLibraryController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeImageLibraryController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeImageLibraryController@store',
    ]);
    Route::post('/store-bulk', [
        'as'   => '.storeBulk',
        'uses' => 'ChallengeImageLibraryController@storeBulk',
    ]);
    Route::get('/{image}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeImageLibraryController@edit',
    ]);
    Route::patch('/{image}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeImageLibraryController@update',
    ]);
    Route::get('/get-images', [
        'as'   => '.getImages',
        'uses' => 'ChallengeImageLibraryController@getImages',
    ]);
    Route::delete('/delete/{image}', [
        'as'   => '.delete',
        'uses' => 'ChallengeImageLibraryController@delete',
    ]);
});

// Labelsettings Module
Route::group([
    'as'     => '.labelsettings',
    'prefix' => 'label-settings',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'LabelsettingsController@index',
    ]);
    Route::get('/change-label', [
        'as'   => '.changelabel',
        'uses' => 'LabelsettingsController@changeLabel',
    ]);
    Route::get('/getlabelstrings', [
        'as'   => '.getlabelstrings',
        'uses' => 'LabelsettingsController@getlabelstrings',
    ]);
    Route::post('update', [
        'as'   => '.update',
        'uses' => 'LabelsettingsController@update',
    ]);
    Route::get('setdefault', [
        'as'   => '.setdefault',
        'uses' => 'LabelsettingsController@setdefault',
    ]);
});

// Webinar Module
Route::group([
    'as'     => '.webinar',
    'prefix' => 'webinar',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'WebinarController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'WebinarController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'WebinarController@store',
    ]);
    Route::post('/get-webinar', [
        'as'   => '.getwebinar',
        'uses' => 'WebinarController@getWebinar',
    ]);
    Route::get('/{webinar}/edit', [
        'as'   => '.edit',
        'uses' => 'WebinarController@edit',
    ]);
    Route::patch('/{webinar}/update', [
        'as'   => '.update',
        'uses' => 'WebinarController@update',
    ]);
    Route::delete('/delete/{webinar}', [
        'as'   => '.delete',
        'uses' => 'WebinarController@delete',
    ]);
});

// Event Module
Route::group([
    'as'     => '.event',
    'prefix' => 'event',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'EventController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'EventController@create',
    ]);
    Route::get('/get-presenters', [
        'as'   => '.getPresenters',
        'uses' => 'EventController@getPresenters',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'EventController@store',
    ]);
    Route::post('/publish/{event}', [
        'as'   => '.publish',
        'uses' => 'EventController@publishEvent',
    ]);
    Route::post('/get-events', [
        'as'   => '.getEvents',
        'uses' => 'EventController@getEvents',
    ]);
    Route::get('edit/{event}/{companyEvent?}', [
        'as'   => '.edit',
        'uses' => 'EventController@edit',
    ]);
    Route::patch('update/{event}/{companyEvent?}', [
        'as'   => '.update',
        'uses' => 'EventController@update',
    ]);
    Route::delete('/delete/{event}', [
        'as'   => '.delete',
        'uses' => 'EventController@delete',
    ]);
    Route::get('/{event}/view', [
        'as'   => '.view',
        'uses' => 'EventController@view',
    ]);
    Route::post('/{event}/view', [
        'as'   => '.view',
        'uses' => 'EventController@getEventCompaniesList',
    ]);
    Route::post('/cancel/{bookingLog}', [
        'as'   => '.cancelEvent',
        'uses' => 'EventController@cancelEvent',
    ]);
    Route::post('/cancel-details/{bookingLog}', [
        'as'   => '.cancelEventDetails',
        'uses' => 'EventController@cancelEventDetails',
    ]);
    Route::delete('/delete/{event}/{company}', [
        'as'   => '.deleteCompanyEvent',
        'uses' => 'EventController@deleteCompanyEvent',
    ]);
    Route::get('/{event}/feedback', [
        'as'   => '.feedback',
        'uses' => 'EventController@viewFeedback',
    ]);
    Route::post('/{event}/feedback', [
        'as'   => '.feedback',
        'uses' => 'EventController@getEventFeedback',
    ]);
});

// Marketplace Module
Route::group([
    'as'     => '.marketplace',
    'prefix' => 'marketplace',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'MarketplaceController@index',
    ]);
    Route::post('events', [
        'as'   => '.get-events',
        'uses' => 'MarketplaceController@getEvents',
    ]);
    Route::get('book-event-old/{event}', [
        'as'   => '.book-event-old',
        'uses' => 'MarketplaceController@bookEvent',
    ]);
    Route::get('book-event/{event}/{eventBookingLogsTemp?}', [
        'as'   => '.book-event',
        'uses' => 'MarketplaceController@bookEventNew',
    ]);
    Route::post('get-slot/{event}/{bookingLog?}', [
        'as'   => '.get-slot',
        'uses' => 'MarketplaceController@getSlots',
    ]);
    Route::patch('book-event/{event}/{eventBookingLogsTemp?}', [
        'as'         => '.confirm-event-booking',
        'uses'       => 'MarketplaceController@confirmEventBooking',
        // 'middleware' => ['cronofyEventAuthenticate'],
    ]);
    Route::post('/create-event-slot/{event}', [
        'as'   => '.create-event-slot',
        'uses' => 'MarketplaceController@createEventSlot',
    ]);
    Route::get('/check-credit/{company}', [
        'as'   => '.check-credit',
        'uses' => 'MarketplaceController@checkCredit',
    ]);
});

// AppTheme Module
Route::group([
    'as'     => '.app-themes',
    'prefix' => 'app-themes',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'AppThemeController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'AppThemeController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'AppThemeController@store',
    ]);
    Route::get('/{theme}/edit', [
        'as'   => '.edit',
        'uses' => 'AppThemeController@edit',
    ]);
    Route::patch('/{theme}/update', [
        'as'   => '.update',
        'uses' => 'AppThemeController@update',
    ]);
    Route::get('/get-themes', [
        'as'   => '.get-teams',
        'uses' => 'AppThemeController@getThemes',
    ]);
    Route::delete('/delete/{theme}', [
        'as'   => '.delete',
        'uses' => 'AppThemeController@delete',
    ]);
});

// AppTheme Module
Route::group([
    'as'     => '.broadcast-message',
    'prefix' => 'broadcast-message',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'BroadcastMessageController@index',
    ]);
    Route::post('/get-broadcasts', [
        'as'   => '.get-broadcasts',
        'uses' => 'BroadcastMessageController@getBroadcasts',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'BroadcastMessageController@create',
    ]);
    Route::post('/get-groups', [
        'as'   => '.get-groups',
        'uses' => 'BroadcastMessageController@getGroups',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'BroadcastMessageController@store',
    ]);
    Route::get('/{broadcast}/edit', [
        'as'   => '.edit',
        'uses' => 'BroadcastMessageController@edit',
    ]);
    Route::patch('/{broadcast}/update', [
        'as'   => '.update',
        'uses' => 'BroadcastMessageController@update',
    ]);
    Route::delete('/delete/{broadcast}', [
        'as'   => '.delete',
        'uses' => 'BroadcastMessageController@delete',
    ]);
});

// Calendly Sessions Module
Route::group([
    'as'     => '.sessions',
    'prefix' => 'sessions',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'CalendlyController@index',
    ]);
    Route::get('/getSessions', [
        'as'   => '.getSessions',
        'uses' => 'CalendlyController@getSessions',
    ]);
    Route::get('/show/{calendly}', [
        'as'   => '.show',
        'uses' => 'CalendlyController@show',
    ]);
    Route::patch('/complete/{calendly}', [
        'as'   => '.complete',
        'uses' => 'CalendlyController@markAsCompleted',
    ]);
    Route::patch('/update/{calendly}', [
        'as'   => '.update',
        'uses' => 'CalendlyController@update',
    ]);
});

// EAP Client list Module
Route::group([
    'as'     => '.clientlist',
    'prefix' => 'client-list',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ZendeskController@index',
    ]);
    Route::post('/get-clients', [
        'as'   => '.get-clients',
        'uses' => 'ZendeskController@getClients',
    ]);
    Route::get('/details/{ticket}', [
        'as'   => '.details',
        'uses' => 'ZendeskController@clientDetails',
    ]);
    Route::post('/client-sessions/{ticket}/{client}', [
        'as'   => '.client-sessions',
        'uses' => 'ZendeskController@getClientSessions',
    ]);
    Route::post('/notes/{ticket}/{type}', [
        'as'   => '.notes',
        'uses' => 'ZendeskController@getNotes',
    ]);
    Route::post('/note/{ticket}/add', [
        'as'   => '.add-note',
        'uses' => 'ZendeskController@addNote',
    ]);
    Route::get('/get-client-note', [
        'as'   => '.get-client-note',
        'uses' => 'ZendeskController@getNoteById',
    ]);
    Route::patch('/edit-note', [
        'as'   => '.edit-note',
        'uses' => 'ZendeskController@updateNoteById',
    ]);
    Route::delete('/delete/{id}', [
        'as'   => '.delete',
        'uses' => 'ZendeskController@deleteClientNote',
    ]);
    Route::delete('/delete-session-notes/{id}', [
        'as'   => '.delete-session-notes',
        'uses' => 'ZendeskController@deleteSessionNote',
    ]);
});

// Webinar Module
Route::group([
    'as'     => '.categoryTags',
    'prefix' => 'category-tags',
], function () {
    Route::get('/', [
        'as'   => '.tag-index',
        'uses' => 'CategoriesController@tagIndex',
    ]);
    Route::post('/get-tags', [
        'as'   => '.getTags',
        'uses' => 'CategoriesController@getTags',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'CategoriesController@createTags',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'CategoriesController@storeTags',
    ]);
    Route::get('/{category}/tags', [
        'as'   => '.view',
        'uses' => 'CategoriesController@viewCategoryTags',
    ]);
    Route::post('/{category}/tags', [
        'as'   => '.getCategoryTags',
        'uses' => 'CategoriesController@getCategoryTags',
    ]);
    Route::get('/{tag}/edit', [
        'as'   => '.edit',
        'uses' => 'CategoriesController@editTag',
    ]);
    Route::patch('/{tag}/update', [
        'as'   => '.update',
        'uses' => 'CategoriesController@updateTag',
    ]);
    Route::delete('/delete/{tag}', [
        'as'   => '.delete',
        'uses' => 'CategoriesController@deleteTag',
    ]);
});

// Company Plan Module
Route::group([
    'as'     => '.company-plan',
    'prefix' => 'company-plan',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'CompanyplanController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'CompanyplanController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'CompanyplanController@store',
    ]);
    Route::post('/get-companyplan', [
        'as'   => '.getcompanyplan',
        'uses' => 'CompanyplanController@getCompanyplan',
    ]);
    Route::get('/{cpPlan}/edit', [
        'as'   => '.edit',
        'uses' => 'CompanyplanController@edit',
    ]);
    Route::patch('/{cpPlan}/update', [
        'as'   => '.update',
        'uses' => 'CompanyplanController@update',
    ]);
    Route::delete('/delete/{cpPlan}', [
        'as'   => '.delete',
        'uses' => 'CompanyplanController@delete',
    ]);
});

// Marketplace Module
Route::group([
    'as'     => '.bookings',
    'prefix' => 'bookings',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'BookingController@index',
    ]);
    Route::post('get-booked-events', [
        'as'   => '.get-booked-events',
        'uses' => 'BookingController@getBookedEvents',
    ]);
    Route::get('booking-details/{eventBookingId}', [
        'as'   => '.booking-details',
        'uses' => 'BookingController@bookingDetails',
    ]);
    Route::post('cancel-event', [
        'as'   => '.cancel-event',
        'uses' => 'BookingController@cancelEvent',
    ]);
    Route::get('edit-booked-event/{bookingLog}/{eventBookingLogsTemp?}', [
        'as'   => '.edit-booked-event',
        'uses' => 'BookingController@editBookedEvent',
    ]);
    Route::post('edit-booked-event/{bookingLog}/{eventBookingLogsTemp?}', [
        'as'   => '.edit-booked-event',
        'uses' => 'BookingController@updateBookedEvent',
    ]);
    Route::get('registered-users/{eventBookingId}', [
        'as'   => '.registered-users',
        'uses' => 'BookingController@eventRegisteredUsers',
    ]);
    Route::post('get-registered-users/{eventBookingId}', [
        'as'   => '.get-registered-users',
        'uses' => 'BookingController@getEventRegisteredUsers',
    ]);
    Route::post('exportBookings', [
        'as'   => '.exportBookings',
        'uses' => 'BookingController@exportBookings',
    ]);
});

// challenge map library
Route::group([
    'as'     => '.challengeMapLibrary',
    'prefix' => 'challenge-map-library',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ChallengeMapLibraryController@index',
    ]);
    Route::get('get-map-library', [
        'as'   => '.getMapLibrary',
        'uses' => 'ChallengeMapLibraryController@getMapLibrary',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ChallengeMapLibraryController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ChallengeMapLibraryController@store',
    ]);
    Route::get('/{map}/edit', [
        'as'   => '.edit',
        'uses' => 'ChallengeMapLibraryController@edit',
    ]);
    Route::get('/{map}/step-2', [
        'as'   => '.step-2',
        'uses' => 'ChallengeMapLibraryController@edit',
    ]);
    Route::patch('/{map}/step-save', [
        'as'   => '.step-save',
        'uses' => 'ChallengeMapLibraryController@stepSave',
    ]);
    Route::patch('/{map}/update', [
        'as'   => '.update',
        'uses' => 'ChallengeMapLibraryController@update',
    ]);
    Route::delete('/delete/{map}', [
        'as'   => '.delete',
        'uses' => 'ChallengeMapLibraryController@delete',
    ]);
    Route::delete('/deletelocation/{mapLocation}', [
        'as'   => '.deletelocation',
        'uses' => 'ChallengeMapLibraryController@deleteLocation',
    ]);
    Route::get('/getMapLocation/{map}', [
        'as'   => '.getMapLocation',
        'uses' => 'ChallengeMapLibraryController@getMapLocation',
    ]);
    Route::get('/getLocation/{mapLocation}', [
        'as'   => '.getLocation',
        'uses' => 'ChallengeMapLibraryController@getLocation',
    ]);
    Route::PATCH('/store-property', [
        'as'   => '.store-property',
        'uses' => 'ChallengeMapLibraryController@storeProperty',
    ]);
    Route::get('/getMapDetails/{map}', [
        'as'   => '.getMapDetails',
        'uses' => 'ChallengeMapLibraryController@getMapDetails',
    ]);
    Route::PATCH('/store-lat-long', [
        'as'   => '.store-lat-long',
        'uses' => 'ChallengeMapLibraryController@storeLatLong',
    ]);
    Route::delete('/archive/{map}', [
        'as'   => '.archive',
        'uses' => 'ChallengeMapLibraryController@archive',
    ]);
});

Route::group([
    'as'     => '.services',
    'prefix' => 'services',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ServicesController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ServicesController@create',
    ]);
    Route::post('/', [
        'as'   => '.store',
        'uses' => 'ServicesController@store',
    ]);
    Route::get('/{service}/edit', [
        'as'   => '.edit',
        'uses' => 'ServicesController@edit',
    ]);
    Route::patch('/{service}', [
        'as'   => '.update',
        'uses' => 'ServicesController@update',
    ]);
    Route::get('/getServices', [
        'as'   => '.getServices',
        'uses' => 'ServicesController@getServices',
    ]);
    Route::delete('/{service}', [
        'as'   => '.delete',
        'uses' => 'ServicesController@delete',
    ]);
});

Route::group([
    'as'     => '.companiesold',
    'prefix' => 'companiesold',
], function () {
    Route::delete('/delete/{companiesold}', [
        'as'   => '.delete',
        'uses' => 'CompaniesOldController@delete',
    ]);
    Route::get('/getCompanies', [
        'as'   => '.getCompanies',
        'uses' => 'CompaniesOldController@getCompanies',
    ]);
    Route::get('/{companiesold}/getCompanyTeams', [
        'as'   => '.getCompanyTeams',
        'uses' => 'CompaniesOldController@getCompanyTeams',
    ]);
    Route::get('/{companiesold}/getCompanyModerators', [
        'as'   => '.getCompanyModerators',
        'uses' => 'CompaniesOldController@getCompanyModerators',
    ]);
    Route::get('/{companiesold}/getLimitsList', [
        'as'   => '.getLimitsList',
        'uses' => 'CompaniesOldController@getLimitsList',
    ]);
    Route::post('/{companiesold}/set-default-limits', [
        'as'   => '.setDefaultLimits',
        'uses' => 'CompaniesOldController@setDefaultLimits',
    ]);
    Route::post('/changeAppSettingStoreUpdate', [
        'as'   => '.changeAppSettingStoreUpdate',
        'uses' => 'CompaniesOldController@changeAppSettingStoreUpdate',
    ]);
    Route::get('/{companiesold}/changeToDefaultSettings', [
        'as'   => '.changeToDefaultSettings',
        'uses' => 'CompaniesOldController@changeToDefaultSettings',
    ]);
    Route::get('/get-survey-details/{companiesold}/{type}', [
        'as'   => '.get-survey-details',
        'uses' => 'CompaniesOldController@getSurveyDetails',
    ]);
    Route::post('/export-survey-report/{companiesold}/{type}', [
        'as'   => '.export-survey-report',
        'uses' => 'CompaniesOldController@exportSurveyReport',
    ]);
    Route::post('/survey-configuration/{companiesold}', [
        'as'   => '.set-survey-configuration',
        'uses' => 'CompaniesOldController@setSurveyConfiguration',
    ]);
    Route::get('/reseller-details', [
        'as'   => '.resellerDetails',
        'uses' => 'CompaniesOldController@resellerDetails',
    ]);
    Route::group([
        'prefix' => '{companyType}',
    ], function () {
        Route::get('/', [
            'as'   => '.index',
            'uses' => 'CompaniesOldController@index',
        ]);
        Route::get('/create', [
            'as'   => '.create',
            'uses' => 'CompaniesOldController@create',
        ]);
        Route::post('/store', [
            'as'   => '.store',
            'uses' => 'CompaniesOldController@store',
        ]);
        Route::get('/{companiesold}/edit', [
            'as'   => '.edit',
            'uses' => 'CompaniesOldController@edit',
        ]);
        Route::patch('/{companiesold}/update', [
            'as'   => '.update',
            'uses' => 'CompaniesOldController@update',
        ]);
        Route::get('/{companiesold}/createModerator', [
            'as'   => '.createModerator',
            'uses' => 'CompaniesOldController@createModerator',
        ]);
        Route::patch('/{companiesold}/storeModerator', [
            'as'   => '.storeModerator',
            'uses' => 'CompaniesOldController@storeModerator',
        ]);
        Route::get('/{companiesold}/moderators', [
            'as'   => '.moderators',
            'uses' => 'CompaniesOldController@moderators',
        ]);
        Route::get('/{companiesold}/teams', [
            'as'   => '.teams',
            'uses' => 'CompaniesOldController@teams',
        ]);
        Route::get('/{companiesold}/getLimits', [
            'as'   => '.getLimits',
            'uses' => 'CompaniesOldController@getLimits',
        ]);
        Route::get('/{companiesold}/editLimits', [
            'as'   => '.editLimits',
            'uses' => 'CompaniesOldController@editLimits',
        ]);
        Route::patch('/{companiesold}/updateLimits', [
            'as'   => '.updateLimits',
            'uses' => 'CompaniesOldController@updateLimits',
        ]);
        Route::get('/{companiesold}/changeAppSettingIndex', [
            'as'   => '.changeAppSettingIndex',
            'uses' => 'CompaniesOldController@changeAppSettingIndex',
        ]);
        Route::get('/getCompanyAppSettings', [
            'as'   => '.getCompanyAppSettings',
            'uses' => 'CompaniesOldController@getCompanyAppSettings',
        ]);
        Route::get('/{companiesold}/changeAppSettingCreateEdit', [
            'as'   => '.changeAppSettingCreateEdit',
            'uses' => 'CompaniesOldController@changeAppSettingCreateEdit',
        ]);
        Route::get('/survey-configuration/{companiesold}', [
            'as'   => '.survey-configuration',
            'uses' => 'CompaniesOldController@surveyConfiguration',
        ]);
        Route::get('/{companiesold}/portalFooter', [
            'as'   => '.portalFooter',
            'uses' => 'CompaniesOldController@portalFooter',
        ]);
        Route::patch('/{companiesold}/storePortalFooterDetails', [
            'as'   => '.storePortalFooterDetails',
            'uses' => 'CompaniesOldController@storePortalFooterDetails',
        ]);
    });
});
Route::group([
    'as'     => '.cronofy',
    'prefix' => 'cronofy',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'CronofyController@index',
    ]);
    Route::get('/authenticate', [
        'as'   => '.authenticate',
        'uses' => 'CronofyController@authenticate',
    ]);
    Route::get('/callback', [
        'as'   => '.callback',
        'uses' => 'CronofyController@callback',
    ]);
    Route::get('/getCalendar', [
        'as'   => '.getCalendar',
        'uses' => 'CronofyController@getCalendar',
    ]);
    Route::get('/linkCalendar', [
        'as'         => '.linkCalendar',
        'uses'       => 'CronofyController@linkCalendar',
        'middleware' => ['cronofyAuthenticate'],
    ]);
    Route::get('/unlinkCalendar/{profileId}', [
        'as'         => '.unlinkCalendar',
        'uses'       => 'CronofyController@unlinkCalendar',
        'middleware' => ['cronofyAuthenticate'],
    ]);
    Route::get('/primary/{cronofyCalendar}', [
        'as'   => '.primary',
        'uses' => 'CronofyController@primaryCalendar',
    ]);
    Route::get('/availability', [
        'as'   => '.availability',
        'uses' => 'CronofyController@availability',
    ]);
    Route::post('/store/{user}', [
        'as'         => '.store',
        'uses'       => 'CronofyController@storeAvailability',
        'middleware' => ['cronofyAuthenticate'],
    ]);
    Route::get('/updateDashboad', [
        'as'   => '.updateDashboad',
        'uses' => 'CronofyController@updateDashboad',
    ]);
    Route::group([
        'as'     => '.clientlist',
        'prefix' => 'clientlist',
    ], function () {
        Route::get('/', [
            'as'   => '.index',
            'uses' => 'CronofyClientController@index',
        ]);
        Route::post('/get-clients', [
            'as'   => '.get-clients',
            'uses' => 'CronofyClientController@getClients',
        ]);
        Route::get('/details/{cronofySchedule}', [
            'as'   => '.details',
            'uses' => 'CronofyClientController@clientDetails',
        ]);
        Route::post('/client-sessions/{cronofySchedule}/{client}', [
            'as'   => '.client-sessions',
            'uses' => 'CronofyClientController@getClientSessions',
        ]);
        Route::post('/notes/{cronofySchedule}', [
            'as'   => '.notes',
            'uses' => 'CronofyClientController@getNotes',
        ]);
        Route::post('/note/{cronofySchedule}/add', [
            'as'   => '.add-note',
            'uses' => 'CronofyClientController@addNote',
        ]);
        Route::post('/consent/{cronofySchedule}/send', [
            'as'   => '.send-consent',
            'uses' => 'CronofyClientController@sendConsent',
        ]);
        Route::get('/get-client-note', [
            'as'   => '.get-client-note',
            'uses' => 'CronofyClientController@getNoteById',
        ]);
        Route::patch('/edit-note', [
            'as'   => '.edit-note',
            'uses' => 'CronofyClientController@updateNoteById',
        ]);
        Route::delete('/delete/{id}', [
            'as'   => '.delete',
            'uses' => 'CronofyClientController@deleteClientNote',
        ]);
        Route::delete('/delete-session-notes/{id}', [
            'as'   => '.delete-session-notes',
            'uses' => 'CronofyClientController@deleteSessionNote',
        ]);
        Route::post('exportNotes/{cronofySchedule}', [
            'as'   => '.exportNotes',
            'uses' => 'CronofyClientController@exportNotes',
        ]);
        Route::get('/get-attachments/{cronofySchedule}', [
            'as'   => '.get-attachments',
            'uses' => 'CronofyClientController@getAttachmentsForClients',
        ]);
        Route::get('/health-referral/{cronofySchedule}', [
            'as'   => '.health-referral',
            'uses' => 'CronofyClientController@addHealthReferral',
        ]);
        Route::post('/store-health-referral/{cronofySchedule}', [
            'as'   => '.store-health-referral',
            'uses' => 'CronofyClientController@storeHealthReferral',
        ]);
        Route::get('/get-location/{company?}', [
            'as'   => '.get-location',
            'uses' => 'CronofyClientController@getLocation',
        ]);
        Route::post('/send-email-for-accss-kin-info', [
            'as'   => '.send-email-for-accss-kin-info',
            'uses' => 'CronofyClientController@sendEmailForAccessKinInfo',
        ]);
        Route::post('/export-client', [
            'as'   => '.export-client',
            'uses' => 'CronofyClientController@exportClient',
        ]);
    });
    Route::group([
        'as'     => '.sessions',
        'prefix' => 'sessions',
    ], function () {
        Route::get('/', [
            'as'   => '.index',
            'uses' => 'CronofySessionController@index',
        ]);
        Route::get('/get-sessions', [
            'as'   => '.get-sessions',
            'uses' => 'CronofySessionController@getSessions',
        ]);
        Route::post('/get-sessions', [
            'as'   => '.get-sessions',
            'uses' => 'CronofySessionController@getSessions',
        ]);
        Route::get('/show/{cronofySchedule}', [
            'as'   => '.show',
            'uses' => 'CronofySessionController@show',
        ]);
        Route::patch('/complete/{cronofySchedule}', [
            'as'   => '.complete',
            'uses' => 'CronofySessionController@markAsCompleted',
        ]);
        Route::patch('/update/{cronofySchedule}', [
            'as'   => '.update',
            'uses' => 'CronofySessionController@updateSession',
        ]);
        Route::post('/cancel-session/{cronofySchedule}', [
            'as'         => '.cancel-session',
            'uses'       => 'CronofySessionController@cancelSession',
            'middleware' => ['cronofyAuthenticate'],
        ]);
        Route::get('/reschedule-session/{cronofySchedule}', [
            'as'   => '.reschedule-session',
            'uses' => 'CronofySessionController@rescheduleSession',
        ]);
        Route::get('/callback', [
            'as'   => '.callback',
            'uses' => 'CronofySessionController@sessionCallback',
        ]);
        Route::get('/create/{type}', [
            'as'   => '.create',
            'uses' => 'CronofySessionController@createSession',
        ]);
        Route::get('/get-sub-categories/{service?}', [
            'as'   => '.get-sub-categories',
            'uses' => 'CronofySessionController@getSubCategories',
        ]);
        Route::get('/get-users/{company?}', [
            'as'   => '.get-users',
            'uses' => 'CronofySessionController@getUsers',
        ]);
        Route::post('/storeGroupSession/{type}', [
            'as'         => '.storeGroupSession',
            'uses'       => 'CronofySessionController@storeGroupSession',
            'middleware' => ['cronofyAuthenticate'],
        ]);
        Route::get('/{cronofySchedule}/edit', [
            'as'   => '.edit',
            'uses' => 'CronofySessionController@editSession',
        ]);
        Route::post('/updateGroupSession/{cronofySchedule}', [
            'as'   => '.updateGroupSession',
            'uses' => 'CronofySessionController@updateGroupSession',
        ]);
        Route::get('/get-ws-users/{serviceSubCategory}', [
            'as'   => '.get-ws-users',
            'uses' => 'CronofySessionController@getWSUsers',
        ]);
        Route::get('/get-ws-users-list/{serviceSubCategory}', [
            'as'   => '.get-ws-users-list',
            'uses' => 'CronofySessionController@getWSUsersList',
        ]);
        Route::post('/create-event-slot', [
            'as'   => '.create-event-slot',
            'uses' => 'CronofySessionController@createEventSlot',
        ]);
        Route::post('/cronofy-exception', [
            'as'   => '.cronofy-exception',
            'uses' => 'CronofySessionController@cronofyException',
        ]);
        Route::get('/email-logs/{cronofySchedule}', [
            'as'   => '.email-logs',
            'uses' => 'CronofySessionController@emailLogs',
        ]);
        Route::get('/email-log-list/{cronofySchedule}', [
            'as'   => '.email-log-list',
            'uses' => 'CronofySessionController@getEmailLogsData',
        ]);
        Route::patch('/send-session-email/{cronofySchedule}', [
            'as'   => '.send-session-email',
            'uses' => 'CronofySessionController@sendSessionEmail',
        ]);
        Route::post('/store-attachments/{cronofySchedule}', [
            'as'   => '.store-attachments',
            'uses' => 'CronofySessionController@storeAttachments',
        ]);
        Route::get('/get-attachments/{cronofySchedule}', [
            'as'   => '.get-attachments',
            'uses' => 'CronofySessionController@getSessionAttachments',
        ]);
        Route::delete('/delete-attachment/{sessionAttachment}', [
            'as'   => '.delete-attachment',
            'uses' => 'CronofySessionController@deleteSessionAttachment',
        ]);
        Route::get('/download-attachment/{sessionAttachment}', [
            'as'   => '.download-attachment',
            'uses' => 'CronofySessionController@downloadSessionAttachments',
        ]);
        Route::get('/get-company-locations/{company?}', [
            'as'   => '.get-company-locations',
            'uses' => 'CronofySessionController@getCompanyLocations',
        ]);    
    });
    Route::group([
        'as'     => '.consent-form',
        'prefix' => 'consent-form',
    ], function () {
        Route::get('/', [
            'as'   => '.index',
            'uses' => 'ConsentFormController@index',
        ]);
        Route::get('/getConsents', [
            'as'   => '.getConsents',
            'uses' => 'ConsentFormController@getConsents',
        ]);
        Route::get('/{consentForm?}/edit', [
            'as'   => '.edit',
            'uses' => 'ConsentFormController@editConsentForm',
        ]);
        Route::post('/{consentForm?}/update', [
            'as'   => '.update',
            'uses' => 'ConsentFormController@updateconsentform',
        ]);
    });
});

// Content challenge Module
Route::group([
    'as'     => '.contentChallenge',
    'prefix' => 'contentChallenge',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ContentChallengeController@index',
    ]);
    Route::get('/getCategories', [
        'as'   => '.getCategories',
        'uses' => 'ContentChallengeController@getCategories',
    ]);
    Route::post('/{contentChallenge?}/update', [
        'as'   => '.update',
        'uses' => 'ContentChallengeController@updateContentChallenge',
    ]);
});

Route::group([
    'as'     => '.contentChallengeActivity',
    'prefix' => 'contentChallengeActivity',
], function () {
    Route::get('/getContentChallengeActivities', [
        'as'   => '.getContentChallengeActivities',
        'uses' => 'ContentChallengeController@getContentChallengeActivities',
    ]);
    Route::get('/{contentChallenge}', [
        'as'   => '.index',
        'uses' => 'ContentChallengeController@indexActivities',
    ]);
    Route::patch('/updateActivity', [
        'as'   => '.updateActivity',
        'uses' => 'ContentChallengeController@updateActivity',
    ]);
});

// Podcast Module
Route::group([
    'as'     => '.podcasts',
    'prefix' => 'podcasts',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'PodcastController@index',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'PodcastController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'PodcastController@store',
    ]);
    Route::get('/{podcast}/edit', [
        'as'   => '.edit',
        'uses' => 'PodcastController@edit',
    ]);
    Route::patch('/{podcast}/update', [
        'as'   => '.update',
        'uses' => 'PodcastController@update',
    ]);
    Route::post('/getPodcasts', [
        'as'   => '.getPodcasts',
        'uses' => 'PodcastController@getPodcasts',
    ]);
    Route::get('/{podcast}/details', [
        'as'   => '.details',
        'uses' => 'PodcastController@getDetails',
    ]);
    Route::delete('/delete/{podcast}', [
        'as'   => '.delete',
        'uses' => 'PodcastController@delete',
    ]);
    Route::get('/{podcast}/getMembersList', [
        'as'   => '.getMembersList',
        'uses' => 'PodcastController@getMembersList',
    ]);
});

Route::group([
    'as'     => '.admin-alerts',
    'prefix' => 'admin-alerts',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'AdminAlertController@index',
    ]);
    Route::get('/getAdminAlerts', [
        'as'   => '.getAdminAlerts',
        'uses' => 'AdminAlertController@getAdminAlerts',
    ]);
    Route::get('/{adminAlert?}/edit', [
        'as'   => '.edit',
        'uses' => 'AdminAlertController@editAdminAlert',
    ]);
    Route::post('/{adminAlert?}/update', [
        'as'   => '.update',
        'uses' => 'AdminAlertController@updateAdminAlert',
    ]);
});

// Shorts Module
Route::group([
    'as'     => '.shorts',
    'prefix' => 'shorts',
], function () {
    Route::get('/', [
        'as'   => '.index',
        'uses' => 'ShortsController@index',
    ]);
    Route::post('/get-shorts', [
        'as'   => '.getshorts',
        'uses' => 'ShortsController@getShorts',
    ]);
    Route::get('/create', [
        'as'   => '.create',
        'uses' => 'ShortsController@create',
    ]);
    Route::post('/store', [
        'as'   => '.store',
        'uses' => 'ShortsController@store',
    ]);
    Route::get('/{shorts}/edit', [
        'as'   => '.edit',
        'uses' => 'ShortsController@edit',
    ]);
    Route::patch('/{shorts}/update', [
        'as'   => '.update',
        'uses' => 'ShortsController@update',
    ]);
    Route::delete('/delete/{shorts}', [
        'as'   => '.delete',
        'uses' => 'ShortsController@delete',
    ]);
});
