<?php
declare (strict_types = 1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

/*
|--------------------------------------------------------------------------
| Routes v1
|--------------------------------------------------------------------------
|
 */

Route::group([
    'prefix'    => 'v1',
    'as'        => '.v1',
    'namespace' => 'API\V1',
], function () {
    Route::group([
        'prefix'    => 'auth',
        'as'        => '.auth',
        'namespace' => 'Auth',
    ], function () {
        require_once(base_path('routes/api/v1/auth.php'));
    });

    Route::group([
        'prefix' => 'onboard',
        'as'     => '.onboard',
    ], function () {
        require_once(base_path('routes/api/v1/onboard.php'));
    });

    Route::group([
        'prefix'     => 'course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/course.php'));
    });

    Route::group([
        'prefix'     => 'notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/notification.php'));
    });

    Route::group([
        'prefix'     => 'feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/feed.php'));
    });

    Route::group([
        'prefix'     => 'badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/badge.php'));
    });

    Route::group([
        'prefix'     => 'profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/profile.php'));
    });

    Route::group([
        'prefix'     => 'recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/recipe.php'));
    });

    Route::group([
        'prefix'     => 'meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/meditation.php'));
    });

    Route::group([
        'prefix'     => 'group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/group.php'));
    });

    Route::group([
        'prefix' => 'plans',
        'as'     => '.plans',
    ], function () {
        require_once(base_path('routes/api/v1/plans.php'));
    });

    Route::group([
        'prefix'     => 'move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/move.php'));
    });

    Route::group([
        'prefix'     => 'challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/challenge.php'));
    });

    Route::group([
        'prefix'     => 'common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/common.php'));
    });

    Route::group([
        'prefix'     => 'nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/nourish.php'));
    });

    Route::group([
        'prefix'     => 'inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/inspire.php'));
    });

    Route::group([
        'prefix'     => 'healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
    ], function () {
        require_once(base_path('routes/api/v1/healthscore.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v2
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v2',
], function () {
    Route::group([
        'prefix'    => 'v2/auth',
        'as'        => '.auth',
        'namespace' => 'API\V2\Auth',
    ], function () {
        require_once(base_path('routes/api/v2/auth.php'));
    });

    Route::group([
        'prefix'    => 'v2/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v2/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v2/course.php'));
    });

    Route::group([
        'prefix'     => 'v2/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/notification.php'));
    });

    Route::group([
        'prefix'     => 'v2/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v2/feed.php'));
    });

    Route::group([
        'prefix'     => 'v2/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/badge.php'));
    });

    Route::group([
        'prefix'     => 'v2/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/profile.php'));
    });

    Route::group([
        'prefix'     => 'v2/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v2/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v2/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v2/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/group.php'));
    });

    Route::group([
        'prefix'    => 'v2/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/plans.php'));
    });

    Route::group([
        'prefix'     => 'v2/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/move.php'));
    });

    Route::group([
        'prefix'     => 'v2/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v2/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v2/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v2/common.php'));
    });

    Route::group([
        'prefix'     => 'v2/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v2/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v2/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v2/healthscore.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v3
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v3',
], function () {
    Route::group([
        'prefix'    => 'v3/auth',
        'as'        => '.auth',
        'namespace' => 'API\V3\Auth',
    ], function () {
        require_once(base_path('routes/api/v3/auth.php'));
    });

    Route::group([
        'prefix'    => 'v3/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v3/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v3/course.php'));
    });

    Route::group([
        'prefix'     => 'v3/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/notification.php'));
    });

    Route::group([
        'prefix'     => 'v3/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V2',
    ], function () {
        require_once(base_path('routes/api/v3/feed.php'));
    });

    Route::group([
        'prefix'     => 'v3/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/badge.php'));
    });

    Route::group([
        'prefix'     => 'v3/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/profile.php'));
    });

    Route::group([
        'prefix'     => 'v3/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v3/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v3/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/group.php'));
    });

    Route::group([
        'prefix'    => 'v3/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/plans.php'));
    });

    Route::group([
        'prefix'     => 'v3/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/move.php'));
    });

    Route::group([
        'prefix'     => 'v3/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v3/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/common.php'));
    });

    Route::group([
        'prefix'     => 'v3/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v3/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v3/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v3/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v3/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/moods.php'));
    });

    Route::group([
        'prefix'     => 'v3/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v3/personalChallenge.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v4
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v4',
], function () {
    Route::group([
        'prefix'    => 'v4/auth',
        'as'        => '.auth',
        'namespace' => 'API\V4\Auth',
    ], function () {
        require_once(base_path('routes/api/v4/auth.php'));
    });

    Route::group([
        'prefix'    => 'v4/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v4/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/course.php'));
    });

    Route::group([
        'prefix'     => 'v4/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/notification.php'));
    });

    Route::group([
        'prefix'     => 'v4/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/feed.php'));
    });

    Route::group([
        'prefix'     => 'v4/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v4/badge.php'));
    });

    Route::group([
        'prefix'     => 'v4/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/profile.php'));
    });

    Route::group([
        'prefix'     => 'v4/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v4/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v4/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/group.php'));
    });

    Route::group([
        'prefix'    => 'v4/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/plans.php'));
    });

    Route::group([
        'prefix'     => 'v4/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/move.php'));
    });

    Route::group([
        'prefix'     => 'v4/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v4/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v4/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v4/common.php'));
    });

    Route::group([
        'prefix'     => 'v4/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v4/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v4/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v4/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v4/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v4/moods.php'));
    });

    Route::group([
        'prefix'     => 'v4/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v4/personalChallenge.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v5
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v5',
], function () {
    Route::group([
        'prefix'    => 'v5/auth',
        'as'        => '.auth',
        'namespace' => 'API\V5\Auth',
    ], function () {
        require_once(base_path('routes/api/v5/auth.php'));
    });

    Route::group([
        'prefix'    => 'v5/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v5/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v5/course.php'));
    });

    Route::group([
        'prefix'     => 'v5/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/notification.php'));
    });

    Route::group([
        'prefix'     => 'v5/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/feed.php'));
    });

    Route::group([
        'prefix'     => 'v5/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v5/badge.php'));
    });

    Route::group([
        'prefix'     => 'v5/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/profile.php'));
    });

    Route::group([
        'prefix'     => 'v5/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v5/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v5/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v5/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v5/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/group.php'));
    });

    Route::group([
        'prefix'    => 'v5/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/plans.php'));
    });

    Route::group([
        'prefix'     => 'v5/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/move.php'));
    });

    Route::group([
        'prefix'     => 'v5/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v5/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/common.php'));
    });

    Route::group([
        'prefix'     => 'v5/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v5/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v5/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v5/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v5/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v5/moods.php'));
    });

    Route::group([
        'prefix'     => 'v5/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V5',
    ], function () {
        require_once(base_path('routes/api/v5/personalChallenge.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v6
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v6',
], function () {
    Route::group([
        'prefix'    => 'v6/auth',
        'as'        => '.auth',
        'namespace' => 'API\V6\Auth',
    ], function () {
        require_once(base_path('routes/api/v6/auth.php'));
    });

    Route::group([
        'prefix'    => 'v6/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v6/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v6/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v6/course.php'));
    });

    Route::group([
        'prefix'     => 'v6/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/notification.php'));
    });

    Route::group([
        'prefix'     => 'v6/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/feed.php'));
    });

    Route::group([
        'prefix'     => 'v6/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v6/badge.php'));
    });

    Route::group([
        'prefix'     => 'v6/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/profile.php'));
    });

    Route::group([
        'prefix'     => 'v6/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v6/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v6/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v6/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/group.php'));
    });

    Route::group([
        'prefix'    => 'v6/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v6/plans.php'));
    });

    Route::group([
        'prefix'     => 'v6/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v6/move.php'));
    });

    Route::group([
        'prefix'     => 'v6/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v6/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/common.php'));
    });

    Route::group([
        'prefix'     => 'v6/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v6/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v6/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v6/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v6/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v6/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/moods.php'));
    });

    Route::group([
        'prefix'     => 'v6/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v6/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/eap.php'));
    });

    Route::group([
        'prefix'     => 'v6/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v6/masterclass.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v7
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v7',
], function () {
    Route::group([
        'prefix'    => 'v7/auth',
        'as'        => '.auth',
        'namespace' => 'API\V6\Auth',
    ], function () {
        require_once(base_path('routes/api/v7/auth.php'));
    });

    Route::group([
        'prefix'    => 'v7/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v7/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v7/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v7/course.php'));
    });

    Route::group([
        'prefix'     => 'v7/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/notification.php'));
    });

    Route::group([
        'prefix'     => 'v7/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v7/feed.php'));
    });

    Route::group([
        'prefix'     => 'v7/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v7/badge.php'));
    });

    Route::group([
        'prefix'     => 'v7/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/profile.php'));
    });

    Route::group([
        'prefix'     => 'v7/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v7/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v7/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v7/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/group.php'));
    });

    Route::group([
        'prefix'    => 'v7/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v7/plans.php'));
    });

    Route::group([
        'prefix'     => 'v7/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v7/move.php'));
    });

    Route::group([
        'prefix'     => 'v7/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v7/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v7/common.php'));
    });

    Route::group([
        'prefix'     => 'v7/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v7/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v7/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v7/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v7/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v7/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v7/moods.php'));
    });

    Route::group([
        'prefix'     => 'v7/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v7/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v7/eap.php'));
    });

    Route::group([
        'prefix'     => 'v7/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v7/masterclass.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v8
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v8',
], function () {
    Route::group([
        'prefix'    => 'v8/auth',
        'as'        => '.auth',
        'namespace' => 'API\V8\Auth',
    ], function () {
        require_once(base_path('routes/api/v8/auth.php'));
    });

    Route::group([
        'prefix'    => 'v8/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v8/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v8/course.php'));
    });

    Route::group([
        'prefix'     => 'v8/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v8/notification.php'));
    });

    Route::group([
        'prefix'     => 'v8/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v8/feed.php'));
    });

    Route::group([
        'prefix'     => 'v8/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v8/badge.php'));
    });

    Route::group([
        'prefix'     => 'v8/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/profile.php'));
    });

    Route::group([
        'prefix'     => 'v8/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v8/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v8/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v8/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/group.php'));
    });

    Route::group([
        'prefix'    => 'v8/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v8/plans.php'));
    });

    Route::group([
        'prefix'     => 'v8/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v8/move.php'));
    });

    Route::group([
        'prefix'     => 'v8/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v8/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v8/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/common.php'));
    });

    Route::group([
        'prefix'     => 'v8/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v8/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v8/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v8/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v8/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v8/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v8/moods.php'));
    });

    Route::group([
        'prefix'     => 'v8/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v8/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v8/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v8/eap.php'));
    });

    Route::group([
        'prefix'     => 'v8/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v8/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v8/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v8/goal.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v9
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v9',
], function () {
    Route::group([
        'prefix'    => 'v9/auth',
        'as'        => '.auth',
        'namespace' => 'API\V8\Auth',
    ], function () {
        require_once(base_path('routes/api/v9/auth.php'));
    });

    Route::group([
        'prefix'    => 'v9/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v9/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v9/course',
        'as'         => '.course',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v9/course.php'));
    });

    Route::group([
        'prefix'     => 'v9/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v9/notification.php'));
    });

    Route::group([
        'prefix'     => 'v9/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v9/feed.php'));
    });

    Route::group([
        'prefix'     => 'v9/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v9/badge.php'));
    });

    Route::group([
        'prefix'     => 'v9/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v9/profile.php'));
    });

    Route::group([
        'prefix'     => 'v9/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v9/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v9/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v9/group',
        'as'         => '.group',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/group.php'));
    });

    Route::group([
        'prefix'    => 'v9/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v9/plans.php'));
    });

    Route::group([
        'prefix'     => 'v9/move',
        'as'         => '.move',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v9/move.php'));
    });

    Route::group([
        'prefix'     => 'v9/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v9/common',
        'as'         => '.common',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/common.php'));
    });

    Route::group([
        'prefix'     => 'v9/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v9/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v9/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v9/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v9/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v9/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v9/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v9/moods.php'));
    });

    Route::group([
        'prefix'     => 'v9/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v9/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v9/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v9/eap.php'));
    });

    Route::group([
        'prefix'     => 'v9/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v9/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v9/goal.php'));
    });

    Route::group([
        'prefix'     => 'v9/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v9/challengeImages.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v10
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v10',
], function () {
    Route::group([
        'prefix'    => 'v10/auth',
        'as'        => '.auth',
        'namespace' => 'API\V10\Auth',
    ], function () {
        require_once(base_path('routes/api/v10/auth.php'));
    });

    Route::group([
        'prefix'    => 'v10/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V10',
    ], function () {
        require_once(base_path('routes/api/v10/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v10/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v10/course.php'));
    });

    Route::group([
        'prefix'     => 'v10/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v10/notification.php'));
    });

    Route::group([
        'prefix'     => 'v10/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v10/feed.php'));
    });

    Route::group([
        'prefix'     => 'v10/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v10/badge.php'));
    });

    Route::group([
        'prefix'     => 'v10/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v10/profile.php'));
    });

    Route::group([
        'prefix'     => 'v10/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v10/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v10/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v10/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v10/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v10/group.php'));
    });

    Route::group([
        'prefix'    => 'v10/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v10/plans.php'));
    });

    Route::group([
        'prefix'     => 'v10/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v10/move.php'));
    });

    Route::group([
        'prefix'     => 'v10/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v10/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v10/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V10',
    ], function () {
        require_once(base_path('routes/api/v10/common.php'));
    });

    Route::group([
        'prefix'     => 'v10/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v10/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v10/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v10/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v10/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v10/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v10/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v10/moods.php'));
    });

    Route::group([
        'prefix'     => 'v10/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v10/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v10/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v10/eap.php'));
    });

    Route::group([
        'prefix'     => 'v10/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V10',
    ], function () {
        require_once(base_path('routes/api/v10/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v10/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v10/goal.php'));
    });

    Route::group([
        'prefix'     => 'v10/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v10/challengeImages.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v11
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v11',
], function () {
    Route::group([
        'prefix'    => 'v11/auth',
        'as'        => '.auth',
        'namespace' => 'API\V11\Auth',
    ], function () {
        require_once(base_path('routes/api/v11/auth.php'));
    });

    Route::group([
        'prefix'    => 'v11/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V10',
    ], function () {
        require_once(base_path('routes/api/v11/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v11/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v11/course.php'));
    });

    Route::group([
        'prefix'     => 'v11/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v11/notification.php'));
    });

    Route::group([
        'prefix'     => 'v11/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/feed.php'));
    });

    Route::group([
        'prefix'     => 'v11/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v11/badge.php'));
    });

    Route::group([
        'prefix'     => 'v11/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/profile.php'));
    });

    Route::group([
        'prefix'     => 'v11/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v11/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v11/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v11/group.php'));
    });

    Route::group([
        'prefix'    => 'v11/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v11/plans.php'));
    });

    Route::group([
        'prefix'     => 'v11/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/move.php'));
    });

    Route::group([
        'prefix'     => 'v11/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v11/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v11/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/common.php'));
    });

    Route::group([
        'prefix'     => 'v11/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v11/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v11/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v11/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v11/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v11/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v11/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v11/moods.php'));
    });

    Route::group([
        'prefix'     => 'v11/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v11/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v11/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/eap.php'));
    });

    Route::group([
        'prefix'     => 'v11/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v11/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v11/goal.php'));
    });

    Route::group([
        'prefix'     => 'v11/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v11/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v11/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v11/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v11/event.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v12
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v12',
], function () {
    Route::group([
        'prefix'    => 'v12/auth',
        'as'        => '.auth',
        'namespace' => 'API\V12\Auth',
    ], function () {
        require_once(base_path('routes/api/v12/auth.php'));
    });

    Route::group([
        'prefix'    => 'v12/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v12/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v12/course.php'));
    });

    Route::group([
        'prefix'     => 'v12/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/notification.php'));
    });

    Route::group([
        'prefix'     => 'v12/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/feed.php'));
    });

    Route::group([
        'prefix'     => 'v12/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v12/badge.php'));
    });

    Route::group([
        'prefix'     => 'v12/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v12/profile.php'));
    });

    Route::group([
        'prefix'     => 'v12/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v12/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v12/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v12/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v12/group.php'));
    });

    Route::group([
        'prefix'    => 'v12/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v12/plans.php'));
    });

    Route::group([
        'prefix'     => 'v12/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/move.php'));
    });

    Route::group([
        'prefix'     => 'v12/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v12/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v12/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/common.php'));
    });

    Route::group([
        'prefix'     => 'v12/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v12/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v12/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v12/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v12/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v12/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v12/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v12/moods.php'));
    });

    Route::group([
        'prefix'     => 'v12/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v12/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v12/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v12/eap.php'));
    });

    Route::group([
        'prefix'     => 'v12/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v12/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v12/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v12/goal.php'));
    });

    Route::group([
        'prefix'     => 'v12/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v12/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v12/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v12/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/event.php'));
    });

    Route::group([
        'prefix'     => 'v12/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v12/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v13
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v13',
], function () {
    Route::group([
        'prefix'    => 'v13/auth',
        'as'        => '.auth',
        'namespace' => 'API\V12\Auth',
    ], function () {
        require_once(base_path('routes/api/v13/auth.php'));
    });

    Route::group([
        'prefix'    => 'v13/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v13/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v13/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v13/course.php'));
    });

    Route::group([
        'prefix'     => 'v13/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v13/notification.php'));
    });

    Route::group([
        'prefix'     => 'v13/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v13/feed.php'));
    });

    Route::group([
        'prefix'     => 'v13/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v13/badge.php'));
    });

    Route::group([
        'prefix'     => 'v13/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v13/profile.php'));
    });

    Route::group([
        'prefix'     => 'v13/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v13/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v13/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v13/group.php'));
    });

    Route::group([
        'prefix'    => 'v13/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v13/plans.php'));
    });

    Route::group([
        'prefix'     => 'v13/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v13/move.php'));
    });

    Route::group([
        'prefix'     => 'v13/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v13/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v13/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/common.php'));
    });

    Route::group([
        'prefix'     => 'v13/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v13/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v13/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v13/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v13/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v13/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v13/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v13/moods.php'));
    });

    Route::group([
        'prefix'     => 'v13/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v13/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v13/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v13/eap.php'));
    });

    Route::group([
        'prefix'     => 'v13/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v13/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v13/goal.php'));
    });

    Route::group([
        'prefix'     => 'v13/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v13/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v13/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v13/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/event.php'));
    });

    Route::group([
        'prefix'     => 'v13/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v13/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v14
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v14',
], function () {
    Route::group([
        'prefix'    => 'v14/auth',
        'as'        => '.auth',
        'namespace' => 'API\V12\Auth',
    ], function () {
        require_once(base_path('routes/api/v14/auth.php'));
    });

    Route::group([
        'prefix'    => 'v14/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v14/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v14/course.php'));
    });

    Route::group([
        'prefix'     => 'v14/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v14/notification.php'));
    });

    Route::group([
        'prefix'     => 'v14/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/feed.php'));
    });

    Route::group([
        'prefix'     => 'v14/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v14/badge.php'));
    });

    Route::group([
        'prefix'     => 'v14/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v14/profile.php'));
    });

    Route::group([
        'prefix'     => 'v14/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v14/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v14/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v14/group.php'));
    });

    Route::group([
        'prefix'    => 'v14/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v14/plans.php'));
    });

    Route::group([
        'prefix'     => 'v14/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v14/move.php'));
    });

    Route::group([
        'prefix'     => 'v14/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v14/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v14/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V13',
    ], function () {
        require_once(base_path('routes/api/v14/common.php'));
    });

    Route::group([
        'prefix'     => 'v14/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v14/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v14/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v14/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v14/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v14/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v14/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v14/moods.php'));
    });

    Route::group([
        'prefix'     => 'v14/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v14/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v14/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v14/eap.php'));
    });

    Route::group([
        'prefix'     => 'v14/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v14/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v14/goal.php'));
    });

    Route::group([
        'prefix'     => 'v14/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v14/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v14/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v14/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/event.php'));
    });

    Route::group([
        'prefix'     => 'v14/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v14/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v15
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v15',
], function () {
    Route::group([
        'prefix'    => 'v15/auth',
        'as'        => '.auth',
        'namespace' => 'API\V15\Auth',
    ], function () {
        require_once(base_path('routes/api/v15/auth.php'));
    });

    Route::group([
        'prefix'    => 'v15/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v15/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v15/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v15/course.php'));
    });

    Route::group([
        'prefix'     => 'v15/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v15/notification.php'));
    });

    Route::group([
        'prefix'     => 'v15/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v15/feed.php'));
    });

    Route::group([
        'prefix'     => 'v15/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v15/badge.php'));
    });

    Route::group([
        'prefix'     => 'v15/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v15/profile.php'));
    });

    Route::group([
        'prefix'     => 'v15/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v15/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v15/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v15/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v15/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v15/group.php'));
    });

    Route::group([
        'prefix'    => 'v15/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v15/plans.php'));
    });

    Route::group([
        'prefix'     => 'v15/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v15/move.php'));
    });

    Route::group([
        'prefix'     => 'v15/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v15/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v15/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v15/common.php'));
    });

    Route::group([
        'prefix'     => 'v15/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v15/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v15/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v15/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v15/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v15/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v15/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v15/moods.php'));
    });

    Route::group([
        'prefix'     => 'v15/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v15/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v15/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v15/eap.php'));
    });

    Route::group([
        'prefix'     => 'v15/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v15/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v15/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v15/goal.php'));
    });

    Route::group([
        'prefix'     => 'v15/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v15/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v15/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v15/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v15/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v15/event.php'));
    });

    Route::group([
        'prefix'     => 'v15/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v15/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v16
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v16',
], function () {
    Route::group([
        'prefix'    => 'v16/auth',
        'as'        => '.auth',
        'namespace' => 'API\V15\Auth',
    ], function () {
        require_once(base_path('routes/api/v16/auth.php'));
    });

    Route::group([
        'prefix'    => 'v16/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v16/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v16/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v16/course.php'));
    });

    Route::group([
        'prefix'     => 'v16/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V16',
    ], function () {
        require_once(base_path('routes/api/v16/notification.php'));
    });

    Route::group([
        'prefix'     => 'v16/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v16/feed.php'));
    });

    Route::group([
        'prefix'     => 'v16/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V3',
    ], function () {
        require_once(base_path('routes/api/v16/badge.php'));
    });

    Route::group([
        'prefix'     => 'v16/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v16/profile.php'));
    });

    Route::group([
        'prefix'     => 'v16/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v16/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v16/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v16/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v16/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v16/group.php'));
    });

    Route::group([
        'prefix'    => 'v16/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v16/plans.php'));
    });

    Route::group([
        'prefix'     => 'v16/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V12',
    ], function () {
        require_once(base_path('routes/api/v16/move.php'));
    });

    Route::group([
        'prefix'     => 'v16/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v16/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v16/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V16',
    ], function () {
        require_once(base_path('routes/api/v16/common.php'));
    });

    Route::group([
        'prefix'     => 'v16/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v16/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v16/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v16/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v16/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v16/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v16/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v16/moods.php'));
    });

    Route::group([
        'prefix'     => 'v16/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v16/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v16/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v16/eap.php'));
    });

    Route::group([
        'prefix'     => 'v16/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V16',
    ], function () {
        require_once(base_path('routes/api/v16/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v16/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v16/goal.php'));
    });

    Route::group([
        'prefix'     => 'v16/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v16/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v16/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v16/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v16/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v16/event.php'));
    });

    Route::group([
        'prefix'     => 'v16/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v16/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v17
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v17',
], function () {
    Route::group([
        'prefix'    => 'v17/auth',
        'as'        => '.auth',
        'namespace' => 'API\V17\Auth',
    ], function () {
        require_once(base_path('routes/api/v17/auth.php'));
    });

    Route::group([
        'prefix'    => 'v17/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v17/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v17/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v17/course.php'));
    });

    Route::group([
        'prefix'     => 'v17/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V16',
    ], function () {
        require_once(base_path('routes/api/v17/notification.php'));
    });

    Route::group([
        'prefix'     => 'v17/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/feed.php'));
    });

    Route::group([
        'prefix'     => 'v17/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/badge.php'));
    });

    Route::group([
        'prefix'     => 'v17/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v17/profile.php'));
    });

    Route::group([
        'prefix'     => 'v17/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v17/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v17/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v17/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/group.php'));
    });

    Route::group([
        'prefix'    => 'v17/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v17/plans.php'));
    });

    Route::group([
        'prefix'     => 'v17/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/move.php'));
    });

    Route::group([
        'prefix'     => 'v17/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v17/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v17/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/common.php'));
    });

    Route::group([
        'prefix'     => 'v17/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v17/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v17/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v17/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v17/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v17/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v17/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v17/moods.php'));
    });

    Route::group([
        'prefix'     => 'v17/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v17/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v17/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V11',
    ], function () {
        require_once(base_path('routes/api/v17/eap.php'));
    });

    Route::group([
        'prefix'     => 'v17/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v17/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v17/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v17/goal.php'));
    });

    Route::group([
        'prefix'     => 'v17/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v17/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v17/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v17/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v17/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v17/event.php'));
    });

    Route::group([
        'prefix'     => 'v17/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v17/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v18
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v18',
], function () {
    Route::group([
        'prefix'    => 'v18/auth',
        'as'        => '.auth',
        'namespace' => 'API\V18\Auth',
    ], function () {
        require_once(base_path('routes/api/v18/auth.php'));
    });

    Route::group([
        'prefix'    => 'v18/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v18/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v18/course.php'));
    });

    Route::group([
        'prefix'     => 'v18/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/notification.php'));
    });

    Route::group([
        'prefix'     => 'v18/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v18/feed.php'));
    });

    Route::group([
        'prefix'     => 'v18/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v18/badge.php'));
    });

    Route::group([
        'prefix'     => 'v18/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/profile.php'));
    });

    Route::group([
        'prefix'     => 'v18/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v18/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v18/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v18/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v18/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v18/group.php'));
    });

    Route::group([
        'prefix'    => 'v18/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v18/plans.php'));
    });

    Route::group([
        'prefix'     => 'v18/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v18/move.php'));
    });

    Route::group([
        'prefix'     => 'v18/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v18/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/common.php'));
    });

    Route::group([
        'prefix'     => 'v18/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v18/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v18/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v18/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v18/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v18/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v18/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v18/moods.php'));
    });

    Route::group([
        'prefix'     => 'v18/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v18/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v18/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/eap.php'));
    });

    Route::group([
        'prefix'     => 'v18/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v18/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v18/goal.php'));
    });

    Route::group([
        'prefix'     => 'v18/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v18/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v18/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v18/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v18/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v18/event.php'));
    });

    Route::group([
        'prefix'     => 'v18/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v18/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v19
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v19',
], function () {
    Route::group([
        'prefix'    => 'v19/auth',
        'as'        => '.auth',
        'namespace' => 'API\V19\Auth',
    ], function () {
        require_once(base_path('routes/api/v19/auth.php'));
    });

    Route::group([
        'prefix'    => 'v19/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v19/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v19/course.php'));
    });

    Route::group([
        'prefix'     => 'v19/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/notification.php'));
    });

    Route::group([
        'prefix'     => 'v19/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v19/feed.php'));
    });

    Route::group([
        'prefix'     => 'v19/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v19/badge.php'));
    });

    Route::group([
        'prefix'     => 'v19/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/profile.php'));
    });

    Route::group([
        'prefix'     => 'v19/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v19/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v19/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v19/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v19/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V19',
    ], function () {
        require_once(base_path('routes/api/v19/group.php'));
    });

    Route::group([
        'prefix'    => 'v19/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v19/plans.php'));
    });

    Route::group([
        'prefix'     => 'v19/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v19/move.php'));
    });

    Route::group([
        'prefix'     => 'v19/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V19',
    ], function () {
        require_once(base_path('routes/api/v19/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v19/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/common.php'));
    });

    Route::group([
        'prefix'     => 'v19/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v19/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v19/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v19/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v19/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v19/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v19/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v19/moods.php'));
    });

    Route::group([
        'prefix'     => 'v19/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v19/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v19/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/eap.php'));
    });

    Route::group([
        'prefix'     => 'v19/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v19/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v19/goal.php'));
    });

    Route::group([
        'prefix'     => 'v19/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v19/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v19/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v19/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v19/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v19/event.php'));
    });

    Route::group([
        'prefix'     => 'v19/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v19/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v20
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v20',
], function () {
    Route::group([
        'prefix'    => 'v20/auth',
        'as'        => '.auth',
        'namespace' => 'API\V20\Auth',
    ], function () {
        require_once(base_path('routes/api/v20/auth.php'));
    });

    Route::group([
        'prefix'    => 'v20/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v20/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v20/course.php'));
    });

    Route::group([
        'prefix'     => 'v20/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v20/notification.php'));
    });

    Route::group([
        'prefix'     => 'v20/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v20/feed.php'));
    });

    Route::group([
        'prefix'     => 'v20/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v20/badge.php'));
    });

    Route::group([
        'prefix'     => 'v20/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/profile.php'));
    });

    Route::group([
        'prefix'     => 'v20/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v20/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v20/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v20/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v20/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V19',
    ], function () {
        require_once(base_path('routes/api/v20/group.php'));
    });

    Route::group([
        'prefix'    => 'v20/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v20/plans.php'));
    });

    Route::group([
        'prefix'     => 'v20/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v20/move.php'));
    });

    Route::group([
        'prefix'     => 'v20/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v20/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/common.php'));
    });

    Route::group([
        'prefix'     => 'v20/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v20/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v20/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v20/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v20/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v20/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v20/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v20/moods.php'));
    });

    Route::group([
        'prefix'     => 'v20/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v20/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v20/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v20/eap.php'));
    });

    Route::group([
        'prefix'     => 'v20/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v20/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v20/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v20/goal.php'));
    });

    Route::group([
        'prefix'     => 'v20/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v20/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v20/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v20/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v20/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/event.php'));
    });

    Route::group([
        'prefix'     => 'v20/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v20/portalhome.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v21
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v21',
], function () {
    Route::group([
        'prefix'    => 'v21/auth',
        'as'        => '.auth',
        'namespace' => 'API\V21\Auth',
    ], function () {
        require_once(base_path('routes/api/v21/auth.php'));
    });

    Route::group([
        'prefix'    => 'v21/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v21/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v21/course.php'));
    });

    Route::group([
        'prefix'     => 'v21/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v21/notification.php'));
    });

    Route::group([
        'prefix'     => 'v21/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v21/feed.php'));
    });

    Route::group([
        'prefix'     => 'v21/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v21/badge.php'));
    });

    Route::group([
        'prefix'     => 'v21/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v21/profile.php'));
    });

    Route::group([
        'prefix'     => 'v21/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v21/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v21/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v21/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v21/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V19',
    ], function () {
        require_once(base_path('routes/api/v21/group.php'));
    });

    Route::group([
        'prefix'    => 'v21/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v21/plans.php'));
    });

    Route::group([
        'prefix'     => 'v21/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v21/move.php'));
    });

    Route::group([
        'prefix'     => 'v21/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v21/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v21/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/common.php'));
    });

    Route::group([
        'prefix'     => 'v21/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v21/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v21/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v21/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v21/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v21/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v21/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v21/moods.php'));
    });

    Route::group([
        'prefix'     => 'v21/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v21/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/eap.php'));
    });

    Route::group([
        'prefix'     => 'v21/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v21/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v21/goal.php'));
    });

    Route::group([
        'prefix'     => 'v21/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v21/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v21/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v21/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v21/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/event.php'));
    });

    Route::group([
        'prefix'     => 'v21/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v21/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v21/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v21/counsellor.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v22
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v22',
], function () {
    Route::group([
        'prefix'    => 'v22/auth',
        'as'        => '.auth',
        'namespace' => 'API\V22\Auth',
    ], function () {
        require_once(base_path('routes/api/v22/auth.php'));
    });

    Route::group([
        'prefix'    => 'v22/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v22/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v22/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v22/course.php'));
    });

    Route::group([
        'prefix'     => 'v22/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v22/notification.php'));
    });

    Route::group([
        'prefix'     => 'v22/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/feed.php'));
    });

    Route::group([
        'prefix'     => 'v22/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/badge.php'));
    });

    Route::group([
        'prefix'     => 'v22/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v22/profile.php'));
    });

    Route::group([
        'prefix'     => 'v22/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V17',
    ], function () {
        require_once(base_path('routes/api/v22/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v22/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V15',
    ], function () {
        require_once(base_path('routes/api/v22/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v22/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/group.php'));
    });

    Route::group([
        'prefix'    => 'v22/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v22/plans.php'));
    });

    Route::group([
        'prefix'     => 'v22/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/move.php'));
    });

    Route::group([
        'prefix'     => 'v22/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v22/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v22/common.php'));
    });

    Route::group([
        'prefix'     => 'v22/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v22/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v22/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v22/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v22/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v22/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v22/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v22/moods.php'));
    });

    Route::group([
        'prefix'     => 'v22/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v22/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v22/eap.php'));
    });

    Route::group([
        'prefix'     => 'v22/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v22/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v22/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v22/goal.php'));
    });

    Route::group([
        'prefix'     => 'v22/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v22/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v22/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v22/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v22/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/event.php'));
    });

    Route::group([
        'prefix'     => 'v22/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v22/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v22/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v22/counsellor.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v23
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v23',
], function () {
    Route::group([
        'prefix'    => 'v23/auth',
        'as'        => '.auth',
        'namespace' => 'API\V23\Auth',
    ], function () {
        require_once(base_path('routes/api/v23/auth.php'));
    });

    Route::group([
        'prefix'    => 'v23/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v23/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v23/course.php'));
    });

    Route::group([
        'prefix'     => 'v23/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v23/notification.php'));
    });

    Route::group([
        'prefix'     => 'v23/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v23/feed.php'));
    });

    Route::group([
        'prefix'     => 'v23/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/badge.php'));
    });

    Route::group([
        'prefix'     => 'v23/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v23/profile.php'));
    });

    Route::group([
        'prefix'     => 'v23/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v23/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v23/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v23/group.php'));
    });

    Route::group([
        'prefix'    => 'v23/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v23/plans.php'));
    });

    Route::group([
        'prefix'     => 'v23/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/move.php'));
    });

    Route::group([
        'prefix'     => 'v23/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v23/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v23/common.php'));
    });

    Route::group([
        'prefix'     => 'v23/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v23/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v23/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v23/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v23/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v23/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v23/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v23/moods.php'));
    });

    Route::group([
        'prefix'     => 'v23/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v23/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v23/eap.php'));
    });

    Route::group([
        'prefix'     => 'v23/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v23/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v23/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v23/goal.php'));
    });

    Route::group([
        'prefix'     => 'v23/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v23/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v23/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v23/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v23/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V22',
    ], function () {
        require_once(base_path('routes/api/v23/event.php'));
    });

    Route::group([
        'prefix'     => 'v23/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v23/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v23/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v23/counsellor.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v24
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v24',
], function () {
    Route::group([
        'prefix'    => 'v24/auth',
        'as'        => '.auth',
        'namespace' => 'API\V24\Auth',
    ], function () {
        require_once(base_path('routes/api/v24/auth.php'));
    });

    Route::group([
        'prefix'    => 'v24/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v24/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v24/course.php'));
    });

    Route::group([
        'prefix'     => 'v24/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v24/notification.php'));
    });

    Route::group([
        'prefix'     => 'v24/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/feed.php'));
    });

    Route::group([
        'prefix'     => 'v24/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/badge.php'));
    });

    Route::group([
        'prefix'     => 'v24/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/profile.php'));
    });

    Route::group([
        'prefix'     => 'v24/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v24/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v24/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v24/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v24/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/group.php'));
    });

    Route::group([
        'prefix'    => 'v24/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v24/plans.php'));
    });

    Route::group([
        'prefix'     => 'v24/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v24/move.php'));
    });

    Route::group([
        'prefix'     => 'v24/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v24/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v24/common.php'));
    });

    Route::group([
        'prefix'     => 'v24/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v24/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v24/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v24/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v24/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v24/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v24/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v24/moods.php'));
    });

    Route::group([
        'prefix'     => 'v24/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v24/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v24/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v24/eap.php'));
    });

    Route::group([
        'prefix'     => 'v24/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v24/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v24/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v24/goal.php'));
    });

    Route::group([
        'prefix'     => 'v24/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v24/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v24/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v24/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v24/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/event.php'));
    });

    Route::group([
        'prefix'     => 'v24/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V20',
    ], function () {
        require_once(base_path('routes/api/v24/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v24/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v24/counsellor.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v25
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v25',
], function () {
    Route::group([
        'prefix'    => 'v25/auth',
        'as'        => '.auth',
        'namespace' => 'API\V25\Auth',
    ], function () {
        require_once(base_path('routes/api/v25/auth.php'));
    });

    Route::group([
        'prefix'    => 'v25/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v25/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v25/course.php'));
    });

    Route::group([
        'prefix'     => 'v25/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v25/notification.php'));
    });

    Route::group([
        'prefix'     => 'v25/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v25/feed.php'));
    });

    Route::group([
        'prefix'     => 'v25/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v25/badge.php'));
    });

    Route::group([
        'prefix'     => 'v25/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v25/profile.php'));
    });

    Route::group([
        'prefix'     => 'v25/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v25/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v25/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v25/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v25/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v25/group.php'));
    });

    Route::group([
        'prefix'    => 'v25/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v25/plans.php'));
    });

    Route::group([
        'prefix'     => 'v25/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v25/move.php'));
    });

    Route::group([
        'prefix'     => 'v25/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v25/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v25/common.php'));
    });

    Route::group([
        'prefix'     => 'v25/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v25/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v25/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v25/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v25/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v25/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v25/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v25/moods.php'));
    });

    Route::group([
        'prefix'     => 'v25/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v25/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v25/eap.php'));
    });

    Route::group([
        'prefix'     => 'v25/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v25/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v25/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v25/goal.php'));
    });

    Route::group([
        'prefix'     => 'v25/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v25/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v25/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v25/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v25/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/event.php'));
    });

    Route::group([
        'prefix'     => 'v25/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v25/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v25/counsellor.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v26
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v26',
], function () {
    Route::group([
        'prefix'    => 'v26/auth',
        'as'        => '.auth',
        'namespace' => 'API\V26\Auth',
    ], function () {
        require_once(base_path('routes/api/v26/auth.php'));
    });

    Route::group([
        'prefix'    => 'v26/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v26/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v26/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v26/course.php'));
    });

    Route::group([
        'prefix'     => 'v26/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V18',
    ], function () {
        require_once(base_path('routes/api/v26/notification.php'));
    });

    Route::group([
        'prefix'     => 'v26/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/feed.php'));
    });

    Route::group([
        'prefix'     => 'v26/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/badge.php'));
    });

    Route::group([
        'prefix'     => 'v26/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v26/profile.php'));
    });

    Route::group([
        'prefix'     => 'v26/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v26/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v26/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v26/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v26/group.php'));
    });

    Route::group([
        'prefix'    => 'v26/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v26/plans.php'));
    });

    Route::group([
        'prefix'     => 'v26/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/move.php'));
    });

    Route::group([
        'prefix'     => 'v26/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v26/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/common.php'));
    });

    Route::group([
        'prefix'     => 'v26/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v26/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v26/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v26/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v26/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v26/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v26/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v26/moods.php'));
    });

    Route::group([
        'prefix'     => 'v26/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v26/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v26/eap.php'));
    });

    Route::group([
        'prefix'     => 'v26/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v26/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v26/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v26/goal.php'));
    });

    Route::group([
        'prefix'     => 'v26/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v26/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v26/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v26/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v26/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/event.php'));
    });

    Route::group([
        'prefix'     => 'v26/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v26/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v26/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v26/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v26/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v26/contactus.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v27
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v27',
], function () {
    Route::group([
        'prefix'    => 'v27/auth',
        'as'        => '.auth',
        'namespace' => 'API\V27\Auth',
    ], function () {
        require_once(base_path('routes/api/v27/auth.php'));
    });

    Route::group([
        'prefix'    => 'v27/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v27/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v27/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v27/course.php'));
    });

    Route::group([
        'prefix'     => 'v27/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/notification.php'));
    });

    Route::group([
        'prefix'     => 'v27/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/feed.php'));
    });

    Route::group([
        'prefix'     => 'v27/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v27/badge.php'));
    });

    Route::group([
        'prefix'     => 'v27/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V24',
    ], function () {
        require_once(base_path('routes/api/v27/profile.php'));
    });

    Route::group([
        'prefix'     => 'v27/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v27/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v27/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v27/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v27/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/group.php'));
    });

    Route::group([
        'prefix'    => 'v27/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v27/plans.php'));
    });

    Route::group([
        'prefix'     => 'v27/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/move.php'));
    });

    Route::group([
        'prefix'     => 'v27/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v27/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v27/common.php'));
    });

    Route::group([
        'prefix'     => 'v27/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v27/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v27/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v27/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v27/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v27/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v27/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v27/moods.php'));
    });

    Route::group([
        'prefix'     => 'v27/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v27/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v27/eap.php'));
    });

    Route::group([
        'prefix'     => 'v27/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v27/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v27/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v27/goal.php'));
    });

    Route::group([
        'prefix'     => 'v27/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v27/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v27/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v27/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v27/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v27/event.php'));
    });

    Route::group([
        'prefix'     => 'v27/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v27/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v27/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v27/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v27/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v27/contactus.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v28
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v28',
], function () {
    Route::group([
        'prefix'    => 'v28/auth',
        'as'        => '.auth',
        'namespace' => 'API\V28\Auth',
    ], function () {
        require_once(base_path('routes/api/v28/auth.php'));
    });

    Route::group([
        'prefix'    => 'v28/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v28/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v28/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v28/course.php'));
    });

    Route::group([
        'prefix'     => 'v28/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/notification.php'));
    });

    Route::group([
        'prefix'     => 'v28/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/feed.php'));
    });

    Route::group([
        'prefix'     => 'v28/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v28/badge.php'));
    });

    Route::group([
        'prefix'     => 'v28/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V28',
    ], function () {
        require_once(base_path('routes/api/v28/profile.php'));
    });

    Route::group([
        'prefix'     => 'v28/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v28/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v28/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v28/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v28/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/group.php'));
    });

    Route::group([
        'prefix'    => 'v28/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v28/plans.php'));
    });

    Route::group([
        'prefix'     => 'v28/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/move.php'));
    });

    Route::group([
        'prefix'     => 'v28/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v28/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v28/common.php'));
    });

    Route::group([
        'prefix'     => 'v28/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v28/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v28/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v28/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v28/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v28/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v28/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v28/moods.php'));
    });

    Route::group([
        'prefix'     => 'v28/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v28/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v28/eap.php'));
    });

    Route::group([
        'prefix'     => 'v28/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v28/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v28/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v28/goal.php'));
    });

    Route::group([
        'prefix'     => 'v28/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v28/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v28/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v28/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v28/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V28',
    ], function () {
        require_once(base_path('routes/api/v28/event.php'));
    });

    Route::group([
        'prefix'     => 'v28/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v28/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v28/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v28/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v28/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v28/contactus.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v29
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v29',
], function () {
    Route::group([
        'prefix'    => 'v29/auth',
        'as'        => '.auth',
        'namespace' => 'API\V29\Auth',
    ], function () {
        require_once(base_path('routes/api/v29/auth.php'));
    });

    Route::group([
        'prefix'    => 'v29/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v29/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v29/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v29/course.php'));
    });

    Route::group([
        'prefix'     => 'v29/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v29/notification.php'));
    });

    Route::group([
        'prefix'     => 'v29/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V29',
    ], function () {
        require_once(base_path('routes/api/v29/feed.php'));
    });

    Route::group([
        'prefix'     => 'v29/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v29/badge.php'));
    });

    Route::group([
        'prefix'     => 'v29/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V28',
    ], function () {
        require_once(base_path('routes/api/v29/profile.php'));
    });

    Route::group([
        'prefix'     => 'v29/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v29/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v29/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V23',
    ], function () {
        require_once(base_path('routes/api/v29/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v29/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v29/group.php'));
    });

    Route::group([
        'prefix'    => 'v29/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v29/plans.php'));
    });

    Route::group([
        'prefix'     => 'v29/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v29/move.php'));
    });

    Route::group([
        'prefix'     => 'v29/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V29',
    ], function () {
        require_once(base_path('routes/api/v29/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v29/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v29/common.php'));
    });

    Route::group([
        'prefix'     => 'v29/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v29/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v29/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v29/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v29/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v29/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v29/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v29/moods.php'));
    });

    Route::group([
        'prefix'     => 'v29/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v29/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v29/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v29/eap.php'));
    });

    Route::group([
        'prefix'     => 'v29/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V21',
    ], function () {
        require_once(base_path('routes/api/v29/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v29/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v29/goal.php'));
    });

    Route::group([
        'prefix'     => 'v29/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V9',
    ], function () {
        require_once(base_path('routes/api/v29/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v29/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V14',
    ], function () {
        require_once(base_path('routes/api/v29/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v29/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V28',
    ], function () {
        require_once(base_path('routes/api/v29/event.php'));
    });

    Route::group([
        'prefix'     => 'v29/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v29/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v29/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v29/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v29/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v29/contactus.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v30
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v30',
], function () {
    Route::group([
        'prefix'    => 'v30/auth',
        'as'        => '.auth',
        'namespace' => 'API\V30\Auth',
    ], function () {
        require_once(base_path('routes/api/v30/auth.php'));
    });

    Route::group([
        'prefix'    => 'v30/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v30/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v30/course.php'));
    });

    Route::group([
        'prefix'     => 'v30/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v30/notification.php'));
    });

    Route::group([
        'prefix'     => 'v30/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/feed.php'));
    });

    Route::group([
        'prefix'     => 'v30/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v30/badge.php'));
    });

    Route::group([
        'prefix'     => 'v30/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/profile.php'));
    });

    Route::group([
        'prefix'     => 'v30/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v30/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v30/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v30/group.php'));
    });

    Route::group([
        'prefix'    => 'v30/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v30/plans.php'));
    });

    Route::group([
        'prefix'     => 'v30/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v30/move.php'));
    });

    Route::group([
        'prefix'     => 'v30/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v30/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/common.php'));
    });

    Route::group([
        'prefix'     => 'v30/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v30/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v30/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v30/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v30/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v30/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v30/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v30/moods.php'));
    });

    Route::group([
        'prefix'     => 'v30/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v30/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v30/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/eap.php'));
    });

    Route::group([
        'prefix'     => 'v30/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v30/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v30/goal.php'));
    });

    Route::group([
        'prefix'     => 'v30/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v30/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v30/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/event.php'));
    });

    Route::group([
        'prefix'     => 'v30/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v30/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v30/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v30/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v30/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v30/contactus.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v31
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v31',
], function () {
    Route::group([
        'prefix'    => 'v31/auth',
        'as'        => '.auth',
        'namespace' => 'API\V31\Auth',
    ], function () {
        require_once(base_path('routes/api/v31/auth.php'));
    });

    Route::group([
        'prefix'    => 'v31/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v31/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v31/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v31/course.php'));
    });

    Route::group([
        'prefix'     => 'v31/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v31/notification.php'));
    });

    Route::group([
        'prefix'     => 'v31/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/feed.php'));
    });

    Route::group([
        'prefix'     => 'v31/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v31/badge.php'));
    });

    Route::group([
        'prefix'     => 'v31/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v31/profile.php'));
    });

    Route::group([
        'prefix'     => 'v31/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v31/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v31/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v31/group.php'));
    });

    Route::group([
        'prefix'    => 'v31/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v31/plans.php'));
    });

    Route::group([
        'prefix'     => 'v31/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v31/move.php'));
    });

    Route::group([
        'prefix'     => 'v31/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v31/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/common.php'));
    });

    Route::group([
        'prefix'     => 'v31/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v31/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v31/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v31/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v31/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v31/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v31/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v31/moods.php'));
    });

    Route::group([
        'prefix'     => 'v31/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v31/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v31/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/eap.php'));
    });

    Route::group([
        'prefix'     => 'v31/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v31/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v31/goal.php'));
    });

    Route::group([
        'prefix'     => 'v31/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v31/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v31/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v31/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/event.php'));
    });

    Route::group([
        'prefix'     => 'v31/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v31/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v31/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v31/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v31/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v31/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v31/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v31/digitalTherapy.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v32
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v32',
], function () {
    Route::group([
        'prefix'    => 'v32/auth',
        'as'        => '.auth',
        'namespace' => 'API\V32\Auth',
    ], function () {
        require_once(base_path('routes/api/v32/auth.php'));
    });

    Route::group([
        'prefix'    => 'v32/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v32/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v32/course.php'));
    });

    Route::group([
        'prefix'     => 'v32/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v32/notification.php'));
    });

    Route::group([
        'prefix'     => 'v32/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/feed.php'));
    });

    Route::group([
        'prefix'     => 'v32/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v32/badge.php'));
    });

    Route::group([
        'prefix'     => 'v32/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v32/profile.php'));
    });

    Route::group([
        'prefix'     => 'v32/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v32/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v32/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v32/group.php'));
    });

    Route::group([
        'prefix'    => 'v32/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v32/plans.php'));
    });

    Route::group([
        'prefix'     => 'v32/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v32/move.php'));
    });

    Route::group([
        'prefix'     => 'v32/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v32/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v32/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v32/common.php'));
    });

    Route::group([
        'prefix'     => 'v32/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v32/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v32/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v32/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v32/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v32/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v32/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V7',
    ], function () {
        require_once(base_path('routes/api/v32/moods.php'));
    });

    Route::group([
        'prefix'     => 'v32/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v32/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v32/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v32/eap.php'));
    });

    Route::group([
        'prefix'     => 'v32/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v32/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v32/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v32/goal.php'));
    });

    Route::group([
        'prefix'     => 'v32/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v32/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v32/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v32/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v32/event.php'));
    });

    Route::group([
        'prefix'     => 'v32/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v32/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v32/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v32/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v32/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v32/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v32/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V32',
    ], function () {
        require_once(base_path('routes/api/v32/digitalTherapy.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v33
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v33',
], function () {
    Route::group([
        'prefix'    => 'v33/auth',
        'as'        => '.auth',
        'namespace' => 'API\V33\Auth',
    ], function () {
        require_once(base_path('routes/api/v33/auth.php'));
    });

    Route::group([
        'prefix'    => 'v33/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v33/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v33/course.php'));
    });

    Route::group([
        'prefix'     => 'v33/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v33/notification.php'));
    });

    Route::group([
        'prefix'     => 'v33/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/feed.php'));
    });

    Route::group([
        'prefix'     => 'v33/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v33/badge.php'));
    });

    Route::group([
        'prefix'     => 'v33/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v33/profile.php'));
    });

    Route::group([
        'prefix'     => 'v33/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v33/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v33/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/group.php'));
    });

    Route::group([
        'prefix'    => 'v33/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v33/plans.php'));
    });

    Route::group([
        'prefix'     => 'v33/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v33/move.php'));
    });

    Route::group([
        'prefix'     => 'v33/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v33/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/common.php'));
    });

    Route::group([
        'prefix'     => 'v33/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v33/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v33/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v33/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v33/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v33/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v33/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/moods.php'));
    });

    Route::group([
        'prefix'     => 'v33/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v33/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v33/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v33/eap.php'));
    });

    Route::group([
        'prefix'     => 'v33/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v33/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v33/goal.php'));
    });

    Route::group([
        'prefix'     => 'v33/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v33/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v33/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v33/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v33/event.php'));
    });

    Route::group([
        'prefix'     => 'v33/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v33/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v33/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v33/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v33/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v33/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v33/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v33/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v33/consentForm.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v34
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v34',
], function () {
    Route::group([
        'prefix'    => 'v34/auth',
        'as'        => '.auth',
        'namespace' => 'API\V34\Auth',
    ], function () {
        require_once(base_path('routes/api/v34/auth.php'));
    });

    Route::group([
        'prefix'    => 'v34/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v34/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v34/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v34/course.php'));
    });

    Route::group([
        'prefix'     => 'v34/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v34/notification.php'));
    });

    Route::group([
        'prefix'     => 'v34/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/feed.php'));
    });

    Route::group([
        'prefix'     => 'v34/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v34/badge.php'));
    });

    Route::group([
        'prefix'     => 'v34/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/profile.php'));
    });

    Route::group([
        'prefix'     => 'v34/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v34/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v34/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v34/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v34/group.php'));
    });

    Route::group([
        'prefix'    => 'v34/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v34/plans.php'));
    });

    Route::group([
        'prefix'     => 'v34/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v34/move.php'));
    });

    Route::group([
        'prefix'     => 'v34/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v34/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/common.php'));
    });

    Route::group([
        'prefix'     => 'v34/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v34/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v34/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v34/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v34/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v34/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v34/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v34/moods.php'));
    });

    Route::group([
        'prefix'     => 'v34/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v34/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v34/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/eap.php'));
    });

    Route::group([
        'prefix'     => 'v34/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v34/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v34/goal.php'));
    });

    Route::group([
        'prefix'     => 'v34/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v34/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v34/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v34/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V31',
    ], function () {
        require_once(base_path('routes/api/v34/event.php'));
    });

    Route::group([
        'prefix'     => 'v34/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v34/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v34/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v34/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v34/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v34/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v34/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v34/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v34/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v34/consentForm.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v35
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v35',
], function () {
    Route::group([
        'prefix'    => 'v35/auth',
        'as'        => '.auth',
        'namespace' => 'API\V35\Auth',
    ], function () {
        require_once(base_path('routes/api/v35/auth.php'));
    });

    Route::group([
        'prefix'    => 'v35/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v35/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v35/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v35/course.php'));
    });

    Route::group([
        'prefix'     => 'v35/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v35/notification.php'));
    });

    Route::group([
        'prefix'     => 'v35/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/feed.php'));
    });

    Route::group([
        'prefix'     => 'v35/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v35/badge.php'));
    });

    Route::group([
        'prefix'     => 'v35/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/profile.php'));
    });

    Route::group([
        'prefix'     => 'v35/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v35/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v35/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v35/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v35/group.php'));
    });

    Route::group([
        'prefix'    => 'v35/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v35/plans.php'));
    });

    Route::group([
        'prefix'     => 'v35/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v35/move.php'));
    });

    Route::group([
        'prefix'     => 'v35/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v35/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/common.php'));
    });

    Route::group([
        'prefix'     => 'v35/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v35/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v35/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v35/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v35/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v35/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v35/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v35/moods.php'));
    });

    Route::group([
        'prefix'     => 'v35/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v35/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v35/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/eap.php'));
    });

    Route::group([
        'prefix'     => 'v35/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v35/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v35/goal.php'));
    });

    Route::group([
        'prefix'     => 'v35/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v35/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v35/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v35/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v35/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v35/event.php'));
    });

    Route::group([
        'prefix'     => 'v35/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v35/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v35/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v35/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v35/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v35/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v35/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v35/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v35/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v35/consentForm.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v36
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v36',
], function () {
    Route::group([
        'prefix'    => 'v36/auth',
        'as'        => '.auth',
        'namespace' => 'API\V36\Auth',
    ], function () {
        require_once(base_path('routes/api/v36/auth.php'));
    });

    Route::group([
        'prefix'    => 'v36/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v36/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v36/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v36/course.php'));
    });

    Route::group([
        'prefix'     => 'v36/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v36/notification.php'));
    });

    Route::group([
        'prefix'     => 'v36/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v36/feed.php'));
    });

    Route::group([
        'prefix'     => 'v36/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v36/badge.php'));
    });

    Route::group([
        'prefix'     => 'v36/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/profile.php'));
    });

    Route::group([
        'prefix'     => 'v36/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v36/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v36/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v36/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v36/group.php'));
    });

    Route::group([
        'prefix'    => 'v36/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v36/plans.php'));
    });

    Route::group([
        'prefix'     => 'v36/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v36/move.php'));
    });

    Route::group([
        'prefix'     => 'v36/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v36/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v36/common.php'));
    });

    Route::group([
        'prefix'     => 'v36/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v36/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v36/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v36/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v36/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v36/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v36/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v36/moods.php'));
    });

    Route::group([
        'prefix'     => 'v36/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v36/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v36/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/eap.php'));
    });

    Route::group([
        'prefix'     => 'v36/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v36/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v36/goal.php'));
    });

    Route::group([
        'prefix'     => 'v36/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v36/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v36/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v36/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v36/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v36/event.php'));
    });

    Route::group([
        'prefix'     => 'v36/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V25',
    ], function () {
        require_once(base_path('routes/api/v36/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v36/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v36/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v36/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v36/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v36/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v36/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v36/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v36/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v36/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v36/podcast.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v37
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v37',
], function () {
    Route::group([
        'prefix'    => 'v37/auth',
        'as'        => '.auth',
        'namespace' => 'API\V37\Auth',
    ], function () {
        require_once(base_path('routes/api/v37/auth.php'));
    });

    Route::group([
        'prefix'    => 'v37/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v37/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v37/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v37/course.php'));
    });

    Route::group([
        'prefix'     => 'v37/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v37/notification.php'));
    });

    Route::group([
        'prefix'     => 'v37/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v37/feed.php'));
    });

    Route::group([
        'prefix'     => 'v37/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v37/badge.php'));
    });

    Route::group([
        'prefix'     => 'v37/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v37/profile.php'));
    });

    Route::group([
        'prefix'     => 'v37/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v37/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v37/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v37/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v37/group.php'));
    });

    Route::group([
        'prefix'    => 'v37/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v37/plans.php'));
    });

    Route::group([
        'prefix'     => 'v37/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v37/move.php'));
    });

    Route::group([
        'prefix'     => 'v37/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v37/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v37/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v37/common.php'));
    });

    Route::group([
        'prefix'     => 'v37/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v37/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v37/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v37/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v37/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v37/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v37/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v37/moods.php'));
    });

    Route::group([
        'prefix'     => 'v37/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v37/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v37/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v37/eap.php'));
    });

    Route::group([
        'prefix'     => 'v37/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v37/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v37/goal.php'));
    });

    Route::group([
        'prefix'     => 'v37/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v37/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v37/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v37/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v37/event.php'));
    });

    Route::group([
        'prefix'     => 'v37/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v37/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v37/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v37/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v37/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v37/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v37/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v37/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v37/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V36',
    ], function () {
        require_once(base_path('routes/api/v37/podcast.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v38
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v38',
], function () {
    Route::group([
        'prefix'    => 'v38/auth',
        'as'        => '.auth',
        'namespace' => 'API\V38\Auth',
    ], function () {
        require_once(base_path('routes/api/v38/auth.php'));
    });

    Route::group([
        'prefix'    => 'v38/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v38/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v38/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v38/course.php'));
    });

    Route::group([
        'prefix'     => 'v38/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v38/notification.php'));
    });

    Route::group([
        'prefix'     => 'v38/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/feed.php'));
    });

    Route::group([
        'prefix'     => 'v38/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v38/badge.php'));
    });

    Route::group([
        'prefix'     => 'v38/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v38/profile.php'));
    });

    Route::group([
        'prefix'     => 'v38/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v38/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v38/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v38/group.php'));
    });

    Route::group([
        'prefix'    => 'v38/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v38/plans.php'));
    });

    Route::group([
        'prefix'     => 'v38/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/move.php'));
    });

    Route::group([
        'prefix'     => 'v38/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v38/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v38/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/common.php'));
    });

    Route::group([
        'prefix'     => 'v38/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v38/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v38/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v38/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v38/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v38/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v38/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v38/moods.php'));
    });

    Route::group([
        'prefix'     => 'v38/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v38/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v38/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v38/eap.php'));
    });

    Route::group([
        'prefix'     => 'v38/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v38/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v38/goal.php'));
    });

    Route::group([
        'prefix'     => 'v38/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v38/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v38/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v38/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v38/event.php'));
    });

    Route::group([
        'prefix'     => 'v38/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v38/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v38/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v38/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v38/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v38/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v38/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v38/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v38/podcast.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v39
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v39',
], function () {
    Route::group([
        'prefix'    => 'v39/auth',
        'as'        => '.auth',
        'namespace' => 'API\V39\Auth',
    ], function () {
        require_once(base_path('routes/api/v39/auth.php'));
    });

    Route::group([
        'prefix'    => 'v39/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v39/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v39/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v39/course.php'));
    });

    Route::group([
        'prefix'     => 'v39/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v39/notification.php'));
    });

    Route::group([
        'prefix'     => 'v39/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/feed.php'));
    });

    Route::group([
        'prefix'     => 'v39/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v39/badge.php'));
    });

    Route::group([
        'prefix'     => 'v39/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v39/profile.php'));
    });

    Route::group([
        'prefix'     => 'v39/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v39/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v39/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v39/group.php'));
    });

    Route::group([
        'prefix'    => 'v39/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v39/plans.php'));
    });

    Route::group([
        'prefix'     => 'v39/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/move.php'));
    });

    Route::group([
        'prefix'     => 'v39/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v39/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v39/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/common.php'));
    });

    Route::group([
        'prefix'     => 'v39/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v39/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v39/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v39/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v39/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v39/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v39/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v39/moods.php'));
    });

    Route::group([
        'prefix'     => 'v39/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v39/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v39/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v39/eap.php'));
    });

    Route::group([
        'prefix'     => 'v39/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v39/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v39/goal.php'));
    });

    Route::group([
        'prefix'     => 'v39/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v39/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v39/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v39/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v39/event.php'));
    });

    Route::group([
        'prefix'     => 'v39/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v39/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v39/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v39/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v39/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v39/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v39/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v39/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v39/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v39/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v39/podcast.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v40
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v40',
], function () {
    Route::group([
        'prefix'    => 'v40/auth',
        'as'        => '.auth',
        'namespace' => 'API\V40\Auth',
    ], function () {
        require_once(base_path('routes/api/v40/auth.php'));
    });

    Route::group([
        'prefix'    => 'v40/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v40/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v40/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v40/course.php'));
    });

    Route::group([
        'prefix'     => 'v40/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v40/notification.php'));
    });

    Route::group([
        'prefix'     => 'v40/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/feed.php'));
    });

    Route::group([
        'prefix'     => 'v40/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v40/badge.php'));
    });

    Route::group([
        'prefix'     => 'v40/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v40/profile.php'));
    });

    Route::group([
        'prefix'     => 'v40/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v40/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v40/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v40/group.php'));
    });

    Route::group([
        'prefix'    => 'v40/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v40/plans.php'));
    });

    Route::group([
        'prefix'     => 'v40/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v40/move.php'));
    });

    Route::group([
        'prefix'     => 'v40/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v40/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v40/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v40/common.php'));
    });

    Route::group([
        'prefix'     => 'v40/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v40/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v40/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v40/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v40/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v40/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v40/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v40/moods.php'));
    });

    Route::group([
        'prefix'     => 'v40/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v40/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v40/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v40/eap.php'));
    });

    Route::group([
        'prefix'     => 'v40/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v40/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v40/goal.php'));
    });

    Route::group([
        'prefix'     => 'v40/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v40/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v40/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v40/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v40/event.php'));
    });

    Route::group([
        'prefix'     => 'v40/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v40/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v40/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v40/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v40/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v40/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v40/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v40/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v40/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v40/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v40/podcast.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v41
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v41',
], function () {
    Route::group([
        'prefix'    => 'v41/auth',
        'as'        => '.auth',
        'namespace' => 'API\V41\Auth',
    ], function () {
        require_once(base_path('routes/api/v41/auth.php'));
    });

    Route::group([
        'prefix'    => 'v41/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v41/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v41/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v41/course.php'));
    });

    Route::group([
        'prefix'     => 'v41/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v41/notification.php'));
    });

    Route::group([
        'prefix'     => 'v41/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/feed.php'));
    });

    Route::group([
        'prefix'     => 'v41/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v41/badge.php'));
    });

    Route::group([
        'prefix'     => 'v41/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v41/profile.php'));
    });

    Route::group([
        'prefix'     => 'v41/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v41/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v41/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v41/group.php'));
    });

    Route::group([
        'prefix'    => 'v41/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v41/plans.php'));
    });

    Route::group([
        'prefix'     => 'v41/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v41/move.php'));
    });

    Route::group([
        'prefix'     => 'v41/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v41/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v41/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v41/common.php'));
    });

    Route::group([
        'prefix'     => 'v41/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v41/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v41/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v41/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v41/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v41/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v41/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v41/moods.php'));
    });

    Route::group([
        'prefix'     => 'v41/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v41/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v41/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v41/eap.php'));
    });

    Route::group([
        'prefix'     => 'v41/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v41/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v41/goal.php'));
    });

    Route::group([
        'prefix'     => 'v41/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v41/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v41/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v41/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v41/event.php'));
    });

    Route::group([
        'prefix'     => 'v41/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v41/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v41/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v41/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v41/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v41/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v41/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v41/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v41/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v41/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v41/podcast.php'));
    });

    Route::group([
        'prefix'     => 'v41/shorts',
        'as'         => '.shorts',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v41/shorts.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v42
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v42',
], function () {
    Route::group([
        'prefix'    => 'v42/auth',
        'as'        => '.auth',
        'namespace' => 'API\V42\Auth',
    ], function () {
        require_once(base_path('routes/api/v42/auth.php'));
    });

    Route::group([
        'prefix'    => 'v42/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v42/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v42/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v42/course.php'));
    });

    Route::group([
        'prefix'     => 'v42/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v42/notification.php'));
    });

    Route::group([
        'prefix'     => 'v42/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/feed.php'));
    });

    Route::group([
        'prefix'     => 'v42/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v42/badge.php'));
    });

    Route::group([
        'prefix'     => 'v42/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v42/profile.php'));
    });

    Route::group([
        'prefix'     => 'v42/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v42/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v42/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v42/group.php'));
    });

    Route::group([
        'prefix'    => 'v42/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v42/plans.php'));
    });

    Route::group([
        'prefix'     => 'v42/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v42/move.php'));
    });

    Route::group([
        'prefix'     => 'v42/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v42/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v42/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V42',
    ], function () {
        require_once(base_path('routes/api/v42/common.php'));
    });

    Route::group([
        'prefix'     => 'v42/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v42/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v42/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v42/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v42/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v42/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v42/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v42/moods.php'));
    });

    Route::group([
        'prefix'     => 'v42/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v42/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v42/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v42/eap.php'));
    });

    Route::group([
        'prefix'     => 'v42/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v42/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v42/goal.php'));
    });

    Route::group([
        'prefix'     => 'v42/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v42/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v42/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v42/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v42/event.php'));
    });

    Route::group([
        'prefix'     => 'v42/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v42/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v42/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v42/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v42/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v42/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v42/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V39',
    ], function () {
        require_once(base_path('routes/api/v42/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v42/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v42/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v42/podcast.php'));
    });

    Route::group([
        'prefix'     => 'v42/shorts',
        'as'         => '.shorts',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v42/shorts.php'));
    });
});

/*
|--------------------------------------------------------------------------
| Routes v43
|--------------------------------------------------------------------------
|
 */
Route::group([
    'as' => '.v43',
], function () {
    Route::group([
        'prefix'    => 'v43/auth',
        'as'        => '.auth',
        'namespace' => 'API\V43\Auth',
    ], function () {
        require_once(base_path('routes/api/v43/auth.php'));
    });

    Route::group([
        'prefix'    => 'v43/onboard',
        'as'        => '.onboard',
        'namespace' => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v43/onboard.php'));
    });

    Route::group([
        'prefix'     => 'v43/course',
        'as'         => '.course',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V4',
    ], function () {
        require_once(base_path('routes/api/v43/course.php'));
    });

    Route::group([
        'prefix'     => 'v43/notification',
        'as'         => '.notification',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v43/notification.php'));
    });

    Route::group([
        'prefix'     => 'v43/feed',
        'as'         => '.feed',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/feed.php'));
    });

    Route::group([
        'prefix'     => 'v43/badge',
        'as'         => '.badge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V26',
    ], function () {
        require_once(base_path('routes/api/v43/badge.php'));
    });

    Route::group([
        'prefix'     => 'v43/profile',
        'as'         => '.profile',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v43/profile.php'));
    });

    Route::group([
        'prefix'     => 'v43/recipe',
        'as'         => '.recipe',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/recipe.php'));
    });

    Route::group([
        'prefix'     => 'v43/meditation',
        'as'         => '.meditation',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/meditation.php'));
    });

    Route::group([
        'prefix'     => 'v43/group',
        'as'         => '.group',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V35',
    ], function () {
        require_once(base_path('routes/api/v43/group.php'));
    });

    Route::group([
        'prefix'    => 'v43/plans',
        'as'        => '.plans',
        'namespace' => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v43/plans.php'));
    });

    Route::group([
        'prefix'     => 'v43/move',
        'as'         => '.move',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v43/move.php'));
    });

    Route::group([
        'prefix'     => 'v43/challenge',
        'as'         => '.challenge',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v43/challenge.php'));
    });

    Route::group([
        'prefix'     => 'v43/common',
        'as'         => '.common',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V43',
    ], function () {
        require_once(base_path('routes/api/v43/common.php'));
    });

    Route::group([
        'prefix'     => 'v43/nourish',
        'as'         => '.nourish',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V1',
    ], function () {
        require_once(base_path('routes/api/v43/nourish.php'));
    });

    Route::group([
        'prefix'     => 'v43/inspire',
        'as'         => '.inspire',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V6',
    ], function () {
        require_once(base_path('routes/api/v43/inspire.php'));
    });

    Route::group([
        'prefix'     => 'v43/healthscore',
        'as'         => '.healthscore',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v43/healthscore.php'));
    });

    Route::group([
        'prefix'     => 'v43/moods',
        'as'         => '.moods',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V33',
    ], function () {
        require_once(base_path('routes/api/v43/moods.php'));
    });

    Route::group([
        'prefix'     => 'v43/challenges',
        'as'         => '.challenges',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V27',
    ], function () {
        require_once(base_path('routes/api/v43/personalChallenge.php'));
    });

    Route::group([
        'prefix'     => 'v43/eap',
        'as'         => '.eap',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V34',
    ], function () {
        require_once(base_path('routes/api/v43/eap.php'));
    });

    Route::group([
        'prefix'     => 'v43/masterclass',
        'as'         => '.masterclass',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/masterclass.php'));
    });

    Route::group([
        'prefix'     => 'v43/goal',
        'as'         => '.goal',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V8',
    ], function () {
        require_once(base_path('routes/api/v43/goal.php'));
    });

    Route::group([
        'prefix'     => 'v43/challenge-images',
        'as'         => '.challengeImages',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v43/challengeImages.php'));
    });

    Route::group([
        'prefix'     => 'v43/webinar',
        'as'         => '.webinar',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/webinar.php'));
    });

    Route::group([
        'prefix'     => 'v43/event',
        'as'         => '.event',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V40',
    ], function () {
        require_once(base_path('routes/api/v43/event.php'));
    });

    Route::group([
        'prefix'     => 'v43/common/portal',
        'as'         => '.common/portal',
        'middleware' => ['auth:portal'],
        'namespace'  => 'API\V43',
    ], function () {
        require_once(base_path('routes/api/v43/portalhome.php'));
    });

    Route::group([
        'prefix'     => 'v43/counsellor',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V30',
    ], function () {
        require_once(base_path('routes/api/v43/counsellor.php'));
    });

    Route::group([
        'prefix'     => 'v43/contactus',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V37',
    ], function () {
        require_once(base_path('routes/api/v43/contactus.php'));
    });

    Route::group([
        'prefix'     => 'v43/digitalTherapy',
        'as'         => '.digitalTherapy',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V43',
    ], function () {
        require_once(base_path('routes/api/v43/digitalTherapy.php'));
    });

    Route::group([
        'prefix'     => 'v43/consentForm',
        'as'         => '.consentForm',
        'middleware' => ['auth:api'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/consentForm.php'));
    });

    Route::group([
        'prefix'     => 'v43/podcast',
        'as'         => '.podcast',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V38',
    ], function () {
        require_once(base_path('routes/api/v43/podcast.php'));
    });

    Route::group([
        'prefix'     => 'v43/shorts',
        'as'         => '.shorts',
        'middleware' => ['auth:api', 'auth:portal'],
        'namespace'  => 'API\V41',
    ], function () {
        require_once(base_path('routes/api/v43/shorts.php'));
    });
});