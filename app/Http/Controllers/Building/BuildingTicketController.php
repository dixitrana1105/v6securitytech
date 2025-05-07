<?php

namespace App\Http\Controllers\Building;
use App\Models\BuildingAdminTenant;
use App\Models\BuildingAdminTicket;
use App\Models\BuildingSecurityTicket;
use App\Models\BuildingTenantTicket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildingTicketController extends Controller
{
    public function dashboard()
    {

        $tenant_new   = BuildingTenantTicket::where('role', 'building_admin')->whereNull('status_of_button')->count();
        $tenant_open  = BuildingTenantTicket::where('role', 'building_admin')->where('status_of_button', 0)->count();
        $tenant_hold  = BuildingTenantTicket::where('role', 'building_admin')->where('status_of_button', 1)->count();
        $tenant_close = BuildingTenantTicket::where('role', 'building_admin')->where('status_of_button', 2)->count();

// Count tickets from BuildingSecurityTicket
        $security_new   = BuildingSecurityTicket::where('role', 'building_admin')->whereNull('status_of_button')->count();
        $security_open  = BuildingSecurityTicket::where('role', 'building_admin')->where('status_of_button', 0)->count();
        $security_hold  = BuildingSecurityTicket::where('role', 'building_admin')->where('status_of_button', 1)->count();
        $security_close = BuildingSecurityTicket::where('role', 'building_admin')->where('status_of_button', 2)->count();

// Sum up counts for all statuses
        $total_new   = $tenant_new + $security_new;
        $total_open  = $tenant_open + $security_open;
        $total_hold  = $tenant_hold + $security_hold;
        $total_close = $tenant_close + $security_close;

// Count BuildingAdminTicket
        $my_ticket_count = BuildingAdminTicket::where('added_by', Auth::guard('buildingadmin')->user()->id)->count();

// Now calculate total tickets after defining all totals
        $total_tickets = $total_new + $total_open + $total_hold + $total_close + $my_ticket_count;

// Display results
        $data = [
            'New'           => $total_new,
            'Open'          => $total_open,
            'Hold'          => $total_hold,
            'Close'         => $total_close,
            'My Tickets'    => $my_ticket_count,
            'total_tickets' => $total_tickets,
        ];

        return view('building-admin.ticket.ticket-dashboard', compact('data'));
    }

    public function new_ticket()
    {
        $ticket_data_1 = BuildingTenantTicket::with('followUps')->get()->sortByDesc('created_at');
        $get_all_ticket_new = BuildingTenantTicket::with('building')->where('role', 'building_admin')->where('status_of_button', null)->get();
        $get_all_ticket_new_from_security = BuildingSecurityTicket::with('building')->where('role', 'building_admin')->where('status_of_button', null)->get();
        $ticket_data_1 = collect($ticket_data_1);
        $get_all_ticket_new_from_security = collect($get_all_ticket_new_from_security);
        $ticket_data = $get_all_ticket_new_from_security->merge($ticket_data_1);

        return view('building-admin.ticket.newticket', compact('get_all_ticket_new', 'get_all_ticket_new_from_security', 'ticket_data'));
    }

    public function open_ticket()
    {
        $ticket_data_1 = BuildingTenantTicket::with('followUps')->get()->sortByDesc('created_at');
        $get_all_ticket_new = BuildingTenantTicket::with('building')->where('role', 'building_admin')->where('status_of_button', 0)->get();
        $get_all_ticket_new_from_security = BuildingSecurityTicket::with('building', 'followUps')->where('role', 'building_admin')->where('status_of_button', 0)->get();

        $ticket_data_1 = collect($ticket_data_1);
        $get_all_ticket_new_from_security = collect($get_all_ticket_new_from_security);
        $ticket_data = $get_all_ticket_new_from_security->merge($ticket_data_1);

        // dd($ticket_data);

        return view('building-admin.ticket.openticket', compact('get_all_ticket_new', 'ticket_data', 'get_all_ticket_new_from_security'));

    }

    public function hold_ticket()
    {
        $ticket_data_1 = BuildingTenantTicket::with('followUps')->get()->sortByDesc('created_at');
        $get_all_ticket_new = BuildingTenantTicket::with('building')->where('role', 'building_admin')->where('status_of_button', 1)->get();
        $get_all_ticket_new_from_security = BuildingSecurityTicket::with('building', 'followUps')->where('role', 'building_admin')->where('status_of_button', 1)->get();

        // Convert to base Collection for compatibility
        $ticket_data_1 = collect($ticket_data_1);
        $get_all_ticket_new_from_security = collect($get_all_ticket_new_from_security);

        // Merge collections
        $ticket_data = $get_all_ticket_new_from_security->merge($ticket_data_1);

        return view('building-admin.ticket.holdticket', compact('get_all_ticket_new', 'ticket_data', 'get_all_ticket_new_from_security'));
    }

    public function close_ticket()
    {
        $ticket_data_1 = BuildingTenantTicket::with('followUps')->get()->sortByDesc('created_at');
        $get_all_ticket_new = BuildingTenantTicket::with('building')->where('role', 'building_admin')->where('status_of_button', 2)->get();
        $get_all_ticket_new_from_security = BuildingSecurityTicket::with('building', 'followUps')->where('role', 'building_admin')->where('status_of_button', 2)->get();

        $ticket_data = $get_all_ticket_new_from_security->merge($ticket_data_1);
        return view('building-admin.ticket.closeticket', compact('get_all_ticket_new', 'ticket_data', 'get_all_ticket_new_from_security'));
    }

    public function create_MyTicket()
    {
        $building_id = Auth::guard('buildingadmin')->user()->id;

        $lastTicket = BuildingAdminTicket::where('ticket_id', 'like', 'BAT' . $building_id . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = intval(substr($lastTicket->ticket_id, strlen('BAT' . $building_id)));
            $nextIndex = $lastNumber + 1;
        } else {
            $nextIndex = 1;
        }

        $ticketId = 'BAT' . $building_id . str_pad($nextIndex, 4, '0', STR_PAD_LEFT);

        return view('building-admin.ticket.myTicket-create', compact('ticketId'));
    }

    public function store_MyTicket(Request $request)
    {

        $validatedData = $request->validate([
            'ticket_id' => 'required|string|max:255',
            'date_time' => 'required|date',
            'subject' => 'required|string|max:255',
            'role' => 'required|string|max:50',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'description' => 'required|string',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $destinationPath = public_path('assets/images/');
            $attachmentFileName = time() . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move($destinationPath, $attachmentFileName);
            $attachmentPath = 'assets/images/' . $attachmentFileName;
        }
        // $building_id = Auth::guard('buildingadmin')->user()->id;
        $building_id = Auth::guard('buildingadmin')->user()->building_id;

        // dd($building_id);

        // dd()

        $ticket = BuildingAdminTicket::create([
            // 'building_id' => $building_id,
            'ticket_id' => $validatedData['ticket_id'],
            'date_time' => $validatedData['date_time'],
            'subject' => $validatedData['subject'],
            'role' => $validatedData['role'],
            'attachment' => $attachmentPath,
            'building_id' => $building_id,
            'description' => $validatedData['description'],
            'added_by' => Auth::guard('buildingadmin')->user()->id,
            'created_at' => now(),
        ]);

        return redirect()->route('building-admin.myTicket-index')->with('success', 'Ticket created successfully.');

    }

    public function index_MyTicket()
    {
        // dd($buildingAdminId);

        $ticket_data = BuildingAdminTicket::where('added_by', Auth::guard('buildingadmin')
                ->user()->id)
            ->with('followUps')
            ->get()
            ->sortByDesc('created_at');

        // $replay_for_followup = BuildingAdminTicketFollow::all();

        // dd($ticket_data);

        return view('building-admin.ticket.myTicket-index', compact('ticket_data'));
    }
}
