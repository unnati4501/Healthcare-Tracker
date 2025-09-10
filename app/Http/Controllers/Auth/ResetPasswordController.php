<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
     */
    private $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $model, AuditLogRepository $auditLogRepository)
    {
        $this->middleware('guest');
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param string|null $token
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetForm(Request $request, $token = null)
    {
        $data            = array();
        $data['appUser'] = '';
        if (!empty($request->get('appUser')) && $request->get('appUser') == 'yes') {
            $data['appUser'] = 'yes';
        }
        $user = \DB::table('password_resets')->where('token', $token)->first();

        if (isset($user)) {
            $branding         = getBrandingData(); // Check is object or null
            $data['branding'] = $branding;
            $data['ga_title'] = trans('page_title.reset-password');
            return view('auth.reset-password', $data)
                ->withToken($token)
                ->withEmail($user->email);
        } else {
            return redirect()->route('login');
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $payload    = $request->all();
            $validation = Validator::make($payload, ['token' => 'required', 'email' => 'required|email', 'password' => 'required|confirmed']);

            if ($validation->fails()) {
                return redirect()->back()->withErrors($validation->errors());
            }
            DB::beginTransaction();
            $result = $this->model->resetPassword($request->all());
            DB::commit();
            if ($result['status'] == 'success') {
                $messageData = [
                    'data'   => trans('non-auth.reset-password.messages.password_changed'),
                    'status' => 1,
                ];
                $this->auditLogRepository->created("Password changed successfully", $request->all());
            } elseif ($result['status'] == 'emailerror') {
                $messageData = [
                    'data' => trans('non-auth.reset-password.messages.invalid_email'),
                ];
                return redirect()->back()->withErrors($messageData);
            } elseif ($result['status'] == 'tokenerror') {
                $messageData = [
                    'data' => trans('non-auth.reset-password.messages.token_mismatch'),
                ];
                return redirect()->back()->withErrors($messageData);
            } else {
                $messageData = [
                    'data'   => trans('non-auth.reset-password.messages.something_wrong'),
                    'status' => 0,
                ];
            }
            if (!empty($payload['appUser']) && $payload['appUser'] == 'yes') {
                $data             = array();
                $branding         = getBrandingData(); // Check is object or null
                $data['branding'] = $branding;
                return \view('auth.redirect-app-user', $data)->with('message', $messageData);
            } else {
                return \Redirect::route('login')->with('message', $messageData);
            }
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            $messageData = [
                'data'   => $e->getMessage(),
                'status' => 0,
            ];
            return \Redirect::route('login')->with('message', $messageData);
        }
    }
}
