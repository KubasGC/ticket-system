<?php

namespace App\Http\Controllers;

use App\Libraries\AppLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kubas\Oauth\OauthProvider;

class TicketController extends Controller
{
    public function newTicket()
    {
        return view('tickets.new');
    }

    public function listTicket()
    {
        $tickets = DB::table("lsvrp_ticket_tickets")->where(['Owner' => OauthProvider::Instance()->getUserInfo()->toArray()['id']])->get();
        return view('tickets.list', ['tickets' => $tickets]);
    }

    public function addTicket(Request $request)
    {
        $title = $request->get('title');
        $message = $request->get('message');

        if ($title == null || strlen($title) < 4 || $message == null || strlen($message) < 4) return view('error', ['errno' => 500, 'err' => 'Nie wypełniłeś wszystkich pól']);

        $ticketId = DB::table("lsvrp_ticket_tickets")->insertGetId([
            'Title' => AppLibrary::AddEllipsis($title, 255, false),
            'Owner' => OauthProvider::Instance()->getUserInfo()->toArray()['id'],
            'Timestamp' => time(),
            'Admin' => 0,
            'Status' => 0
        ]);

        DB::table("lsvrp_ticket_messages")->insert([
            'Ticket' => $ticketId,
            'User' => OauthProvider::Instance()->getUserInfo()->toArray()['id'],
            'Content' => $message,
            'Timestamp' => time()
        ]);

        return redirect()->route('ticket.show', ['id' => $ticketId]);
    }

    public function adminList()
    {
        if (!AppLibrary::CheckAdminPerm(OauthProvider::Instance()->getUserInfo()->toArray()['group'])) return view('error', ['errno' => 403, 'err' => 'Brak uprawnień']);

        $tickets = DB::table("lsvrp_ticket_tickets")->orderBy('Id', 'desc')->get();
        $yTickets = DB::table("lsvrp_ticket_tickets")->where([
            ['Admin', '=', OauthProvider::Instance()->getUserInfo()->toArray()['id']],
            ['Status', '<>', 3]
        ])->orderBy('Id', 'desc')->get();

        return view('tickets.admin', ['tickets' => $tickets, 'yTickets' => $yTickets]);
    }

    public function showTicket($id)
    {
        \Debugbar::startMeasure('showTicket', 'showTicket');
        if (!AppLibrary::DoesTicketExists($id)) return view('error', ['errno' => 404, 'err' => 'Nie ma takiego zgłoszenia']);
        if (!AppLibrary::CheckPermissionsToTicket($id)) return view('error', ['errno' => 403, 'err' => 'Brak uprawnień']);

        $ticketData = DB::table("lsvrp_ticket_tickets")->where(['Id' => $id])->first();
        $adminName = 'Brak';
        if ($ticketData->Admin != 0)
        {
            $adminInfo = AppLibrary::GetForumUserInfo($ticketData->Admin);
            $adminName = $adminInfo->name;
        }

        $ticketMessages = DB::table("lsvrp_ticket_messages")->where(['Ticket' => $ticketData->Id])->orderBy('Id', 'desc')->get();
        foreach ($ticketMessages as $key => $value)
        {
            if ($value->User == 0) continue;
            $ticketMessages[$key]->forumInfo = AppLibrary::GetForumUserInfo($value->User);
        }
        \Debugbar::stopMeasure('showTicket');
        return view('tickets.show', ['ticketData' => $ticketData, 'adminName' => $adminName, 'ticketMessages' => $ticketMessages]);
    }

    public function addMessage(Request $request, $id)
    {
        if (!AppLibrary::DoesTicketExists($id)) return view('error', ['errno' => 404, 'err' => 'Nie ma takiego zgłoszenia']);
        if (!AppLibrary::CheckPermissionsToTicket($id)) return view('error', ['errno' => 403, 'err' => 'Brak uprawnień']);

        $ticketData = DB::table("lsvrp_ticket_tickets")->where(['Id' => $id])->first();

        $message = $request->get('message');
        if ($message == null || strlen($message) < 4) return view('error', ['errno' => 500, 'err' => 'Wiadomość jest zbyt krótka']);

        DB::table("lsvrp_ticket_messages")->insert([
            'Ticket' => $ticketData->Id,
            'User' => OauthProvider::Instance()->getUserInfo()->toArray()['id'],
            'Content' => $message,
            'Timestamp' => time()]);

        return redirect()->route('ticket.show', ['id' => $id]);
    }

    public function saveAdmin(Request $request, $id)
    {
        if (!AppLibrary::DoesTicketExists($id)) return view('error', ['errno' => 404, 'err' => 'Nie ma takiego zgłoszenia']);
        if (!AppLibrary::CheckPermissionsToTicket($id)) return view('error', ['errno' => 403, 'err' => 'Brak uprawnień']);

        $ticketData = DB::table("lsvrp_ticket_tickets")->where(['Id' => $id])->first();

        $newAdmin = $ticketData->Admin;
        $newStatus = $ticketData->Status;

        $status = intval($request->get('changeStatus'));
        $changeAdmin = $request->get('changeAdmin');
        if ($changeAdmin != null && $changeAdmin != "null") $newAdmin = intval($changeAdmin);
        if ($status > -1)
        {
            $statuses = [0, 1, 2, 3];
            if (in_array($status, $statuses))
            {
                $newStatus = $status;
            }
        }

        $adminChanged = false;
        $statusChanged = false;

        $adminInfo = AppLibrary::GetForumUserInfo(OauthProvider::Instance()->getUserInfo()->toArray()['id']);
        $message = "Dane zgłoszenia zostały zmienione przez {$adminInfo->name}.";
        if ($ticketData->Status != $newStatus)
        {
            $oldName = AppLibrary::GetStatusName($ticketData->Status);
            $newName = AppLibrary::GetStatusName($newStatus);
            $message.= " Status: [{$oldName} => {$newName}]";
            $statusChanged = true;
        }
        if ($ticketData->Admin != $newAdmin)
        {

            $oldAdminInfo = AppLibrary::GetForumUserInfo($ticketData->Admin);
            $newAdminInfo = AppLibrary::GetForumUserInfo($newAdmin);
            $message.= " Administrator: [{$oldAdminInfo->name} => {$newAdminInfo->name}]";
            $adminChanged = true;
        }

        if ($adminChanged || $statusChanged)
        {
            DB::table("lsvrp_ticket_tickets")->where(['Id' => $ticketData->Id])
                ->update(['Admin' => $newAdmin, 'Status' => $newStatus]);
            DB::table("lsvrp_ticket_messages")->insert(['Ticket' => $ticketData->Id, 'User' => 0, 'Content' => $message, 'Timestamp' => time()]);
        }

        return redirect()->route('ticket.show', ['id' => $id]);
    }
}
