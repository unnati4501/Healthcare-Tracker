<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Models\CompanyBranding;
use Closure;

/**
 * Class CheckDomainBranding
 * @package App\Http\Middleware
 */
class CheckDomainBranding
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
        $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
        $host              = $request->getHost();
        // $host              = "eringreer.zevolife.local";
        $serverNameAsArray = explode('.', $host);

        if (!defined('ZC_SERVER')) {
            define('ZC_SERVER', $host);
        }

        if (in_array($serverNameAsArray[0], $platformDomain) || app()->environment() == "local") {
            if (!defined('ZC_SERVER_SUBDOMAIN')) {
                define('ZC_SERVER_SUBDOMAIN', $serverNameAsArray[0]);
            }
        } else {
            $allowedDomainBrandingList = CompanyBranding::where("status", true)->pluck("sub_domain")->toArray();
            if (in_array($serverNameAsArray[0], $allowedDomainBrandingList)) {
                if (!defined('ZC_SERVER_SUBDOMAIN')) {
                    define('ZC_SERVER_SUBDOMAIN', $serverNameAsArray[0]);
                }
            } else {
                abort(404);
            }
        }
        return $next($request);
    }
}
