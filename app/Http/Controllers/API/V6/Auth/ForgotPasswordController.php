<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V6\Auth;

use App\Http\Controllers\API\V5\Auth\ForgotPasswordController as v5ForgotPasswordController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ServesApiTrait;
use App\Events\UserForgotPasswordEvent;

/**
 * Class ForgotPasswordController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class ForgotPasswordController extends v5ForgotPasswordController
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
            $user = $this->model->findByEmail($request->email);

            if ($user->is_blocked) {
                return $this->unauthorizedResponse(\trans('api_labels.auth.inactive'));
            }

            $token = $this->model->saveToken($user);

            // fire forgot password event
            event(new UserForgotPasswordEvent($user, $token, 'yes'));

            return $this->successResponse([], 'Reset Password link sent to your registered email address.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
