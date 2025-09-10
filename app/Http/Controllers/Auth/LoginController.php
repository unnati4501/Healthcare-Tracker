<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\SendSingleCodeEmailJob;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\User;
use App\Models\UserOtp;
use App\Repositories\AuditLogRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'en/dashboard';

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * Create a new user model instance.
     *
     * @return void
     */
    public function __construct(User $user, AuditLogRepository $auditLogRepository)
    {
        $this->middleware('guest')->except('logout');
        $this->auditLogRepository = $auditLogRepository;
        $this->user               = $user;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {

        if (!empty($_COOKIE["azureAdAuth"])) {
            $domain      = getDefaultDomain();
            $azureAdAuth = json_decode(decrypt($_COOKIE["azureAdAuth"]), true);

            setcookie("azureAdAuth", "", time() - 3600, "/", $domain);
            setcookie("callingUrl", "", time() - 3600, "/", $domain);

            if (!empty($azureAdAuth['email'])) {
                $serverNameAsArray = explode('.', $azureAdAuth["domain"]);
                $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
                $user              = User::where('email', $azureAdAuth['email'])->first();
                $company           = $user->company->first();
                $isChildCo         = (!$company->is_reseller && !is_null($company->parent_id));
                Auth::login($user, true);

                $brandingLogo = asset('assets/dist/img/full-logo.png');

                if (!empty($company)) {
                    $companyID    = $company->id;
                    $brandingData = getBrandingDataByCompanyId((($isChildCo) ? $company->parent_id : $companyID));
                    $brandingLogo = $brandingData->company_logo;

                    if (in_array($serverNameAsArray[0], $platformDomain)) {
                        $brandingLogo = asset('assets/dist/img/full-logo.png');
                    }
                }

                $request->session()->put('companyLogo', $brandingLogo);
                return redirect('/');
            }
        }

        $branding     = getBrandingData(); // Check is object or null
        $access_token = $request->session()->get('azure_access_token');

        // Disable SSO functionality when disable from company.
        $disableSSO = true;
        $loginUrl   = request()->getHost();
        $splitUrl   = explode('.', $loginUrl);
        if (!empty($splitUrl)) {
            $brandingSubDomain = $splitUrl[0];
            $companyBranding   = CompanyBranding::where('sub_domain', $brandingSubDomain)->first();
            if (!empty($companyBranding)) {
                $company    = $companyBranding->company()->select('disable_sso')->first();
                $disableSSO = !($company->disable_sso);
            }
        }

        if ($access_token) {
            return redirect(App::currentLocale().'/logout/azure');
        }

        $layOutData = [
            'branding'   => $branding,
            'disableSSO' => $disableSSO,
            'lang' => App::currentLocale(),
        ];
        $layOutData['ga_title'] = trans('page_title.login');
        return view('auth.login', $layOutData);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $userTimezone      = $request->timezone;
        $oldMexicoTimezone = config('zevolifesettings.mexico_city_timezone.old_timezone');
        $newMexicoTimezone = config('zevolifesettings.mexico_city_timezone.new_timezone');
        $isTimezone        = false;
        if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
            $userTimezone = $newMexicoTimezone;
            $isTimezone   = true;
        }

        $userTimezone = (!empty($userTimezone) ? $userTimezone : "Europe/Dublin");
        $this->validateLogin($request);

        $payload = $request->all();
        $user    = $this->user->findByEmail($payload['email']);

        if ((!$user) || ($user && empty($user->password))) {
            $messageData = [
                'data'   => trans('non-auth.login.messages.not_registered'),
                'status' => 0,
            ];
            return redirect(App::currentLocale().'/login')->with('message', $messageData);
        }

        $role = getUserRole($user);

        if ($role->group != 'zevo') {
            if ($user->company->first()->status == 0) {
                $messageData = [
                    'data'   => trans('non-auth.login.messages.company_status'),
                    'status' => 2,
                ];
                return redirect(App::currentLocale().'/login')->with('message', $messageData);
            }
        }

        if ($role->slug == 'wellbeing_specialist' && !is_null($user->deleted_at)) {
            $messageData = [
                'data'   => trans('api_labels.auth.archive_user'),
                'status' => 0,
            ];
            return redirect(App::currentLocale().'/login')->with('message', $messageData);
        }
        
        if ($this->attemptLogin($request)) {
            $company = Auth::user()->company->first();
            $this->auditLogRepository->created("Login successfully", $user->toArray());
            if (!empty($company)) {
                $isChildCo = (!$company->is_reseller && !is_null($company->parent_id));
                $parentCo  = null;
                if ($isChildCo) {
                    $parentCo = Company::find($company->parent_id);
                }

                $companyID         = $company->id;
                $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
                $allBrandingDomain = CompanyBranding::where("status", true)
                    ->whereNotIn("sub_domain", $platformDomain);

                if ($isChildCo) {
                    $allBrandingDomain->where('company_id', $parentCo->getKey());
                } elseif ($company->is_reseller) {
                    $allBrandingDomain->where('company_id', $company->id);
                }

                $allBrandingDomain = $allBrandingDomain->pluck("sub_domain")->toArray();
                $brandingData      = getBrandingDataByCompanyId((($isChildCo) ? $company->parent_id : $companyID));

                $brandingLogo = $brandingData->company_logo;

                if (in_array(ZC_SERVER_SUBDOMAIN, $platformDomain)) {
                    $brandingLogo = asset('assets/dist/img/full-logo.png');
                }

                $request->session()->put('companyLogo', $brandingLogo);

                if ($company->is_reseller == 1 || !is_null($company->parent_id)) {
                    $request->session()->put('is_reseller', 1);
                }

                $isServerDomainAvailableInBranding = in_array($brandingData->sub_domain, $allBrandingDomain);

                $companyBrandingStatus = (boolean) (($isChildCo) ? $parentCo->is_branding : $company->is_branding);
                $brandingStatus        = (boolean) $brandingData->status;
                $isBrandinEnable       = ($companyBrandingStatus && $brandingStatus);
                $isServerDomainSame    = (ZC_SERVER_SUBDOMAIN == $brandingData->sub_domain);

                if ($isServerDomainAvailableInBranding && $isServerDomainSame && $isBrandinEnable && $companyBrandingStatus) {
                    // Only branding domain can login from same domain.
                    $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                    return $this->sendLoginResponse($request);
                } elseif (!$isServerDomainSame && in_array(ZC_SERVER_SUBDOMAIN, $platformDomain)) {
                    // Branding domain can login from Zevo Platfom domain.
                    $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                    return $this->sendLoginResponse($request);
                } elseif (!$isServerDomainAvailableInBranding && !$isServerDomainSame && !$isBrandinEnable && $companyBrandingStatus) {
                    // Branding not available.
                    $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                    return $this->sendLoginResponse($request);
                } elseif (app()->environment() == "local") {
                    $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                    return $this->sendLoginResponse($request);
                } else {
                    $this->guard()->logout();
                    $request->session()->invalidate();
                    $messageData = [
                        'data'   => trans('non-auth.login.messages.not_authorized'),
                        'status' => 2,
                    ];
                    return redirect(App::currentLocale().'/login')->with('message', $messageData);
                }
            }

            if (!$user->is_coach) {
                $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone, 'saml_token' => null]);
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $email = Auth::user()->email;
        $role  = getUserRole(Auth::user());
        if ($role->slug == 'wellbeing_specialist') {
            \DB::table('consent_form_logs')->where('ws_id', Auth::user()->id)->update(['is_accessed' => 0]);
        }
        $this->auditLogRepository->created("Logged out successfully", Auth::user()->toArray());
        $this->guard()->logout();
        $access_token  = $request->session()->get('azure_access_token');
        $saml_token    = $request->session()->get('azure_saml_token');
        $request->session()->invalidate();

        if (!empty($saml_token)) {
            return redirect('/en/logout/saml')->with('email', $email);
        }

        if (!$access_token) {
            return redirect()->intended("/");
        }

        return $this->loggedOut($request) ?: redirect(App::currentLocale().'/logout/azure');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $messageData = [
            'data'   => trans('api_labels.auth.wrong_credentials'),
            'status' => 0,
        ];
        return redirect(App::currentLocale().'/login')->with('message', $messageData);
    }

    /**
     * Send Otp to login email address
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendOtp(Request $request)
    {
        try {
            $email = $request->input('email');
            if (empty($email)) {
                $this->validateLogin($request);
            }

            $user = User::where('email', $email)->first();
            if (empty($user)) {
                return [
                    'status'  => false,
                    'message' => trans('api_labels.auth.email_not_exists'),
                ];
            }
            $role = getUserRole($user);
            if ($role->slug == 'wellbeing_specialist' && !is_null($user->deleted_at)) {
                return [
                    'status'  => false,
                    'message' => trans('api_labels.auth.archive_user'),
                ];
            }
            $userOtpExists    = UserOtp::where('email', $email)->get();
            $companyDetails   = $user->company->first();
            $randomOtp        = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

            //If otp count > 3 then latest one then remove first added entry
            if ($userOtpExists->count() > 3) {
                UserOtp::where('email', $email)->orderBy('id', 'ASC')->limit(1)->delete();
            }

            \DB::beginTransaction();
            $record = UserOtp::create(
                [
                    'email'           => $email,
                    'single_use_code' => $randomOtp,
                ]
            );
            $this->auditLogRepository->created("Send OTP successfully", $user->toArray());
            // Expire the previously added codes
            UserOtp::where('id', '!=', $record->id)->where('email', $request->input('email'))
                ->update([
                    'updated_at' => Carbon::now()->subMinute(16),
                ]);

            if ($record) {
                dispatch(new SendSingleCodeEmailJob([
                    'singleUseCode'  => $randomOtp,
                    'email'          => $email,
                    'companyDetails' => !empty($companyDetails) ? $companyDetails->id : null,
                ]));
                $otpValidTill        = '15 minutes';
                $otpValidTillMessage = str_replace("##VALID_TILL##", $otpValidTill, trans('api_labels.auth.sucess_otp_message'));
                $successMessage      = "Welcome " . $request->input('email') . ". " . $otpValidTillMessage;
                $messageData         = [
                    'message' => $successMessage,
                    'status'  => true,
                ];
                \DB::commit();
                return $messageData;
            } else {
                return [
                    'message' => trans('api_labels.auth.email_not_exists'),
                    'status'  => false,
                ];
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return [
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong'),
            ];
        }
    }

    /**
     * Verify Otp for login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyOtp(LoginRequest $request)
    {
        try {
            $digit             = implode('', $request->input('digit'));
            $email             = $request->get('email');
            $appTimezone       = config('app.timezone');
            $now               = now($appTimezone)->toDateTimeString();
            $query             = UserOtp::where('email', $email)->where('single_use_code', $digit);
            $userData          = $query->first();
            $userTimezone      = $request->timezone;
            $oldMexicoTimezone = config('zevolifesettings.mexico_city_timezone.old_timezone');
            $newMexicoTimezone = config('zevolifesettings.mexico_city_timezone.new_timezone');
            $isTimezone        = false;
            $codeExpired       = $query->whereRaw("DATE_ADD(updated_at, INTERVAL '15' MINUTE) >= ?", $now)->first();

            if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
                $userTimezone = $newMexicoTimezone;
                $isTimezone   = true;
            }
            if (empty($userData)) {
                return [
                    'message' => trans('api_labels.auth.invalid_otp'),
                    'status'  => false,
                ];
            } else if (is_null($codeExpired)) {
                return [
                    'message' => trans('api_labels.auth.otp_expired'),
                    'status'  => false,
                ];
            } else {
                $userTimezone = (!empty($userTimezone) ? $userTimezone : "Europe/Dublin");
                $user         = $this->user->findByEmail($email);
                if (!$user) {
                    return [
                        'message' => trans('non-auth.login.messages.not_registered'),
                        'status'  => false,
                    ];
                }

                $role = getUserRole($user);

                if ($role->group != 'zevo' && $user->company->first()->status == 0) {
                    return [
                        'message' => trans('non-auth.login.messages.company_status'),
                        'status'  => false,
                    ];
                }

                Auth::login($user, true);
                $this->auditLogRepository->created("OTP verified successfully", $user->toArray());
                UserOtp::where('email', $user->email)->delete();

                $company = Auth::user()->company->first();
                if (!empty($company)) {
                    $isChildCo = (!$company->is_reseller && !is_null($company->parent_id));
                    $parentCo  = null;
                    if ($isChildCo) {
                        $parentCo = Company::find($company->parent_id);
                    }

                    $companyID         = $company->id;
                    $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
                    $allBrandingDomain = CompanyBranding::where("status", true)
                        ->whereNotIn("sub_domain", $platformDomain);

                    if ($isChildCo) {
                        $allBrandingDomain->where('company_id', $parentCo->getKey());
                    } elseif ($company->is_reseller) {
                        $allBrandingDomain->where('company_id', $company->id);
                    }

                    $allBrandingDomain = $allBrandingDomain->pluck("sub_domain")->toArray();
                    $brandingData      = getBrandingDataByCompanyId((($isChildCo) ? $company->parent_id : $companyID));

                    $brandingLogo = $brandingData->company_logo;

                    if (in_array(ZC_SERVER_SUBDOMAIN, $platformDomain)) {
                        $brandingLogo = asset('assets/dist/img/full-logo.png');
                    }

                    $request->session()->put('companyLogo', $brandingLogo);

                    if ($company->is_reseller == 1 || !is_null($company->parent_id)) {
                        $request->session()->put('is_reseller', 1);
                    }

                    $isServerDomainAvailableInBranding = in_array($brandingData->sub_domain, $allBrandingDomain);

                    $companyBrandingStatus = (boolean) (($isChildCo) ? $parentCo->is_branding : $company->is_branding);
                    $brandingStatus        = (boolean) $brandingData->status;
                    $isBrandinEnable       = ($companyBrandingStatus && $brandingStatus);
                    $isServerDomainSame    = (ZC_SERVER_SUBDOMAIN == $brandingData->sub_domain);

                    if ($isServerDomainAvailableInBranding && $isServerDomainSame && $isBrandinEnable && $companyBrandingStatus) {
                        // Only branding domain can login from same domain.
                        $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                        return [
                            'message' => trans('non-auth.login.messages.successfully_authorized'),
                            'status'  => true,
                        ];
                    } elseif (!$isServerDomainSame && in_array(ZC_SERVER_SUBDOMAIN, $platformDomain)) {
                        // Branding domain can login from Zevo Platfom domain.
                        $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                        return [
                            'message' => trans('non-auth.login.messages.successfully_authorized'),
                            'status'  => true,
                        ];
                    } elseif (!$isServerDomainAvailableInBranding && !$isServerDomainSame && !$isBrandinEnable && $companyBrandingStatus) {
                        // Branding not available.
                        $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                        return [
                            'message' => trans('non-auth.login.messages.successfully_authorized'),
                            'status'  => true,
                        ];
                    } elseif (app()->environment() == "local") {
                        $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone]);
                        return [
                            'message' => trans('non-auth.login.messages.successfully_authorized'),
                            'status'  => true,
                        ];
                    } else {
                        $this->guard()->logout();
                        $request->session()->invalidate();
                        return [
                            'message' => trans('non-auth.login.messages.not_authorized'),
                            'status'  => false,
                        ];
                    }
                }
                if (!$user->is_coach) {
                    $user->update(['timezone' => $userTimezone, 'is_timezone' => $isTimezone, 'saml_token' => null]);
                }
                return [
                    'message' => trans('non-auth.login.messages.successfully_authorized'),
                    'status'  => true,
                ];
            }
        } catch (\Exception $e) {
            report($exception);
            return [
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong'),
            ];
        }
    }
}
