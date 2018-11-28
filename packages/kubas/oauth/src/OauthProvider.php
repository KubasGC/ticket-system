<?php
/**
 * Created by PhpStorm.
 * User: Kubas
 * Date: 2018-08-20
 * Time: 10:29
 */

namespace Kubas\Oauth;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Request as R2;

class OauthProvider
{

    /** @var \League\OAuth2\Client\Provider\GenericProvider */
    private $m_provider;

    /*
     * Singleton
     */
    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new OauthProvider();
        }
        return $inst;
    }

    public function getProvider()
    {
        return $this->m_provider;
    }

    private function __construct()
    {

    }

    public function SetProvider()
    {
        $this->m_provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => '7b4cda9f11a0d07d5774',    // The client ID assigned to you by the provider
            'clientSecret'            => '07d8c728970096b3c6aacdbb26f596c6',   // The client password assigned to you by the provider
            'redirectUri'             => 'https://ticket.lsvrp.pl/kubas/oauth/callback',
            'urlAuthorize'            => 'https://lsvrp.pl/applications/oauth2server/interface/oauth/authorize.php',
            'urlAccessToken'          => 'https://lsvrp.pl/applications/oauth2server/interface/oauth/token.php',
            'urlResourceOwnerDetails' => 'https://lsvrp.pl/applications/oauth2server/interface/oauth/me.php',
        ]);
    }

    public function SetAuth(Request $request)
    {
        if (!$request->has("state") || $request->get("state") !== $request->session()->get("oauth_session_key_state"))
        {
            return response("Niepoprawny status.", 400);
        }

        try {
            $accessToken = $this->m_provider->getAccessToken("authorization_code", [
                "code" => $request->get("code")
            ]);

            $resourceOwner = $this->m_provider->getResourceOwner($accessToken);
            // var_dump($resourceOwner->toArray());

            session()->put("oauth_session_code", $accessToken);
            session()->put("oauth_refresh_token", $accessToken->getRefreshToken());
            return redirect()->guest("https://ticket.lsvrp.pl");

        }
        catch (IdentityProviderException $e)
        {
            exit($e->getMessage());
        }
    }

    public function getUserInfo()
    {
        $authCode = session()->get("oauth_session_code");
        return $this->m_provider->getResourceOwner($authCode);
    }

    public function getUserRankName()
    {
        $groupName = DB::table("lsvrpcore_sys_lang_words")->where(['word_key' => 'core_group_'.$this->getUserInfo()->toArray()['group']]);
        if ($groupName->count() == 0) return "Użytkownik";
        return $groupName->first()->word_custom;
    }

}