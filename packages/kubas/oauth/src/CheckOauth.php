<?php

namespace Kubas\Oauth;

use Closure;
use League\OAuth2\Client\Token\AccessToken;

class CheckOauth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Debugbar::startMeasure('oauth', 'OAuth');
        /** @var AccessToken $authCode */
        $authCode = $request->session()->get("oauth_session_code");
        if (!$authCode)
        {
            \Debugbar::stopMeasure('oauth');
            return $this->RedirectToAuth();
        }

        if ($authCode->hasExpired())
        {

            $newToken = OauthProvider::Instance()->getProvider()->getAccessToken("refresh_token", [
                "refresh_token" => $request->session()->get("oauth_refresh_token")
            ]);
            session()->put("oauth_session_code", $newToken);
            session()->put("oauth_refresh_token", $newToken->getRefreshToken());

        }

        \Debugbar::disable();
        if (OauthProvider::Instance()->getUserInfo()->toArray()['group'] == 4)
        {
            \Debugbar::enable();
        }

        \Debugbar::stopMeasure('oauth');
        return $next($request);
    }

    protected function RedirectToAuth()
    {
        $authUrl = OauthProvider::Instance()->getProvider()->getAuthorizationUrl();
        session()->put("oauth_session_key_state", OauthProvider::Instance()->getProvider()->getState());
        return redirect()->guest($authUrl);
    }
}
