<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        // \Spatie\CookieConsent\CookieConsentMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\AppSaml::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\PreventBackHistory::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\CheckDomainBranding::class,
        ],

        'api' => [
            'throttle:500,0.01',
            'bindings',
            \App\Http\Middleware\UpdateUserTimezone::class,
            \App\Http\Middleware\CheckUserTracker::class,
            \App\Http\Middleware\TrackApiData::class,
            \App\Http\Middleware\CheckPortalDomain::class,
        ],

        // 'saml' => [
        //     \App\Http\Middleware\EncryptCookies::class,
        //     \Illuminate\Session\Middleware\StartSession::class,
        // ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                     => \App\Http\Middleware\Authenticate::class,
        'auth.basic'               => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'                 => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers'            => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                      => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'                    => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed'                   => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'                 => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'                 => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'localize'                 => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'localizationRedirect'     => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect'    => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        'localeViewPath'           => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        'azure'                    => \App\Http\Middleware\AppAzure::class,
        'saml'                     => \App\Http\Middleware\AppSaml::class,
        'XssSanitization'          => \App\Http\Middleware\XssSanitization::class,
        'cronofyAuthenticate'      => \App\Http\Middleware\CronofyAuthenticate::class,
        'cronofyEventAuthenticate' => \App\Http\Middleware\CronofyEventAuthenticated::class,
        'cors'                     => \App\Http\Middleware\Cors::class,
        'scopes'                   => \Laravel\Passport\Http\Middleware\CheckScopes::class,
        'scope'                    => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\CheckDomainBranding::class,
        \App\Http\Middleware\AppSaml::class,
        \App\Http\Middleware\AppAzure::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
