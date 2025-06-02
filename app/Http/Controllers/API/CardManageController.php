<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Visitor_Master;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CardManageController extends Controller
{
    public function getCard(Request $request)
    {
        $request->validate([
            'building_id' => 'required|integer|exists:cards,building_id',
        ]);

        $buildingId = $request->input('building_id');

        $cards = Card::where('building_id', $buildingId)
        ->where('assign_status', 'unassigned')
            ->latest()
            ->select('serial_id', 'id')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $cards,
        ]);
    }

    public function visitor_card_assign_api(Request $request)
    {
        $id = $request->id;

        // Step 1: Validate request
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|exists:cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // dd($id);
        $visitor = Visitor_Master::find($id);

        if (! $visitor) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Visitor not found.',
            ], 404);
        }

        // Step 3: Update Visitor
        $visitor->card_id     = $request->card_id;
        $visitor->card_status = "Card Assigned";
        $visitor->save();

        // Step 4: Update Card
        Card::where('id', $request->card_id)->update([
            'assign_status' => "assigned",
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Visitor Card assigned successfully',
            'data'    => $visitor,
        ]);
    }

    public function getSerialId(Request $request)
    {
        $cardId = $request->query('card_id');

        $card = Card::find($cardId);

        if ($card) {
            return response()->json([
                'serial_id' => $card->serial_id,
            ]);
        } else {
            return response()->json([
                'error' => 'Card not found',
            ], 404);
        }
    }

}
