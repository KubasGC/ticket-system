<?php
/**
 * Created by PhpStorm.
 * User: Kubas
 * Date: 2018-08-27
 * Time: 08:24
 */

namespace App\Libraries;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Kubas\Oauth\OauthProvider;

class AppLibrary
{
    private static $adminIds = [4, 15, 10, 8];
    private static $forumInfoCache = array();

    /**
     * Returns string with ellipsis in the end of the string when string is larger than set chars chars.
     * @param $string
     * @return string
     */
    public static function AddEllipsis($string, $chars = 50, $withEllipsis = true)
    {
        $out = $string;
        if (strlen($string) > $chars)
        {
            $out = substr($string,0,$chars);
            if ($withEllipsis) $out.= "...";
        }
        return $out;
    }

    /**
     * Gets admins list.
     * @return \Illuminate\Support\Collection
     */
    public static function GetAdmins()
    {
        return DB::table("lsvrpcore_members")->whereRaw('member_group_id IN ('.implode(",", self::$adminIds).')')->orderBy('member_group_id', 'asc')->get();
    }

    /**
     * Checks for user grant access to the ticket.
     * @param $ticketId integer
     * @return boolean
     */
    public static function CheckPermissionsToTicket($ticketId)
    {
        $ticketsTable = DB::table("lsvrp_ticket_tickets")->where(['Id' => $ticketId])->first();
        if (!$ticketsTable) return false;

        if ($ticketsTable->Owner == OauthProvider::Instance()->getUserInfo()->toArray()['id']) return true;
        if (self::CheckAdminPerm(OauthProvider::Instance()->getUserInfo()->toArray()['group'])) return true;
        return false;
    }

    /**
     * Checks is ticket exists in database.
     * @param $ticketId integer
     * @return boolean
     */
    public static function DoesTicketExists($ticketId)
    {
        $ticketsTable = DB::table("lsvrp_ticket_tickets")->where(['Id' => $ticketId]);
        if ($ticketsTable->count() == 0) return false;
        return true;
    }

    /**
     * Returns true if group id has admin privileges, false otherwise.
     * @param $groupId
     * @return bool
     */
    public static function CheckAdminPerm($groupId)
    {
        \Debugbar::startMeasure('adminperm','CheckAdminPerm('.$groupId.')');
        $groupId = intval($groupId);
        if (in_array($groupId, self::$adminIds))
        {
            \Debugbar::stopMeasure('adminperm');
            return true;
        }
        \Debugbar::stopMeasure('adminperm');
        return false;
    }

    /**
     * Gets user info from forum REST Api.
     * @param $userId integer
     * @return array
     */
    public static function GetForumUserInfo($userId)
    {
        \Debugbar::startMeasure('forumuserinfo','GetForumUserInfo('.$userId.')');
        $userId = intval($userId);
        /*if (array_key_exists($userId, self::$forumInfoCache))
        {
            \Debugbar::stopMeasure('forumuserinfo');
            return self::$forumInfoCache[$userId];
        }*/
        if(Cache::has("forumUser_{$userId}"))
        {
            \Debugbar::info("Loaded {$userId} from cache.");
            \Debugbar::stopMeasure('forumuserinfo');
            return Cache::get("forumUser_{$userId}");
        }

        \Debugbar::info("User {$userId} is not loaded from cache.");

        $apiKey = '8fd09f45335523488d08d539fbee0255';
        $url = "https://lsvrp.pl/api/core/members/{$userId}?key={$apiKey}";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        curl_close($curl);
        $decoded = json_decode($curl_response);

        if (property_exists($decoded, 'errorCode'))
        {
            $decoded->photoUrl = 'https://via.placeholder.com/60x60';
            $decoded->name = 'Nieznany użytkownik';
        }

        // self::$forumInfoCache[$userId] = $decoded;
        Cache::put("forumUser_{$userId}", $decoded, 30);
        \Debugbar::stopMeasure('forumuserinfo');
        return $decoded;
    }

    /**
     * Gets status name from status id.
     * @param $status integer
     * @return string
     */
    public static function GetStatusName($status)
    {
        switch ($status)
        {
            default: return "Nieznany";
            case 0: return "Oczekujące";
            case 1: return "Przyjęte";
            case 2: return "W realizacji";
            case 3: return "Zakończone";
        }
    }
}