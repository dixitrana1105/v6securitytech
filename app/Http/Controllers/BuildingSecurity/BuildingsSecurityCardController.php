<?php

namespace App\Http\Controllers\BuildingSecurity;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Visitor_Master;
use App\Services\BlockTenantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildingsSecurityCardController extends Controller
{

    protected $blockTenantService;

    public function __construct(BlockTenantService $blockTenantService)
    {
        $this->blockTenantService = $blockTenantService;
    }

    public function index_cards()
    {
        $building_id = Auth::guard('buildingSecutityadmin')->user()->building_id;

        $cards = Card::where('building_id', $building_id)->with('building')->latest()->paginate(10);

        return view('building-security.cards.index', compact('cards'));
    }

    public function card_history()
    {
        $building_id = Auth::guard('buildingSecutityadmin')->user()->building_id;
        $visitors = Visitor_Master::with('card')->with('Building_Master')
            ->where('building_id', $building_id)
            ->whereDate('created_at', Carbon::today()) // Fetch records created today
            ->get();
        return view('building-security.cards.history', compact('visitors'));
    }
}

?>