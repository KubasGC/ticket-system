<?php

namespace App\Http\Middleware;

use App\Libraries\AppLibrary;
use Closure;
use Kubas\Oauth\OauthProvider;

class GenerateMenus
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
        \Menu::make('LSVRPNav', function ($menu) {
            $menu->add('<em class="fa fa-home">&nbsp;</em> Strona główna', ['route' => 'index']);
            $menu->add('<em class="fa fa-list">&nbsp;</em> Twoje zgłoszenia', ['route' => 'ticket.list'])->active("ticket/show/*");
            $menu->add('<em class="fa fa-plus-square">&nbsp;</em> Nowe zgłoszenie', ['route' => 'ticket.new']);
            if (AppLibrary::CheckAdminPerm(OauthProvider::Instance()->getUserInfo()->toArray()['group']))
            {
                $menu->add('<em class="fa fa-wrench">&nbsp;</em> Podgląd zgłoszeń', ['route' => 'ticket.admin']);
            }
        });
        return $next($request);
    }
}
