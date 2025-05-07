<?php

namespace App\Http\Controllers\API;
use App\Models\BuildingAdminTenant;
use App\Models\Security_Master;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FcmController;
use App\Models\FcmUser;
use App\Models\Message;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiMessageController extends Controller
{
  public function getUserList(Request $request)
  {
    try{

        $status = $request->input('status') ?? 1;

        $user_id = $request->user_id;
        $building_id = BuildingAdminTenant::where('id', $user_id)->value('building_id');


        $query = BuildingAdminTenant::where('building_id',$building_id)->whereNot('id', $user_id);

        if ($status !== null) {
            $query->where('status', $status);
        }
        $tenant = $query->select('id','contact_number', 'contact_person','email','tenant_photo','flat_office_no')->get();

        return response()->json($tenant, 200);

    }catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function sendNewMessage(Request $request)
    {
        try {
            $receiver_user_type = $request->receiver_user_type ?? 'tenant';

            $validator = Validator::make($request->all(), [
                'reciever_id' => 'required',
                'sender_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $receiver_type = $receiver_user_type === 'security'
                ? Message::RECEIVER_TYPES['security']
                : Message::RECEIVER_TYPES['tenant'];

            $user = BuildingAdminTenant::where('id', $request->sender_id)->first();
            $sender_type = Message::SENDER_TYPES['tenant'];

            if (!$user) {
                $user = Security_Master::where('id', $request->sender_id)->first();
                $sender_type = Message::SENDER_TYPES['security'];
            }

            if (!$user) {
                return response()->json(['error' => 'Unauthorized user.'], 403);
            }

            if ($user->id == $request->reciever_id) {
                return response()->json(['error' => 'You cannot send a message to yourself.'], 403);
            }

            $data['receiver_details'] = $this->getUserDataBasedOnType($receiver_type, $request->reciever_id);
            $data['sender_details'] = $this->getUserDataBasedOnType($sender_type, $request->sender_id);
            $receiver_id = encrypt($request->reciever_id);
            $data = [
                'reciever_id' => $receiver_id,
                'sender_id' => encrypt($user->id),
                'reciever_type' => $receiver_type,
                'sender_type' => $sender_type,
                'sender_details' => $data['sender_details'],
                'receiver_details' => $data['receiver_details'],
            ];

            return response()->json(['status' => 200, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
    public function fetchMessages(Request $request)
    {
        try {
            $days = 7;
            $dateThreshold = Carbon::now()->subDays($days);

            $sender_id = (int) $request->sender_id;
            $receiver_id = (int) $request->receiver_id;

            if (!$sender_id || !$receiver_id) {
                return response()->json(['error' => 'Invalid sender or receiver ID.'], 422);
            }

            $sender_type = $request->sender_type ?? Message::SENDER_TYPES['tenant'];
            $receiver_type = $request->receiver_type ?? Message::RECEIVER_TYPES['tenant'];

            $messages = Message::where('created_at', '>=', $dateThreshold)
                ->where(function ($query) use ($sender_id, $receiver_id) {
                    $query->where(function ($q) use ($sender_id, $receiver_id) {
                        $q->where('sender_id', $sender_id)
                        ->where('receiver_id', $receiver_id);
                    })->orWhere(function ($q) use ($sender_id, $receiver_id) {
                        $q->where('sender_id', $receiver_id)
                        ->where('receiver_id', $sender_id);
                    });
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['messages' => $messages], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $sender_id = (int) $request->sender_id;
            $receiver_id = (int) $request->receiver_id;

            if (!$sender_id || !$receiver_id) {
                return response()->json(['error' => 'Sender and receiver are required.'], 422);
            }

            $sender_type = $request->sender_type ?? Message::SENDER_TYPES['tenant'];
            $receiver_type = $request->receiver_type ?? Message::RECEIVER_TYPES['tenant'];

            $message = Message::create([
                'receiver_id' => $receiver_id,
                'sender_id' => $sender_id,
                'sender_type' => $sender_type,
                'receiver_type' => $receiver_type,
                'message' => $request->message,
            ]);
            
            $this->sendNotificationToReciever($message);

            return response()->json(['success' => true, 'message' => $message], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendNotificationToReciever($message)
    {
        
        $reciever_id = $message->receiver_id;
        $sender_id = $message->sender_id;
        $sender_type = $message->sender_type;
        $receiver_type = $message->receiver_type;
        $receiver_building_type = 'building';
        $fcm_reciever_type = $this->fcmRecieverType($receiver_type,$receiver_building_type);
        $fcm_user_data = FcmUser::where('user_id', $reciever_id)->where('user_type', $fcm_reciever_type)->get();
    
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

    public function fetchNotifications(Request $request)
    {
        try {
            $user_id = (int) $request->user_id;
            $user_type=$request->user_type ?? 1;
            $building_type=$request->building_type ?? 1;
            $notifications = Notification::where('for_user_id', $user_id)
                                ->where('for_user_type', $user_type)
                                ->where('for_building_type', $building_type)
                                ->where('created_at', '>=', Carbon::now()->subDays(7))
                                ->orderBy('created_at', 'desc')->get();
            $formattedNotifications = $notifications->map(function ($notification) {
                                    return [
                                        'id' => $notification->id,
                                        'message' => $notification->getNotificationMessage(),
                                        'created_at' => $notification->created_at,
                                        'is_read' => $notification->is_read,
                                    ];
                                });
            return response()->json(['notifications' => $formattedNotifications], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
     public function userChatSetting(Request $request)
    {
        try {
            $user_id = (int) $request->user_id;
            $user_type=$request->user_type ?? 1;
            $building_type=$request->building_type ?? 1;
            $chat_setting_status = $request->chat_setting_status ?? 1;
            BuildingAdminTenant::where('id', $user_id)->update(['is_chat_enable' => $chat_setting_status]);
            return response()->json( ['success' => true , 'message' => 'Chat setting updated successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}