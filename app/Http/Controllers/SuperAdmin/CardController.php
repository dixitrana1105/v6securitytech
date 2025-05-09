<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reader;
use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Building_Master;


class CardController extends Controller
{
   public function index_card(Request $request)
    {
        $status = $request->input('assign_status');
        $query = Card::with('building')->latest();

        if ($status !== null) {
            $query->where('assign_status', $status);
        }

        $cards = $query->paginate(10);

        return view('super-admin.card.index', compact('cards'));
    }

    public function create_card()
    {
        $buildings = Building_Master::all();
        return view('super-admin.card.create', compact('buildings'));
    }

    public function store_card(Request $request)
    {
        // dd($request);
        $request->validate([
            'building_id' => 'required',
            'serial_id' => 'required',
            'assign_status' => 'required|in:assigned,unassigned',
        ]);

        Card::create($request->all());

        return redirect()->route('super-admin.card-index')->with('success', 'Card created successfully.');
    }

    public function show(Card $card)
    {
        return view('super-admin.card.show', compact('card'));
    }

     public function edit_card($id)
    {
        // dd($id);
        $card = Card::findOrFail($id);
        $buildings = Building_Master::all();
        // dd($card);

        return view('super-admin.card.edit', compact('card', 'buildings'));

    }

    public function update(Request $request, $id)
    {
        $card = Card::findOrFail($id);

        $request->validate([
            'building_id' => 'required',
            'serial_id' => 'required|unique:cards,serial_id,' . $card->id,
            'assign_status' => 'required|in:assigned,unassigned',
        ]);

        $card->update($request->all());

        return redirect()->route('super-admin.card-index')->with('success', 'Card updated successfully.');
    }

    public function destroy(Card $card)
    {
        $card->delete();
        return redirect()->route('super-admin.card-index')->with('success', 'Card deleted successfully.');
    }

}

?>
