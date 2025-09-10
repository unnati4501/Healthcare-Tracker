<?php

namespace App\Http\Middleware;

use App\Models\CompanyBranding;
use App\Models\User;
use Auth;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Graph\Core\Authentication\GraphPhpLeagueAuthenticationProvider;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Graph\Generated\Models;
use Illuminate\Support\Facades\App;

class AppAzure
{
    protected $login_route = "/";
    protected $baseUrl     = "https://login.microsoftonline.com/";
    protected $route2      = "/oauth2/v2.0/";
    protected $route       = "/oauth2/";
    /**
     * Handle an incoming request
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $access_token  = $request->session()->get('azure_access_token');
        $refresh_token = $request->session()->get('azure_refresh_token');

        if (config('app.env') === "testing") {
            return $this->handleTesting($request, $next, $access_token, $refresh_token);
        }

        if (!$access_token || !$refresh_token) {
            return $this->redirect($request);
        }

        $client = new Client();

        try {
            $response = $client->request('POST', $this->baseUrl . 'common' . $this->route . "token", [
                'form_params' => [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => config('azure.client.id'),
                    'client_secret' => config('azure.client.secret'),
                    'refresh_token' => $refresh_token,
                    'resource'      => config('azure.resource'),
                ],
            ]);

            $contents = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            $this->fail($request, $e);
        }

        $request->session()->put('azure_access_token', $contents->access_token);
        $request->session()->put('azure_refresh_token', $contents->refresh_token);

        return $this->handlecallback($request, $next, $access_token, $refresh_token);
    }

    /**
     * Handle an incoming request in a testing environment
     * Assumes tester is calling actingAs or loginAs during testing to run this correctly
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    protected function handleTesting(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!isset($user)) {
            return $this->redirect($request, $next);
        }

        return $this->handlecallback($request, $next, null, null);
    }

    /**
     * Gets the azure url
     *
     * @return String
     */
    public function getAzureUrl()
    {
        return $this->baseUrl . 'common' . $this->route2 . "authorize?response_type=code&client_id=" . config('azure.client.id') . "&domain_hint=" . urlencode(config('azure.domain_hint')) . "&scope=" . urldecode(config('azure.scope'));
    }

    /**
     * Redirects to the Azure route.  Typically used to point a web route to this method.
     * For example: Route::get('/login/azure', '\RootInc\LaravelAzureMiddleware\Azure@azure');
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function azure()
    {
        if (in_array(ZC_SERVER_SUBDOMAIN, explode(',', config('saml.subdomains')))) {
            return redirect(App::currentLocale().'/login/saml');
        }

        $domain = getDefaultDomain();

        setcookie("callingUrl", ZC_SERVER, time() + 3600, "/", $domain);
        return redirect()->away($this->getAzureUrl());
    }

    /**
     * Customized Redirect method
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    protected function redirect()
    {
        return redirect($this->login_route);
    }

    /**
     * Callback after login from Azure
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws \Exception
     */
    public function azurecallback(Request $request)
    {
        $client = new Client();

        $code = $request->input('code');

        try {
            $response = $client->request('POST', $this->baseUrl . 'common' . $this->route . "token", [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => config('azure.client.id'),
                    'client_secret' => config('azure.client.secret'),
                    'code'          => $code,
                    'resource'      => config('azure.resource'),
                ],
            ]);

            $contents = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            return $this->fail($request, $e);
        }

        $access_token  = $contents->access_token;
        $refresh_token = $contents->refresh_token;

        $profile = json_decode(base64_decode(explode(".", $contents->id_token)[1]));

        $request->session()->put('azure_access_token', $access_token);
        $request->session()->put('azure_refresh_token', $refresh_token);

        return $this->success($request, $access_token, $refresh_token, $profile);
    }

    /**
     * Handler that is called when a successful login has taken place for the first time
     *
     * @param \Illuminate\Http\Request $request
     * @param String $access_token
     * @param String $refresh_token
     * @param mixed $profile
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    protected function success(Request $request, $access_token, $refresh_token, $profile)
    {
        try {
            $tokenRequestContext = new ClientCredentialContext(
                config('azure.tenant_id'),
                config('azure.client.id'),
                config('azure.client.secret'),
            );
            $scopes = ['https://graph.microsoft.com/.default'];
            $graph = new GraphServiceClient($tokenRequestContext, $scopes);
            
            $user = $graph->me()->get()->wait();

            $user = User::where('email', strtolower($email))->first();

            if (!$user) {
                abort(403, "User is not authorized within Azure AD to access this application.");
            }

            Auth::login($user, true);
            $company = $user->company->first();

            if (!empty($_COOKIE["callingUrl"]) && !empty($company)) {
                $serverNameAsArray = explode('.', $_COOKIE["callingUrl"]);
                $companyID         = $company->id;
                $platformDomain    = config('zevolifesettings.domain_branding.PLATFORM_DOMAIN');
                $allBrandingDomain = CompanyBranding::where("status", true)
                    ->whereNotIn("sub_domain", $platformDomain)
                    ->pluck("sub_domain")
                    ->toArray();
                $brandingData = getBrandingDataByCompanyId($companyID);

                $brandingLogo = $brandingData->company_logo;

                if (in_array($serverNameAsArray[0], $platformDomain)) {
                    $brandingLogo = asset('assets/dist/img/full-logo.png');
                }

                $request->session()->put('companyLogo', $brandingLogo);
                $companyBrandingStatus             = (boolean) $company->is_branding;
                $brandingStatus                    = (boolean) $brandingData->status;
                $isServerDomainSame                = ($serverNameAsArray[0] == $brandingData->sub_domain);

                if (!$isServerDomainSame && !in_array($serverNameAsArray[0], $platformDomain)) {
                    Auth::guard()->logout();
                    $request->session()->invalidate();
                    $messageData = [
                        'data'   => 'You are not authorized to login from here.',
                        'status' => 2,
                    ];
                    return redirect(App::currentLocale().'/login')->withInput()->with('message', $messageData);
                }
                $redirectUrl = "/";
                if (app()->environment() == "local") {
                    $redirectUrl = "http://" . $_COOKIE["callingUrl"];
                } else {
                    $redirectUrl = "https://" . $_COOKIE["callingUrl"];
                }

                $domain = getDefaultDomain();
                if ($_COOKIE["callingUrl"] != $domain) {
                    $encryptData['domain'] = $_COOKIE["callingUrl"];
                    $encryptData['email']  = $user->email;

                    setcookie("azureAdAuth", encrypt(json_encode($encryptData)), time() + 3600, "/", $domain);
                }
                return redirect()->intended($redirectUrl);
            } else {
                return redirect()->intended("/");
            }
        } catch (RequestException $e) {
            abort(403, "User is not authorized within Azure AD to access this application.");
            return $this->fail($request, $e);
        }
        // return parent::success($request, $access_token, $refresh_token, $profile);
    }

    /**
     * Handler that is called when a failed handshake has taken place
     *
     * @param \Illuminate\Http\Request $request
     * @param \GuzzleHttp\Exception\RequestException $e
     * @return string
     */
    protected function fail(Request $request, RequestException $e)
    {
        if ($request->isMethod('get')) {
            $errorDescription = trim(substr($request->query('error_description', 'SOMETHING_ELSE'), 0, 11));
            if ($errorDescription == "AADSTS50105") {
                abort(403, "User is not authorized within Azure AD to access this application.");
            }
            abort(500, 'Something went wrong.!');
        }

        return implode("", explode(PHP_EOL, $e->getMessage()));
    }

    /**
     * Handler that is called every request when a user is logged in
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param String $access_token
     * @param String $refresh_token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    protected function handlecallback(Request $request, Closure $next, $access_token, $refresh_token)
    {
        return $next($request);

        // $user = Auth::user();

        // $user->updated_at = Carbon::now();

        // $user->save();

        // return parent::handlecallback($request, $next, $access_token, $refresh_token);
    }

    /**
     * Gets the logout url
     *
     * @return String
     */
    public function getLogoutUrl()
    {
        return $this->baseUrl . "common" . $this->route . "logout?post_logout_redirect_uri=" . config('app.url');
    }

    /**
     * Redirects to the Azure logout route.  Typically used to point a web route to this method.
     * For example: Route::get('/logout/azure', '\RootInc\LaravelAzureMiddleware\Azure@azurelogout');
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function azurelogout(Request $request)
    {
        $request->session()->pull('azure_access_token');
        $request->session()->pull('azure_refresh_token');

        return redirect()->away($this->getLogoutUrl());
    }
}
