<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use App\Models\UserDeviceHistory;
use Closure;

/**
 * Class CheckUserTracker
 * @package App\Http\Middleware
 */
class CheckUserTracker
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
        if ($this->guard()->check()) {
            /** @var User $user */
            $user = $this->guard()->user();

            if ($user && $user->is_blocked) {
                $user->token()->revoke();
                return $this->unauthorizedResponse(\trans('api_labels.auth.inactive'));
            }

            $role           = getUserRole($user);
            $companyDetails = $user->company->first();
            $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));

            if ($role->group != 'zevo') {
                if ($companyDetails->status == 0) {
                    return $this->unauthorizedResponse(\trans('auth.company_status'));
                }
            }

            // Check condition for access portal and app when company login
            if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$companyDetails->allow_portal ) {
                $userData = [];
                // if company don't have portal access
                return $this->invalidResponse($userData, \trans('api_labels.auth.not_access_portal'), 401);
            } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$companyDetails->allow_app ) {
                $userData = [];
                return $this->invalidResponse($userData, \trans('api_labels.auth.not_access_app'), 401);
            }

            // Update user tracker if needed
            if ($xDeviceOs != config()->get('zevolifesettings.PORTAL')) {
                if ($request->headers->has('X-Device-Id') && $request->headers->has('X-User-Tracker')) {
                    $device = $user->devices()->first();

                    if (empty($device)) {
                        $user->token()->revoke();
                        return $this->unauthorizedResponse(\trans('api_labels.auth.device_not_found'));
                    }

                    if (!empty($device) && $device->device_id !== $request->headers->get('X-Device-Id')) {
                        $user->token()->revoke();
                        return $this->unauthorizedResponse(\trans('api_labels.auth.multilogin_device'));
                    }

                    $tracker = $request->headers->get('X-User-Tracker');

                    if (!empty($device) && $device->tracker !== $tracker) {
                        $device->update(['tracker' => $tracker]);
                    }

                    // Maintain Tracker History
                    if (!empty($device)) {
                        $checkLastDevice = UserDeviceHistory::select('id', 'tracker')->where('user_id', $user->id)->orderBy('id', 'DESC')->first();

                        if ((empty($checkLastDevice) || (!empty($checkLastDevice) && $checkLastDevice->tracker !== $tracker)) && strtolower($tracker) != 'default') {
                            $userDeviceHistory = new UserDeviceHistory;

                            $userDeviceHistory->user_id = $user->id;
                            $userDeviceHistory->tracker = $tracker;

                            $userDeviceHistory->save();
                        }
                    }

                    $deviceToken = $request->headers->get('X-Push-Token');
                    if (!empty($device) && $device->device_token !== $deviceToken) {
                        $device->update(['device_token' => $deviceToken]);
                    }

                    $userToken = $request->headers->get('X-User-Tracker-Token');
                    if (!empty($device) && $device->user_token !== $userToken) {
                        $device->update(['user_token' => $userToken]);
                    }
                }
            }
        }

        return $next($request);
    }
}
