<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $appEnvironment  = app()->environment();

        switch ($appEnvironment) {
            case 'local':
                $allowedOrigins = "*";
            case 'dev':
                $allowedOrigins = "https://*.zevowork.com http://localhost";
                break;
            case 'qa':
                $allowedOrigins = "https://*.zevowork.com";
                break;
            case 'uat':
                $allowedOrigins = "https://*.zevowork.com";
                break;
            case 'production':
                $allowedOrigins = "https://*.zevowork.com";
                break;
            default:
                $allowedOrigins = "https://*.zevowork.com https://*.zevotherapy.com";
                break;
        }

        return $next($request)
                ->header('Access-Control-Allow-Origin', $allowedOrigins)
                ->header('Access-Control-Allow-Methods', 'PUT, GET, POST, OPTIONS, DELETE, PATCH');
    }
}