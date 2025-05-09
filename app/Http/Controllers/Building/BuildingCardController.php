<?php

namespace App\Http\Controllers\Building;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Reader;
use App\Services\BlockTenantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildingCardController extends Controller
{

    protected $blockTenantService;

    public function __construct(BlockTenantService $blockTenantService)
    {
        $this->blockTenantService = $blockTenantService;
    }

    public function index_cards(Request $request)
    {
        $status = $request->input('assign_status');
        $bulding_id = Auth::guard('buildingadmin')->user()->id;

        $query = Card::where('building_id', $bulding_id)->with('building')->with('reader')->latest();

        if ($status !== null) {
            $query->where('assign_status', $status);
        }

        $cards = $query->paginate(10);

        return view('building-admin.cards.index', compact('cards'));
    }


    public function index_readers()
    {
        $bulding_id = Auth::guard('buildingadmin')->user()->id;

        $readers = Reader::where('building_id', $bulding_id)->with('building')->latest()->paginate(10);

        return view('building-admin.reader.index', compact('readers'));
    }
}

?>
