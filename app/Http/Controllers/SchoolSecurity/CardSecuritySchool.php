<?php

namespace App\Http\Controllers\SchoolSecurity;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\ReadersLog;
use App\Models\SchoolSecurityVisitor;
use App\Services\BlockTenantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardSecuritySchool extends Controller
{

    protected $blockTenantService;

    public function __construct(BlockTenantService $blockTenantService)
    {
        $this->blockTenantService = $blockTenantService;
    }

   public function index_cards(Request $request)
    {
        $status = $request->input('assign_status');
        $building_id = Auth::guard('schoolsecurity')->user()->added_by;

        $query = Card::where('building_id', $building_id)->with('building')->latest();


        if ($status !== null) {
            $query->where('assign_status', $status);
        }

        $cards = $query->paginate(10);

        return view('school-security.cards.index', compact('cards'));
    }

    
    public function card_history()
    {
        $building_id = Auth::guard('schoolsecurity')->user()->added_by;
        $building = Auth::guard('schoolsecurity')->user()->load('building')->building;
        $visitors = SchoolSecurityVisitor::with('card')->with('SchoolAdminSecurity')
            ->where('added_by', $building_id)
            ->whereDate('created_at', Carbon::today()) // Fetch records created today
            ->latest()->get();
        foreach ($visitors as $log) {
            $log->building = $building ?? null; // Attach the visitor manually
        }
        // dd($visitors);
        return view('school-security.cards.history', compact('visitors'));
    }

    public function alerts()
    {
        $building_id = Auth::guard('schoolsecurity')->user()->added_by;
        $building = Auth::guard('schoolsecurity')->user()->load('building')->building;
        $visitors = ReadersLog::with('card')
            ->where('building_id', $building_id)
            ->orderBy('timestamp', 'desc')
            ->get();

        foreach ($visitors as $log) {
            $visitor = SchoolSecurityVisitor::with('SchoolAdminSecurity')->where('id', $log->visitor_id)->first();
            $log->visitor = $visitor ?? null; // Attach the visitor manually
            $log->building = $building ?? null; // Attach the visitor manually
        }

        // dd($visitors);
        return view('school-security.cards.visitor-alert', compact('visitors'));
    }

    public function alert_status_change(Request $request, $id)
    {
        $request->validate([
            'is_read' => 'required',
        ]);

        // dd($request);

        $data = ReadersLog::findOrFail($id);
        $data->is_read = $request->is_read;
        $data->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}

?>
