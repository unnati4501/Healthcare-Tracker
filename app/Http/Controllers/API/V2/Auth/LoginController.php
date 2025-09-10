<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Collections\V1\CategoryCollection;
use App\Http\Collections\V2\AppSettingCollection;
use App\Http\Controllers\API\V1\Auth\LoginController as v1LoginController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeviceRequest;
use App\Http\Requests\Api\V1\EmailRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSetting;
use App\Models\AppVersion;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use JWTAuth;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class LoginController extends v1LoginController
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
                'social_type' => 1,
            ]);
            $token = $this->guard()->fromUser($userData);
            $this->guard()->setToken($token)->setUser($userData);
            return $token;
        } else {
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

        $role = getUserRole($user);

        if ($role->group != 'zevo') {
            if ($user->company->first()->status == 0) {
                return $this->unauthorizedResponse(\trans('auth.company_status'));
            }
        }

        if ($role->slug != 'user') {
            if (!$user->can_access_app) {
                $userData              = [];
                $userData['firstName'] = $user->first_name;
                $userData['lastName']  = $user->last_name;
                $userData['email']     = $user->email;
                $userData['gender']    = ($user->profile) ? $user->profile->gender : 'male';

                $this->guard()->factory()->invalidate($token);
                return $this->invalidResponse($userData, \trans('api_labels.auth.device_access'), 307);
            }
        }

        if ($user->teams->isEmpty()) {
            $this->guard()->factory()->invalidate($token);
            return $this->unauthorizedResponse(\trans('api_labels.auth.failed'));
        }

        // In case it's a request from mobile device
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

        // Update last login
        $user->update(['last_login_at' => \now(config('app.timezone')), 'last_activity_at' => \now(config('app.timezone'))]);

        $message = "You are logged in successfully.";
        if (!empty($request->input('companyCode'))) {
            $message = "You are registered successfully.";
        }

        $response = $this->successResponse([
            'data' => [
                'accessToken' => 'Bearer' . $token,
                'user'        => new UserResource($user),
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
     * Disconnect all existing devices and connect with requested device passed in header.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnectDevice(DeviceRequest $request)
    {
        try {
            /** @var User $user */
            $user = User::findByEmail($request->email);

            // remove devices
            $user->devices()->forceDelete();

            $device = $user->devices()->create([
                'device_id'    => $request->headers->get('X-Device-Id'),
                'device_os'    => $request->headers->get('X-Device-Os'),
                'tracker'      => $request->headers->get('X-User-Tracker'),
                'device_token' => $request->headers->get('X-Push-Token'),
            ]);

            return $this->successResponse([], 'Device disconnected successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * update user device push token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePushToken(Request $request)
    {
        try {
            /** @var User $user */
            $user = $this->user();

            $pushToken = (!empty($request->pushToken)) ? $request->pushToken : ((!empty($request->headers->get('X-Push-Token'))) ? $request->headers->get('X-Push-Token') : null);

            $deviceUpdated = $user->devices()->first()->update([
                'device_token' => $pushToken,
            ]);

            return $this->successResponse([], 'Token updated successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * update user device push token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        try {
            /** @var User $user */
            $user = $this->user();

            // logout user
            auth()->logout();

            // delete user
            $user->deleteRecord();

            return $this->successResponse([], 'Your account has been deleted successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseCategories(Request $request)
    {
        try {
            $records = Category::where('in_activity_level', 1)->get();

            return $this->successResponse(($records->count() > 0) ? new CategoryCollection($records) : ['data' => []], 'Master Categories Received Successfully.');
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
            $appVersion = $request->appVersion;
            $deviceOs   = $request->headers->get('X-Device-Os');

            $forceUpdateCount = 0;

            if (strtolower($deviceOs) == 'android') {
                $exactVersionRecord = AppVersion::where('andriod_version', $appVersion)->first();

                if (!empty($exactVersionRecord)) {
                    $forceUpdateCount = AppVersion::where('id', '>', $exactVersionRecord->id)->where('andriod_force_update', true)->count();
                }
            } elseif (strtolower($deviceOs) == 'ios') {
                $exactVersionRecord = AppVersion::where('ios_version', $appVersion)->first();

                if (!empty($exactVersionRecord)) {
                    $forceUpdateCount = AppVersion::where('id', '>', $exactVersionRecord->id)->where('ios_force_update', true)->count();
                }
            }

            $user = Auth::guard('api')->user();
            if (!empty($user)) {
                // Update last activity at to set as an active users
                $user->update(['last_activity_at' => \now(config('app.timezone'))->format("Y-m-d H:i:s")]);
            }

            $records = AppSetting::all();
            return $this->successResponse(($records->count() > 0) ? new AppSettingCollection($records, $forceUpdateCount, strtolower($deviceOs)) : ['data' => []], 'Data recevied successfully.');
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
            $user = User::where('email', $request->input('email'))->first();

            $role = getUserRole($user);

            if (!empty($user)) {
                if ($role->slug != 'user' && !$user->can_access_app) {
                    if (empty($request->get('socialId'))) {
                        $token = JWTAuth::attempt($request->only('email', 'password'));
                        if (!$token) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'password' => 'Please enter your current password.',
                            ])->status(422);
                        }
                        return $this->successResponse(['data' => ['email_exists' => false]], 'Email does not exist');
                    } else {
                        return $this->successResponse(['data' => ['email_exists' => true]], 'Email already exists');
                    }
                }
                return $this->successResponse(['data' => ['email_exists' => true]], 'Email already exists');
            } else {
                return $this->successResponse(['data' => ['email_exists' => false]], 'Email does not exist');
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
