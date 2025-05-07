<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FcmController;
use App\Models\FcmUser;
use App\Models\Notification;
use App\Models\Security_Master;
use App\Models\Visitor_Master;
use Illuminate\Http\Request;

class AddStatusfromtenenat extends Controller
{
    public function addStatusVisitor(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'building_id' => 'required|string',
                'visitor_id' => 'required|exists:visitor_master,visitor_id',
                'actionType' => 'required|string|in:0,1,2',
                'proveRemark' => 'nullable|string',
                'rejectRemark' => 'nullable|string',
                'resedualRemark' => 'nullable|string',
                'resedualDate' => 'nullable|date',
            ]);

            $record = Visitor_Master::where('visitor_id', $validatedData['visitor_id'])->first();

            if (! $record) {
                return response()->json(['error' => 'Visitor record not found.'], 404);
            }

            $actionType = $validatedData['actionType'];

            switch ($actionType) {
                case '0':
                    if (empty($validatedData['proveRemark'])) {
                        return response()->json(['error' => 'Prove remark is required for this action.'], 422);
                    }
                    $record->status_of_visitor = 0;
                    $record->visitor_remark = $validatedData['proveRemark'];
                    break;

                case '1':
                    if (empty($validatedData['rejectRemark'])) {
                        return response()->json(['error' => 'Reject remark is required for this action.'], 422);
                    }
                    $record->status_of_visitor = 1;
                    $record->visitor_remark = $validatedData['rejectRemark'];
                    break;

                case '2':
                    if (empty($validatedData['resedualRemark']) || empty($validatedData['resedualDate'])) {
                        return response()->json(['error' => 'Both resedual remark and date are required for this action.'], 422);
                    }
                    $record->status_of_visitor = 2;
                    $record->visitor_remark = $validatedData['resedualRemark'];
                    $record->reschedule_date = $validatedData['resedualDate'];
                    break;

                default:
                    return response()->json(['error' => 'Invalid action type.'], 400);
            }

            $record->save();
            $this->sendNotificationToSecurityForUpdate($record);

            return response()->json(['message' => 'Data saved successfully.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.', 'details' => $e->getMessage()], 500);
        }
    }

    public function sendNotificationToSecurityForUpdate($record)
    {
        // dd('ok');
        $bulding_id = $record->building_id;
        $active_security_ids = $this->getSecurityIds($bulding_id);
        $message = $record->full_name."'s status has been updated by user";

        $receiver_type = 2;
        $receiver_building_type = 'building';
        $receiver_building_type_int = 1;
        $this->createNotificationForRecord($record, $active_security_ids, $receiver_type, $receiver_building_type_int);
        $fcm_user_data = FcmUser::whereIn('user_id', $active_security_ids)->where('user_type', 'building_security')->get();

        foreach ($fcm_user_data as $fcm_user) {
            $fcm_data = [
                'user_table_id' => $fcm_user->id,
                'title' => 'Visitor Status Update',
                'body' => $message,
            ];
            (new FcmController)->sendFcmNotification($fcm_data);
        }
    }

    public function createNotificationForRecord($record, $active_security_ids, $receiver_type, $receiver_building_type_int)
    {
        foreach ($active_security_ids as $security_id) {
            Notification::create([
                'notification_master_id' => 2,
                'for_user_id' => $security_id,
                'for_user_type' => $receiver_type,
                'for_building_type' => $receiver_building_type_int,
                'variable_data' => ['visitor_name' => $record->full_name],
                'is_read' => 0,
            ]);
        }
    }

    private function getSecurityIds($building_id)
    {
        $security_ids = Security_Master::where('building_id', $building_id)->where('status', 1)->pluck('id')->toArray();

        return $security_ids;
    }
}
