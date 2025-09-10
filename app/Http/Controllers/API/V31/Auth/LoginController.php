<?php declare (strict_types = 1);

namespace App\Http\Controllers\Api\V31\Auth;

use App\Http\Collections\V31\AppSettingCollection;
use App\Http\Controllers\API\V30\Auth\LoginController as v30LoginController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EmailRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V22\UserAutoFillResource;
use App\Http\Resources\V31\UserResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendSingleCodeEmailJob;
use App\Models\AppSetting;
use App\Models\AppVersion;
use App\Models\Challenge;
use App\Models\CompanyWiseLabelString;
use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class LoginController extends v30LoginController
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
            /*if (empty($request->get('socialId'))) {
            $this->validateLogin($request);
            }*/

            if (!empty($request->get('singleUseCode'))) {
                $this->verifySingleUseCode($request);
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
     * @param  Request $request
     * @return bool|string
     */
    public function attemptLogin(Request $request)
    {
        $userData = User::where('email', $request->get('email'))->first();
        if (!empty($request->get('email')) && !empty($request->get('socialId'))) {
            if (!empty($userData)) {
                $userData->update([
                    'social_id'   => $request->get('socialId'),
                    'social_type' => $request->get('socialType') ?? 1,
                ]);
            }
            $token = $this->guard()->fromUser($userData);
            $this->guard()->setToken($token)->setUser($userData);
            return $token;
        } else {
            if (!empty($userData)) {
                $userData->update([
                    'social_id'   => null,
                    'social_type' => null,
                ]);
            }
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $this->guard()->setTTL(120);
            }
            /*if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
            return $this->guard()->attempt($this->credentials($request));
            } else {*/
            $getUserDetails = User::where('email', $request->get('email'))->first(); //->where('single_use_code', $request->get('singleUseCode'))->first();
            $user           = User::join("user_otp", "user_otp.email", "=", "users.email")
                ->where('user_otp.email', $request->get('email'))
                ->where('user_otp.single_use_code', $request->get('singleUseCode'))
                ->first();
            if ($user) {
                return JWTAuth::fromUser($getUserDetails);
            } else {
                return false;
            }
            /*}*/
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
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        if (empty($request->get('socialId'))) {
            //&& $xDeviceOs != config('zevolifesettings.PORTAL')
            $user = User::select('users.*')->join("user_otp", "user_otp.email", "=", "users.email")
                ->where('user_otp.email', $request->get('email'))
                ->where('user_otp.single_use_code', $request->get('singleUseCode'))
                ->first();
        }
        if ($user->is_blocked) {
            return $this->unauthorizedResponse(\trans('api_labels.auth.inactive'));
        }

        $role           = getUserRole($user);
        $companyDetails = $user->company->first();
        $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));

        if (!empty($role->group) && $role->group != 'zevo') {
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
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $team                    = $user->teams()->select('teams.id', 'teams.name', 'teams.department_id', 'teams.default')->first();
                $location                = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();
                $department              = $team->department()->select('departments.id', 'departments.name')->first();
                $userData['companyCode'] = $companyDetails->code;
                $userData['dob']         = $user->profile->birth_date->toDateString();
                $userData['location']    = [
                    'id'   => $location->id,
                    'name' => $location->name,
                ];
                $userData['department'] = [
                    'id'   => $department->id,
                    'name' => $department->name,
                ];
                $userData['team'] = [
                    'id'   => $team->id,
                    'name' => $team->name,
                ];
            }
            // Check condition for access portal and app when company login
            if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$companyDetails->allow_portal ) {
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $role->slug != 'user' && !$user->can_access_portal) {
                $this->guard()->factory()->invalidate($token);
                // if company has reseller company and he don't have portal access
                if ($companyDetails->is_reseller || $companyDetails->parent_id != null) {
                    return $this->invalidResponse($userData, \trans('api_labels.auth.portal_access'), 307);
                }
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
            } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$companyDetails->allow_app ) {
                $this->guard()->factory()->invalidate($token);
                return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
            }
        } else {
            $this->guard()->factory()->invalidate($token);
            $message = ($xDeviceOs != config('zevolifesettings.PORTAL')) ? \trans('api_labels.auth.not_access_app') : \trans('api_labels.auth.not_access_portal');
            return $this->notaccessFailedResponse($message);
        }

        if ($xDeviceOs != config('zevolifesettings.PORTAL') && $role->slug != 'user' && !$user->can_access_app) {
            $this->guard()->factory()->invalidate($token);
            $data         = new UserAutoFillResource($user);
            $autoFillData = $data->toArray($request);
            return $this->invalidResponse($autoFillData, trans('api_labels.auth.device_access'), 307);
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

                    $userToken = $request->headers->get('X-User-Tracker-Token');
                    if (!empty($device) && $device->user_token !== $userToken) {
                        $device->update(['user_token' => $userToken]);
                    }
                } else {
                    $device = $user->devices()->create([
                        'device_id'    => $request->headers->get('X-Device-Id'),
                        'device_os'    => $request->headers->get('X-Device-Os'),
                        'tracker'      => $request->headers->get('X-User-Tracker'),
                        'device_token' => $request->headers->get('X-Push-Token'),
                        'user_token'   => $request->headers->get('X-User-Tracker-Token'),
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
                    "id"       => $value->id,
                    "title"    => $value->title,
                    "iswin"    => (!empty($value->is_winner) && $value->is_winner) ,
                    "type"     => $value->challenge_type,
                    "teamName" => "",
                ];
            }
        }

        $userTeam           = $user->teams()->first();
        $spotlightChallenge = Challenge::select("challenges.id", "challenges.title", "teams.name AS teamName", DB::raw('"true" AS iswin'), "challenges.challenge_type AS type", DB::raw('max(challenge_wise_team_ponits.points) AS max_points'))
            ->join('challenge_wise_team_ponits', function ($join) use ($appTimezone, $userTimeZone) {
                $join->on('challenge_wise_team_ponits.challenge_id', '=', 'challenges.id')
                    ->whereRaw('round(challenge_wise_team_ponits.points, 1) > 0');
            })
            ->join("teams", "teams.id", "=", "challenge_wise_team_ponits.team_id")
            ->join('challenge_participants', 'challenge_participants.challenge_id', '=', 'challenges.id')
            ->where(function ($query) use ($userTeam) {
                $query->where('challenge_participants.team_id', $userTeam->id);
            })
            ->whereIn('challenges.challenge_type', ['inter_company', 'company_goal', 'team'])
            ->where(DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), ">=", now($userTimeZone)->toDateTimeString())
            ->orderByRaw('max(challenge_wise_team_ponits.points) DESC')
            ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
            ->groupBy("challenge_wise_team_ponits.challenge_id")
            ->get();

        $spotlightChallengeArray = [];
        if ($spotlightChallenge->count() > 0) {
            foreach ($spotlightChallenge as $value) {
                $spotlightChallengeArray[] = [
                    "id"       => $value->id,
                    "title"    => $value->title,
                    "iswin"    => true,
                    "type"     => $value->type,
                    "teamName" => $value->teamName,
                ];
            }
        }

        // Assign company plan records
        $planFeatureList = getCompanyPlanRecords($user);

        // Update last login
        $user->update(['last_login_at' => \now(config('app.timezone')), 'last_activity_at' => \now(config('app.timezone'))]);

        $message = "You are logged in successfully.";
        if (!empty($request->input('companyCode'))) {
            $message = "You are registered successfully.";
        }
        //Delete the record from user_otp table
        UserOtp::where('email', $user->email)->delete();

        $response = $this->successResponse([
            'data' => [
                'accessToken'         => 'Bearer' . $token,
                'user'                => new UserResource($user),
                'completedChallenges' => $participatedChallenges,
                'spotlightChallenge'  => $spotlightChallengeArray,
                'planFeatureList'     => $planFeatureList,
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
            $defaultLabelString = config('zevolifesettings.company_label_string', []);
            $planFeatureList    = [];
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
                $company = $user->company()->select('companies.id')->first();
                // Update user's notificaion setting table modules entries
                updateNotificationSettingModules($user);

                $lastActivityAt = $user->last_activity_at;
                // Update last activity at to set as an active users
                $user->update(['last_activity_at' => \now(config('app.timezone'))->format("Y-m-d H:i:s")]);

                $companyLabelString = $company->companyWiseLabelString()->get()->pluck('label_name', 'field_name')->toArray();

                // iterate default labels loop and check is label's custom value is set then user custom value else default value
                foreach ($defaultLabelString as $groupKey => $groups) {
                    foreach ($groups as $labelKey => $labelValue) {
                        $label = ($companyLabelString[$labelKey] ?? $labelValue['default_value']);
                        if (in_array($labelKey, ['location_logo', 'department_logo'])) {
                            $label = $company->getMediaData($labelKey, ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                        }
                        $companyLabelString[$labelKey] = $label;
                    }
                }

                // Assign company plan records
                $planFeatureList = getCompanyPlanRecords($user);
            } else {
                // get default value of each labels
                foreach ($defaultLabelString as $groupKey => $groups) {
                    foreach ($groups as $labelKey => $labelValue) {
                        $label = $labelValue['default_value'];
                        if (in_array($labelKey, ['location_logo', 'department_logo'])) {
                            $label = [
                                'width'  => 60,
                                'height' => 60,
                                'url'    => getThumbURL(['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'src' => $label], 'company', $labelKey),
                            ];
                        }
                        $companyLabelString[$labelKey] = $label;
                    }
                }
            }

            $records = AppSetting::all();

            return $this->successResponse(($records->count() > 0) ? new AppSettingCollection($records, $forceUpdateCount, $xDeviceOs, $lastActivityAt, $companyLabelString, $planFeatureList) : ['data' => []], 'Data recevied successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Disconnect all existing devices and connect with requested device passed in header.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(EmailRequest $request)
    {
        try {
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $user      = User::where('email', $request->input('email'))->first();
            $role      = getUserRole($user);

            if (!empty($user)) {
                if ($role->slug != 'user' && !$user->can_access_app) {
                    if (empty($request->get('socialId')) && $xDeviceOs != config('zevolifesettings.PORTAL')) {
                        if (!empty($request->get('password'))) {
                            //$xDeviceOs == config('zevolifesettings.PORTAL') &&
                            $token = JWTAuth::attempt($request->only('email', 'password'));
                            if (!$token) {
                                throw \Illuminate\Validation\ValidationException::withMessages([
                                    'password' => 'Please enter your current password.',
                                ])->status(422);
                            }
                        }
                        //Temperory added to send otp to when compny admin access the mobile app
                        if ($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) {
                            return $this->sendSingleUseCode($request, 'register');
                        }
                        return $this->successResponse(['data' => ['email_exists' => false]], 'Email does not exist');
                    } else {
                        return $this->successResponse(['data' => ['email_exists' => true]], trans('api_labels.auth.email_exist'));
                    }
                }
                return $this->successResponse(['data' => ['email_exists' => true]], trans('api_labels.auth.email_exist'));
            } else {

                if (empty($request->get('socialId'))) {
                    return $this->sendSingleUseCode($request, 'register');
                } else {
                    return $this->successResponse(['data' => ['email_exists' => false]], 'Email does not exist');
                }
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Send single use code to the application.
     *
     * @param  Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Spatie\Fractalistic\Exceptions\InvalidTransformation
     * @throws \Spatie\Fractalistic\Exceptions\NoTransformerSpecified
     */
    public function sendSingleUseCode(EmailRequest $request, $fromApi = 'login')
    {
        try {
            $companyDetails = array();
            $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));

            if (empty($request->get('email'))) {
                $this->validateLogin($request);
            }

            if ($fromApi == 'login') {

                $user = User::where('email', $request->input('email'))->first();
                if (empty($user)) {
                    throw ValidationException::withMessages([
                        'email' => trans('api_labels.auth.email_not_exists'),
                    ])->status(422);
                }
                $role           = getUserRole($user);
                $companyDetails = $user->company->first();

                if (!empty($role->group) && $role->group != 'zevo') {
                    if ($companyDetails->status == 0) {
                        return $this->unauthorizedResponse(\trans('auth.company_status'));
                    }
                }
                /*$userData              = [];
                $userData['firstName'] = $user->first_name;
                $userData['lastName']  = $user->last_name;
                $userData['email']     = $user->email;
                $userData['gender']    = ($user->profile) ? $user->profile->gender : 'male';*/

                if ($companyDetails) {
                    if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                        /*$team                    = $user->teams()->select('teams.id', 'teams.name', 'teams.department_id', 'teams.default')->first();
                    $location                = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();
                    $department              = $team->department()->select('departments.id', 'departments.name')->first();
                    $userData['companyCode'] = $companyDetails->code;
                    $userData['dob']         = $user->profile->birth_date->toDateString();
                    $userData['location']    = [
                    'id'   => $location->id,
                    'name' => $location->name,
                    ];
                    $userData['department'] = [
                    'id'   => $department->id,
                    'name' => $department->name,
                    ];
                    $userData['team'] = [
                    'id'   => $team->id,
                    'name' => $team->name,
                    ];*/
                    }

                    if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$companyDetails->allow_portal ) {
                        return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                    } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$companyDetails->allow_app ) {
                        return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
                    }
                } else {
                    $message = ($xDeviceOs != config('zevolifesettings.PORTAL')) ? \trans('api_labels.auth.not_access_app') : \trans('api_labels.auth.not_access_portal');
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
                }
            }

            $userOtpExists    = UserOtp::where('email', $request->input('email'))->first();
            $randomOtp        = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
            $totalResentCount = 0;

            if (!empty($userOtpExists)) {
                //Update
                $checkResentCount = UserOtp::select('user_otp.is_resent')->where('id', $userOtpExists->id)->first();
                if ($checkResentCount->is_resent >= 0) {
                    $totalResentCount = (int) $checkResentCount->is_resent + 1;
                }
                $record = UserOtp::where('id', $userOtpExists->id)
                    ->update([
                        'single_use_code' => $randomOtp,
                        'is_resent'       => $totalResentCount,
                        'updated_at'      => \now(config('app.timezone'))->format("Y-m-d H:i:s"),
                    ]);
            } else {
                //Add
                $record = UserOtp::create([
                    'email'           => $request->input('email'),
                    'single_use_code' => $randomOtp,
                ]);
            }
            if ($record) {
                dispatch(new SendSingleCodeEmailJob([
                    'singleUseCode'  => $randomOtp,
                    'email'          => $request->input('email'),
                    'companyDetails' => !empty($companyDetails) ? $companyDetails->id : null,
                ]));
                $otpValidTill        = '15 minutes';
                $otpValidTillMessage = str_replace("##VALID_TILL##", $otpValidTill, trans('api_labels.auth.sucess_otp_message'));
                $successMessage      = "Welcome " . $request->input('email') . ". " . $otpValidTillMessage;
                return $this->successResponse([], $successMessage);
            } else {
                throw ValidationException::withMessages([
                    'email' => trans('api_labels.auth.email_not_exists'),
                ])->status(422);
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * verify single use code to the application.
     *
     * @param  Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Spatie\Fractalistic\Exceptions\InvalidTransformation
     * @throws \Spatie\Fractalistic\Exceptions\NoTransformerSpecified
     */
    public function verifySingleUseCode(Request $request)
    {
        try {
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone)->toDateTimeString();
            $query       = UserOtp::where('email', $request->get('email'))->where('single_use_code', $request->get('singleUseCode')); //->first();
            $userData    = $query->first();

            if (empty($userData)) {
                return $this->invalidResponse([], \trans('api_labels.auth.invalid_otp'));
            } else {
                $codeExpired = $query
                    ->whereRaw("DATE_ADD(updated_at, INTERVAL '15' MINUTE) >= ?", $now)
                    ->first();

                if (empty($codeExpired)) {
                    throw ValidationException::withMessages([
                        'singleUseCode' => trans('api_labels.auth.otp_expired'),
                    ])->status(422);
                } else {
                    $successMessage = trans('api_labels.auth.otp_verified');
                    return $this->successResponse([], $successMessage);
                }
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
