<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V12\Auth;

use App\Http\Collections\V12\AppSettingCollection;
use App\Http\Controllers\API\V11\Auth\LoginController as v11LoginController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V12\UserResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSetting;
use App\Models\AppVersion;
use App\Models\Challenge;
use App\Models\CompanyWiseLabelString;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class LoginController extends v11LoginController
{
    use AuthenticatesUsers, ServesApiTrait, ProvidesAuthGuardTrait {
        ProvidesAuthGuardTrait::guard insteadof AuthenticatesUsers;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Spatie\Fractalistic\Exceptions\InvalidTransformation
     * @throws \Spatie\Fractalistic\Exceptions\NoTransformerSpecified
     */
    public function login(LoginRequest $request)
    {
        try {
            if (empty($request->get('socialId'))) {
                $this->validateLogin($request);
            }

            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                $this->sendLockoutResponse($request);
            }

            if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                auth()->shouldUse(config()->get('zevolifesettings.PORTAL'));
            }

            if (false !== ($token = $this->attemptLogin($request))) {
                return $this->sendLoginResponse($request, $token);
            }

            $this->incrementLoginAttempts($request);
            $this->sendFailedLoginResponse();
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  Request $request
     * @return bool|string
     */
    public function attemptLogin(Request $request)
    {
        if (!empty($request->get('email')) && !empty($request->get('socialId'))) {
            $userData = User::where('email', $request->get('email'))->first();
            $userData->update([
                'social_id'   => $request->get('socialId'),
                'social_type' => $request->get('socialType') ?? 1,
            ]);
            $token = $this->guard()->fromUser($userData);
            $this->guard()->setToken($token)->setUser($userData);
            return $token;
        } else {
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $this->guard()->setTTL(120);
            }
            return $this->guard()->attempt($this->credentials($request));
        }
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  Request $request
     * @return void
     * @throws ValidationException
     */
    public function sendLockoutResponse(Request $request): void
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => \trans('api_labels.auth.throttle', ['seconds' => $seconds]),
        ])->status(422);
    }

    /**
     * Get the failed login response instance.
     *
     * @return void
     * @throws ValidationException
     */
    public function sendFailedLoginResponse(): void
    {
        throw ValidationException::withMessages([
            'login' => \trans('api_labels.auth.failed'),
        ])->status(422);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  Request $request
     * @param string   $token
     *
     * @return JsonResponse
     * @throws \Spatie\Fractalistic\Exceptions\InvalidTransformation
     * @throws \Spatie\Fractalistic\Exceptions\NoTransformerSpecified
     */
    public function sendLoginResponse(Request $request, string $token): JsonResponse
    {
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user(), $token);
    }

    /**
     * The user has been authenticated.
     *
     * @param  Request                  $request
     * @param  Authenticatable|User     $user
     * @param                           $token
     *
     * @return JsonResponse
     * @throws \Spatie\Fractalistic\Exceptions\InvalidTransformation
     * @throws \Spatie\Fractalistic\Exceptions\NoTransformerSpecified
     */
    public function authenticated(Request $request, $user, $token): JsonResponse
    {
        if ($user->is_blocked) {
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

        $userData              = [];
        $userData['firstName'] = $user->first_name;
        $userData['lastName']  = $user->last_name;
        $userData['email']     = $user->email;
        $userData['gender']    = ($user->profile) ? $user->profile->gender : 'male';

        if ($companyDetails) {
            // Check condition for access portal and app when company login
            if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$companyDetails->allow_portal) {
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $role->slug != 'user' && !$user->can_access_portal) {
                $this->guard()->factory()->invalidate($token);
                // if company has reseller company and he don't have portal access
                if ($companyDetails->is_reseller || $companyDetails->parent_id != null) {
                    return $this->invalidResponse($userData, \trans('api_labels.auth.portal_access'), 307);
                }
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
            } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$companyDetails->allow_app) {
                $this->guard()->factory()->invalidate($token);
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
            }
        } else {
            $this->guard()->factory()->invalidate($token);
            $message = ($xDeviceOs != config('zevolifesettings.PORTAL')) ? \trans('api_labels.auth.not_access_app') : \trans('api_labels.auth.not_access_portal');
            return $this->notaccessFailedResponse($message);
        }

        if ($xDeviceOs != config('zevolifesettings.PORTAL') && $role->slug != 'user' && !$user->can_access_app) {
            $userData              = [];
            $userData['firstName'] = $user->first_name;
            $userData['lastName']  = $user->last_name;
            $userData['email']     = $user->email;
            $userData['gender']    = ($user->profile) ? $user->profile->gender : 'male';
            $this->guard()->factory()->invalidate($token);
            return $this->invalidResponse($userData, \trans('api_labels.auth.device_access'), 307);
        }

        if ($user->teams->isEmpty()) {
            $this->guard()->factory()->invalidate($token);
            return $this->unauthorizedResponse(\trans('api_labels.auth.failed'));
        }

        // In case it's a request from mobile device
        if ($request->header('X-Device-Os') != config('zevolifesettings.PORTAL')) {
            if ($request->headers->has('X-Device-Id') && $request->headers->has('X-Device-Os')) {
                $device = $user->devices()->first();
                if (!empty($device)) {
                    if ($device->device_id !== $request->headers->get('X-Device-Id')) {
                        $this->guard()->factory()->invalidate($token);
                        return $this->preConditionsFailedResponse(\trans('api_labels.auth.multilogin'));
                    }

                    if ($device->tracker !== $request->headers->get('X-User-Tracker')) {
                        $device->update(['tracker' => $request->headers->get('X-User-Tracker')]);
                    }
                } else {
                    $device = $user->devices()->create([
                        'device_id'    => $request->headers->get('X-Device-Id'),
                        'device_os'    => $request->headers->get('X-Device-Os'),
                        'tracker'      => $request->headers->get('X-User-Tracker'),
                        'device_token' => $request->headers->get('X-Push-Token'),
                    ]);
                }
            } else {
                $this->guard()->factory()->invalidate($token);
                return $this->unauthorizedResponse(\trans('api_labels.auth.failed'));
            }
        }

        // Update user's notificaion setting table modules entries
        updateNotificationSettingModules($user);

        $appTimezone    = config('app.timezone');
        $userTimeZone   = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $lastActivityAt = Carbon::parse($user->last_activity_at, $appTimezone)->setTimezone($userTimeZone)->todatetimeString();
        $currentTime    = Carbon::now()->timezone($userTimeZone)->todatetimeString();
        $challengeData  = Challenge::select("challenges.id", "challenges.title", "challenges.challenge_type", "challenge_wise_user_log.is_winner")
            ->join("challenge_wise_user_log", "challenge_wise_user_log.challenge_id", "=", "challenges.id")
            ->where("challenges.challenge_type", "!=", "individual")
            ->where("challenge_wise_user_log.user_id", $user->id)
            ->where("finished", true)
            ->whereNotNull("challenge_wise_user_log.finished_at")
            ->where(\DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), ">=", $lastActivityAt)
            ->where(\DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), "<=", $currentTime)
            ->groupBy("challenge_wise_user_log.challenge_id")
            ->get();

        $participatedChallenges = [];
        if ($challengeData->count() > 0) {
            foreach ($challengeData as $value) {
                $participatedChallenges[] = [
                    "id"    => $value->id,
                    "title" => $value->title,
                    "iswin" => (!empty($value->is_winner) && $value->is_winner),
                    "type"  => $value->challenge_type,
                ];
            }
        }

        // Update last login
        $user->update(['last_login_at' => \now(config('app.timezone')), 'last_activity_at' => \now(config('app.timezone'))]);

        $message = "You are logged in successfully.";
        if (!empty($request->input('companyCode'))) {
            $message = "You are registered successfully.";
        }

        $response = $this->successResponse([
            'data' => [
                'accessToken'         => 'Bearer' . $token,
                'user'                => new UserResource($user),
                'completedChallenges' => $participatedChallenges,
            ],
        ], $message);

        $response->header('Authrorization', "Bearer {$token}");

        return $response;
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function logout(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->user();

            $user->update(['last_activity_at' => \now(config('app.timezone'))->format("Y-m-d H:i:s")]);

            // remove devices
            $user->devices()->forceDelete();

            // logout user
            $this->guard()->logout();

            return $this->successResponse([], 'Successfully logged out.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Returns App Settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function appLaunch(Request $request)
    {
        try {
            $forceUpdateCount   = 0;
            $xDeviceOs          = strtolower($request->header('X-Device-Os', ""));
            $lastActivityAt     = "";
            $user               = Auth::guard('api')->user();
            $companyLabelString = [];
            if ($xDeviceOs != config('zevolifesettings.PORTAL')) {
                $appVersion = $request->appVersion;

                if ($appVersion == null) {
                    return $this->invalidResponse([], \trans('api_labels.auth.not_app_version'));
                }

                if ($xDeviceOs == 'android') {
                    $exactVersionRecord = AppVersion::where('andriod_version', $appVersion)->first();

                    if (!empty($exactVersionRecord)) {
                        $forceUpdateCount = AppVersion::where('id', '>', $exactVersionRecord->id)->where('andriod_force_update', true)->count();
                    }
                } elseif ($xDeviceOs == 'ios') {
                    $exactVersionRecord = AppVersion::where('ios_version', $appVersion)->first();

                    if (!empty($exactVersionRecord)) {
                        $forceUpdateCount = AppVersion::where('id', '>', $exactVersionRecord->id)->where('ios_force_update', true)->count();
                    }
                }
            }

            if (!empty($user)) {
                $companyId = $user->company()->first()->id;
                // Update user's notificaion setting table modules entries
                updateNotificationSettingModules($user);

                $lastActivityAt = $user->last_activity_at;
                // Update last activity at to set as an active users
                $user->update(['last_activity_at' => \now(config('app.timezone'))->format("Y-m-d H:i:s")]);

                $companyLabelString = CompanyWiseLabelString::where('company_id', $companyId)->get()->pluck('label_name', 'field_name')->toArray();
            }

            $records = AppSetting::all();

            if (count($companyLabelString) <= 0) {
                $companyLabelString = [
                    'recent_stories'      => config('zevolifesettings.company_label_string.home.recent_stories.default_value'),
                    'lbl_company'         => config('zevolifesettings.company_label_string.home.lbl_company.default_value'),
                    'get_support'         => config('zevolifesettings.company_label_string.support.get_support.default_value'),
                    'employee_assistance' => config('zevolifesettings.company_label_string.support.employee_assistance.default_value'),
                ];
            }

            return $this->successResponse(($records->count() > 0) ? new AppSettingCollection($records, $forceUpdateCount, $xDeviceOs, $lastActivityAt, $companyLabelString) : ['data' => []], 'Data recevied successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
