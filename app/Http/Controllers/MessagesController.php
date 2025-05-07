<?php
namespace App\Http\Controllers;

use App\Models\BuildingAdminTenant;
use App\Models\FcmUser;
use App\Models\Security_Master;
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    public function fetchMessages(Request $request)
    {
        $days = 7;
        $dateThreshold = Carbon::now()->subDays($days);

        $sender_id = (int) $request->sender_id;
        $receiver_id = (int) $request->reciever_id;

        $sender_type = $request->sender_type ?? Message::SENDER_TYPES['tenant'];
        $receiver_type = $request->receiver_type ?? Message::RECEIVER_TYPES['tenant'];

        $messages = Message::where('created_at', '>=', $dateThreshold)
            ->where('sender_type', $sender_type)
            ->where('receiver_type', $receiver_type)
            ->where(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $sender_id)
                      ->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $receiver_id)
                      ->where('receiver_id', $sender_id);
            })->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }
    public function sendMessage(Request $request)
{
    // Determine the logged-in user and their type
    if (Auth::guard('buildingtenant')->check()) {
        $user_id = Auth::guard('buildingtenant')->user()->id;
        $sender_type = Message::SENDER_TYPES['tenant']; // Sender is a tenant
    } elseif (Auth::guard('buildingSecutityadmin')->check()) {
        $user_id = Auth::guard('buildingSecutityadmin')->user()->id;
        $sender_type = Message::SENDER_TYPES['security']; // Sender is security
    } else {
        return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
    }

    // Determine the receiver type based on sender type
    $receiver_type = $request->receiver_type ?? (
        $sender_type == Message::SENDER_TYPES['tenant'] ? Message::RECEIVER_TYPES['security'] : Message::RECEIVER_TYPES['tenant']
    );

    // Store the message
    $message = Message::create([
        'receiver_id' => $request->receiver_id,
        'sender_id' => $user_id,
        'sender_type' => $sender_type,
        'receiver_type' => $receiver_type,
        'message' => $request->message,
    ]);
    
    $this->sendNotificationToReciever($message);
    return response()->json(['success' => true, 'message' => $message]);
}

    public function sendNotificationToReciever($message)
    {
        
        $reciever_id = $message->receiver_id;
        $sender_id = $message->sender_id;
        $sender_type = $message->sender_type;
        $receiver_type = $message->receiver_type;
        $receiver_building_type = 'building';
        $fcm_reciever_type = $this->fcmRecieverType($receiver_type,$receiver_building_type);
        // $fcm_user_data = FcmUser::where('user_id', $reciever_id)->where('user_type', $fcm_reciever_type)->get();
        $fcm_user_data = FcmUser::where('user_id', 8)->where('user_type', $fcm_reciever_type)->get();

        foreach ($fcm_user_data as $fcm_user) {
            $fcm_data = [
                'user_table_id' => $fcm_user->id,
                'title' => 'New Message',
                'body' => $message->message,
            ];
            (new FcmController())->sendFcmNotification($fcm_data);
        }
    }
    private function fcmRecieverType($receiver_type,$receiver_building_type)
    {
        if ($receiver_type == Message::RECEIVER_TYPES['tenant']) {
            $fcm_reciever_type = $receiver_building_type . '_tenant';
        } elseif ($receiver_type == Message::RECEIVER_TYPES['security']) {
            $fcm_reciever_type = $receiver_building_type . '_security';
        }
        return $fcm_reciever_type;
    }
    public function redirectToMessagePage(Request $request)
    {

        $reciever_id = intval(decrypt($request->reciever_id));
        $sender_id = intval(decrypt($request->sender_id));
        $sender_type = $request->sender_type;
        $receiver_type = $request->receiver_type;

        $data['receiver_details'] = $this->getUserDataBasedOnType($request->receiver_type, $reciever_id);
        $data['sender_details'] = $this->getUserDataBasedOnType($request->sender_type, $sender_id);

        return view('building-tenant.tenant.send-message', compact('reciever_id','sender_id','sender_type','receiver_type','data'));
    }

    public function getUserDataBasedOnType($type, $id)
    {

        $user = null;
        if ($type == Message::SENDER_TYPES['tenant']) {
            $user = BuildingAdminTenant::find($id);
        } else if ($type == Message::SENDER_TYPES['security']) {
            $user = Security_Master::find($id);
        }
        return $user;
    }
}
