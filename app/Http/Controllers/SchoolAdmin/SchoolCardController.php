<?php

namespace App\Http\Controllers\SchoolAdmin;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Reader;
use App\Services\BlockTenantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolCardController extends Controller
{

    protected $blockTenantService;

    public function __construct(BlockTenantService $blockTenantService)
    {
        $this->blockTenantService = $blockTenantService;
    }

    public function index_cards()
    {
        $bulding_id = Auth::guard('buildingadmin')->user()->id;

        $cards = Card::where('building_id', $bulding_id)->with('building')->latest()->paginate(10);

        return view('school-admin.cards.index', compact('cards'));
    }

    public function index_readers()
    {
        $bulding_id = Auth::guard('buildingadmin')->user()->id;

        $readers = Reader::where('building_id', $bulding_id)->with('building')->latest()->paginate(10);

        return view('school-admin.reader.index', compact('readers'));
    }
}

?>