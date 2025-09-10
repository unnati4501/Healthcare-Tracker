<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserForgotPasswordEvent;
use App\Http\Controllers\Controller;
use App\Models\CompanyBranding;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */
    private $model;
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $model)
    {
        $this->middleware('guest');
        $this->model = $model;
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        $branding = getBrandingData(); // Check is object or null

        $layOutData = [
            'branding' => $branding,
        ];
        $layOutData['ga_title'] = trans('page_title.forgot-password');
        return view('auth.forgot-password', $layOutData);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $appTimezone        = config('app.timezone');
        $now                = now($appTimezone)->toDateTimeString();
        $payload            = $request->all();
        $validation         = Validator::make($payload, ['email' => 'required|email']);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation->errors());
        }

        $user  = $this->model->findByEmail($payload['email']);
        if (!empty($user)) {
            $resetCount     = $user->reset_password_count ?? 0;
            $lastResetTime  = $user->where('email', $payload['email'])->whereRaw("DATE_ADD(password_reset_at, INTERVAL '5' MINUTE) >= ?", $now)->first();    
        }
         
        if (!$user) {
            $messageData = [
                'data'   => trans('non-auth.forgot-password.messages.registered_email'),
                'status' => 1,
            ];
            return \Redirect::route('login')->with('message', $messageData);
        }
        
        if ($resetCount >= 5 && is_null($lastResetTime)){
            $user->where('email', $payload['email'])->update([
                'reset_password_count' => 1, 
                'password_reset_at' => null
            ]);
        }
       
        if (!empty($user) && $resetCount >= 5 && !is_null($lastResetTime)) {
            return redirect()->route('login')->withErrors(['error' => trans('non-auth.forgot-password.messages.limit_exceed')]);
        }

        if ($user->is_blocked) {
            $messageData = [
                'data'   => trans('non-auth.forgot-password.messages.user_blocked'),
                'status' => 0,
            ];
            return redirect()->back()->withInput()->with('message', $messageData);
        }
        if ($user->reset_password_count <= 4){
            $user->where('email', $payload['email'])->update(['reset_password_count' => $user->reset_password_count + 1]);
        }
        if ($resetCount == 4) {
            $user->where('email', $payload['email'])->update(['password_reset_at' => $now]);
        }
        $company = $user->company->first();

        if (!empty($company)) {
            $companyID         = $company->id;
            $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
            $allBrandingDomain = CompanyBranding::where("status", true)
                ->whereNotIn("sub_domain", $platformDomain)
                ->pluck("sub_domain")
                ->toArray();
            $brandingData                      = getBrandingDataByCompanyId($companyID);
            $isServerDomainAvailableInBranding = in_array($brandingData->sub_domain, $allBrandingDomain);
            $companyBrandingStatus             = (boolean) $company->is_branding;
            $brandingStatus                    = (boolean) $brandingData->status;
            $isBrandinEnable                   = $companyBrandingStatus && $brandingStatus;
            $isServerDomainSame                = (ZC_SERVER_SUBDOMAIN == $brandingData->sub_domain);

            if ($isServerDomainAvailableInBranding && $isServerDomainSame && $isBrandinEnable && $companyBrandingStatus) {
            } elseif (!$isServerDomainSame && in_array(ZC_SERVER_SUBDOMAIN, $platformDomain)) {
            } elseif (!$isServerDomainAvailableInBranding && !$isServerDomainSame && !$isBrandinEnable && $companyBrandingStatus) {
            } elseif (app()->environment() == "local") {
            } else {
                $messageData = [
                    'data'   => trans('non-auth.forgot-password.messages.not_authorized'),
                    'status' => 2,
                ];
                return redirect()->back()->withInput()->with('message', $messageData);
            }
        }

        $token = $this->model->saveToken($user);

        // fire forgot password event
        event(new UserForgotPasswordEvent($user, $token));

        $messageData = [
            'data'   => trans('non-auth.forgot-password.messages.registered_email'),
            'status' => 1,
        ];

        return \Redirect::route('login')->with('message', $messageData);
    }
}
