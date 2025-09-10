<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V11\Auth;

use App\Events\UserForgotPasswordEvent;
use App\Http\Controllers\API\V10\Auth\ForgotPasswordController as v10ForgotPasswordController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V10\ResetPasswordRequest;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ForgotPasswordController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class ForgotPasswordController extends v10ForgotPasswordController
{
    use ServesApiTrait;

    private $model;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Send a reset link to the requsted user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        try {
            $user      = $this->model->findByEmail($request->email);
            $company   = $user->company->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            if ($user->is_blocked) {
                return $this->unauthorizedResponse(\trans('api_labels.auth.inactive'));
            }

            if ($company) {
                $role = getUserRole($user);
                // Check condition for access portal and app when company login
                if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$company->allow_portal) {
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                }

                if ($xDeviceOs == config('zevolifesettings.PORTAL') && $role->slug != 'user' && !$user->can_access_portal) {
                    // if company has reseller company and he don't have portal access
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$company->allow_app) {
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
                }
            } else {
                $message = ($xDeviceOs != config('zevolifesettings.PORTAL')) ? \trans('api_labels.auth.not_access_app') : \trans('api_labels.auth.not_access_portal');
                return $this->notaccessFailedResponse($message);
            }

            $token   = $this->model->saveToken($user);
            $appUser = null;
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $appUser = 'no';
            } elseif ($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) {
                $appUser = 'yes';
            }

            // fire forgot password event
            event(new UserForgotPasswordEvent($user, $token, $appUser, $xDeviceOs));

            return $this->successResponse([], 'Reset Password link sent to your registered email address.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Send a reset link to the requsted user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $token    = $request->input('token');
            $email    = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();

            if (!empty($user)) {
                $password_resetData = DB::table('password_resets')->where('token', $token)->where('email', $email)->first();

                if (!empty($password_resetData)) {
                    $user->password = bcrypt($password);
                    $user->save();
                    DB::table('password_resets')->where('email', $email)->delete();
                    return $this->successResponse(['status' => 'success'], \trans('api_labels.auth.passwords.reset'));
                } else {
                    return $this->unauthorizedResponse(\trans('api_labels.auth.passwords.token'));
                }
            } else {
                return $this->unauthorizedResponse(\trans('api_labels.auth.passwords.email'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get email from reset password token [Portal]
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmail(Request $request)
    {
        try {
            $token              = $request->input('token');
            $password_resetData = DB::table('password_resets')->where('token', $token)->first();

            if ($password_resetData) {
                $userData               = [];
                $userData['email']      = $password_resetData->email;
                $userData['created_at'] = $password_resetData->created_at;
                return $this->successResponse($userData, \trans('api_labels.auth.passwords.getemail'));
            } else {
                return $this->unauthorizedResponse(\trans('api_labels.auth.passwords.token'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
