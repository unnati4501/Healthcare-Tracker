<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use LightSaml\Binding\SamlPostResponse;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\Protocol\LogoutRequest;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class AppSaml
{
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
        if (!in_array(ZC_SERVER_SUBDOMAIN, explode(',', config('saml.subdomains')))) {
            return $next($request);
        }

        $SAMLResponse  = $request->session()->get('azure_saml_token');
        $SAMLTokenTime = $request->session()->get('azure_saml_time');

        if (!empty($SAMLResponse) && !empty($SAMLTokenTime)) {
            $SAMLExpiryTime = Carbon::parse(decrypt($SAMLTokenTime))->toDateTimeString();
            $currentTime    = Carbon::now()->toDateTimeString();

            if ($SAMLExpiryTime > $currentTime) {
                return $next($request);
            }

            Auth::guard()->logout();
            $request->session()->invalidate();
            return $this->authnRequest(true);
        }
        return $next($request);
    }

    /**
     * SAML login request to IDP Azure AD.
     *
     * @param Request $request
     * @return targets IDP with auth request
     */
    public function loginRequest(Request $request)
    {
        $isPassive = !empty($request->isPassive) ? $request->isPassive : null;
        return $this->authnRequest($isPassive);
    }

    /**
     * Common Authn Request for login and silent login
     *
     * @param $isPassive
     * @return targets IDP with auth request
     */
    public function authnRequest($isPassive = false)
    {
        try {
            // Build auth request
            $authnRequest = new \LightSaml\Model\Protocol\AuthnRequest();
            $authnRequest
                ->setAssertionConsumerServiceURL(config('saml.callback_url'))
                ->setProtocolBinding(\LightSaml\SamlConstants::BINDING_SAML2_HTTP_POST)
                ->setID(\LightSaml\Helper::generateID())
                ->setIssueInstant(new \DateTime())
                ->setDestination(config('saml.destination_url'))
                ->setIssuer(new \LightSaml\Model\Assertion\Issuer(config('saml.entity_id')))
                ->setIsPassive($isPassive);

            // Add self signed certificates to auth request
            $certificate = X509Certificate::fromFile(storage_path('certs/saml.crt'));
            $privateKey  = KeyHelper::createPrivateKey(storage_path('certs/saml.pem'), '', true, XMLSecurityKey::RSA_SHA256);
            $authnRequest->setSignature(new \LightSaml\Model\XmlDSig\SignatureWriter($certificate, $privateKey));

            $serializationContext = new \LightSaml\Model\Context\SerializationContext();
            $authnRequest->serialize($serializationContext->getDocument(), $serializationContext);

            $bindingFactory  = new \LightSaml\Binding\BindingFactory();
            $redirectBinding = $bindingFactory->create(\LightSaml\SamlConstants::BINDING_SAML2_HTTP_REDIRECT);

            $messageContext = new \LightSaml\Context\Profile\MessageContext();
            $messageContext->setMessage($authnRequest);

            $httpResponse = $redirectBinding->send($messageContext);
            // redirect to IDP with SAMLRequest
            return \Redirect::to($httpResponse->getTargetUrl());
        } catch (\Exception $e) {
            $message = config('app.env') == 'local' ? $e->getMessage() : 'Something went wrong.!';
            abort(500, $message);
        }
    }

    /**
     * Handles SAML callback from IDP with SAMLResponse
     *
     * @param Request $request
     * @return logins user and redirects to dashboard
     */
    public function loginCallback(Request $request)
    {
        try {
            // capture and decode saml response
            $SAMLResponse       = $request->SAMLResponse;
            $decodeSAMLResponse = base64_decode($SAMLResponse);

            $deserializationContext = new \LightSaml\Model\Context\DeserializationContext();
            $deserializationContext->getDocument()->loadXML($decodeSAMLResponse);

            $authnRequest = new \LightSaml\Model\Protocol\Response();
            $authnRequest->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);

            // idp cert
            $subdomain = ZC_SERVER_SUBDOMAIN;
            $disk      = config('medialibrary.disk_name');
            $path      = config("medialibrary.{$disk}.domain");
            $crt       = '/static_media/saml_sso_certs/' . $subdomain . '.crt';

            if (!file_exists(public_path() . '/certs/idp/' . $subdomain . '.crt')) {
                \Storage::disk('media')->put('certs/idp/' . $subdomain . '.crt', file_get_contents($path . $crt));
            }

            $key = \LightSaml\Credential\KeyHelper::createPublicKey(
                \LightSaml\Credential\X509Certificate::fromFile(public_path() . '/certs/idp/' . $subdomain . '.crt')
            );
            // validate saml response signature with idp cert.
            $signatureReader = $authnRequest->getFirstAssertion()->getSignature();
            $ok              = $signatureReader->validate($key);

            if (!$ok) {
                abort(403, "IDP signature not valid.");
            }

            // get email from SAML assetions
            foreach ($authnRequest->getFirstAssertion()->getFirstAttributeStatement()->getAllAttributes() as $attribute) {
                if (Str::contains($attribute->getName(), 'claims/givenname')) {
                    $firstName = $attribute->getFirstAttributeValue();
                }
                if (Str::contains($attribute->getName(), 'claims/surname')) {
                    $lastName = $attribute->getFirstAttributeValue();
                }
                if (Str::contains($attribute->getName(), 'claims/name')) {
                    $email = $attribute->getFirstAttributeValue();
                    $user  = User::where('email', $email)->first();
                }
                if (Str::contains($attribute->getName(), 'claims/objectidentifier')) {
                    $id = $attribute->getFirstAttributeValue();
                }
                if (Str::contains($attribute->getName(), 'claims/displayname') && empty($firstName)) {
                    $firstName = $attribute->getFirstAttributeValue();
                }
            }

            $SAMLTokenTime = encrypt($authnRequest->getFirstAssertion()->getConditions()->getNotOnOrAfterString());
            // $SAMLTokenTime = encrypt(Carbon::now()->addMinutes(1)->toDateTimeString());

            $previousUrl = isset($request->getSession()->all()['_previous']) && !Str::contains($request->getSession()->all()['_previous']['url'], '/login/saml') ? $request->getSession()->all()['_previous']['url'] : '';

            if (!empty($request->getSession()->all()) && isset($request->getSession()->all()['_previous']) && isset($request->getSession()->all()['_previous']['url']) && Str::contains($request->getSession()->all()['_previous']['url'], '?device=mobile')) {
                $data = [
                    'SAMLResponse'  => $SAMLResponse,
                    'SAMLTokenTime' => $SAMLTokenTime,
                    'firstName'     => $firstName,
                    'lastName'      => !empty($lastName) ?? $lastName,
                    'email'         => $email,
                    'id'            => $id,
                ];

                return \Redirect::route('saml.appcallback')->with('data', $data);
            }

            // check if user exists in system
            if (!$user) {
                throw new \ErrorException('User is not authorized within system to access this application.', 403);
            } else {
                $request->session()->put('azure_saml_token', $SAMLResponse);
                $request->session()->put('azure_saml_time', $SAMLTokenTime);
                Auth::login($user, true);
                return redirect()->intended($previousUrl);
            }
        } catch (\Exception $e) {
            if ($e->getCode() == 403) {
                abort(403, $e->getMessage());
            }
            $message = config('app.env') == 'local' ? $e->getMessage() : 'Something went wrong.!';
            abort(500, $message);
        }
    }

    /**
     * Handles SAML logout request
     *
     * @return logs out user from AD
     */
    public function logoutRequest(Request $request)
    {
        if ($request->device == 'mobile') {
            // $SAMLResponse = $request->headers->get('Authorization');
            $nameId = $request->email;
        } else {
            // $SAMLResponse = \Session::get('data');
            $nameId = \Session::get('email');
        }

        if (empty($nameId)) {
            return redirect()->intended("/");
        }

        // $decodeSAMLResponse = base64_decode($SAMLResponse);

        // $deserializationContext = new \LightSaml\Model\Context\DeserializationContext();
        // $deserializationContext->getDocument()->loadXML($decodeSAMLResponse);

        // $authnRequest = new \LightSaml\Model\Protocol\Response();
        // $authnRequest->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);

        // $nameId = $authnRequest->getFirstAssertion()->getSubject()->getNameID();

        $logoutRequest = new LogoutRequest();
        $destination   = config('saml.logout_url');

        $logoutRequest
            ->setNameID(new NameID(
                $nameId,
                'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'
            ))
            // ->setNameID($nameId)
            ->setID(\LightSaml\Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination(config('saml.destination_url'))
            ->setIssuer(new \LightSaml\Model\Assertion\Issuer(config('saml.entity_id')));

        $serializationContext = new SerializationContext();
        $logoutRequest->serialize($serializationContext->getDocument(), $serializationContext);
        $XMLrequest = $serializationContext->getDocument()->saveXML();
        $reponse    = new SamlPostResponse($destination, ['SAMLRequest' => base64_encode($XMLrequest)]);
        $reponse->renderContent();
        return $reponse;
    }

    /**
     * JSON response for mobile request
     *
     * @return json
     */
    public function loginAppCallback()
    {
        $data = \Session::get('data');
        return response()->json($data);
    }

    /**
     * JSON response for garmin ping callback
     *
     * @return json
     */
    public function garminPingcallback(Request $request)
    {
        $payload = $request->all();
        return response()->json($payload);
    }
}
