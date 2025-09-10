<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\CompanyBranding;
use App\Models\User;
use Closure;

/**
 * Class CheckPortalDomain
 * @package App\Http\Middleware
 */
class CheckPortalDomain
{
    use ProvidesAuthGuardTrait, ServesApiTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            if ($this->guard()->check()) {
                /** @var User $user */
                $user = $this->guard()->user();
            } else {
                $email = $request->input('email');
                $user  = User::where('email', $email)->get()->first();
            }

            if ($user) {
                $role = getUserRole($user);

                if ($role->slug == 'user') {
                    $companyDetails = $user->company->first();
                    $companyId      = ($companyDetails->parent_id != null) ? $companyDetails->parent_id : $companyDetails->id;

                    $companyBranding = CompanyBranding::where('company_id', $companyId)->first();

                    if ($companyBranding && ($companyDetails->is_reseller || $companyDetails->parent_id != null)) {
                        $origin       = strtolower($request->header('origin', ""));
                        $hostURL      = !empty($origin) ? parse_url($origin)['host'] : "";
                        $portalDomain = $companyBranding->portal_domain;

                        if ($hostURL !== $portalDomain) {
                            $userData = [];
                            return $this->invalidResponse($userData, \trans('api_labels.auth.not_same_domain'));
                        }
                    }
                }
            }
        }
        return $next($request);
    }
}
