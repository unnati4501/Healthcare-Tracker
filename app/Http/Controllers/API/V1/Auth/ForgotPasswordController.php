<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ServesApiTrait;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Mail;

/**
 * Class ForgotPasswordController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails,
        ServesApiTrait;

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

            $data = [
                'email' => $user->email,
                'subject' => "Reset Password Notification",
                'reset_password_url' => route('password.reset', array($token,'appUser' => 'yes')),
                'logo'    => asset('app_assets/LOGO.svg'),
            ];

            $abc = Mail::send(['html' => 'emails.reset-password'], $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });

            return $this->successResponse([], 'Reset Password link sent to your registered email address.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
