<?php

namespace App\Http\Controllers;

use App\Models\FcmUser;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FcmController extends Controller
{
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'user_table_id' => 'required',
            'user_type' => 'required',
            'fcm_token' => 'required|string',
        ]);

        $user = FcmUser::where('user_id', $request->user_id)->where('user_table_id', $request->user_table_id)->where('user_type', $request->user_type)->where('fcm_token', $request->fcm_token)->first();

        if (! $user) {
            $user = new FcmUser;
            $user->user_id = $request->user_id;
            $user->user_table_id = $request->user_table_id;
            $user->user_type = $request->user_type;
            $user->fcm_token = $request->fcm_token;
            $user->save();
        } else {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        return response()->json(['message' => 'Device token updated successfully']);
    }

    public function updateDeviceTokenManually(array $data)
    {

        // Manual validation since $data is an array
        $validator = Validator::make($data, [
            'user_table_id' => 'required',
            'user_type' => 'required',
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $all_user = FcmUser::where('fcm_token', $data['fcm_token'])->get();

        if ($all_user->count() > 1) {
            FcmUser::where('fcm_token', $data['fcm_token'])->delete();
        }
        $user = FcmUser::where('user_id', $data['user_id'])
            ->where('user_table_id', $data['user_table_id'])
            ->where('user_type', $data['user_type'])
            ->where('fcm_token', $data['fcm_token'])
            ->first();
        if (! $user) {
            $user = new FcmUser();
            $user->user_id = $data['user_id'];
            $user->user_table_id = $data['user_table_id'];
            $user->user_type = $data['user_type'];
        } else {
            $user->fcm_token = $data['fcm_token'];
            $user->save();
        }

        $user->fcm_token = $data['fcm_token'];
        $user->save();

        return response()->json(['message' => 'Device token updated successfully']);
    }

    public function sendFcmNotification(array $fcm_data)
    {

        // Validate input data
        $validator = Validator::make($fcm_data, [
            'user_table_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = FcmUser::find($fcm_data['user_table_id']);

        if (!$user || empty($user->fcm_token)) {
            return response()->json(['message' => 'User does not have a valid device token'], 400);
        }

        $fcm = $user->fcm_token;
        $title = $fcm_data['title'];
        $description = $fcm_data['body'];
        // $projectId = config('services.fcm.security-649e4'); // Ensure it's correctly set in config/services.php
        $projectId = env('FIREBASE_PROJECT_ID'); // Ensure it's correctly set in config/services.php

        $credentialsFilePath = storage_path('app/json/fcm_token.json');

        if (!file_exists($credentialsFilePath)) {
            return response()->json(['message' => 'FCM credentials file not found'], 500);
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        if (empty($token['access_token'])) {
            return response()->json(['message' => 'Failed to obtain Firebase access token'], 500);
        }

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json',
        ];

        $data = [
            'message' => [
                'token' => $fcm,
                'notification' => [
                    'title' => $title,
                    'body' => $description,
                ],
                'data' => [
                    'click_action' => 'message',
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err,
            ], 500);
        }

        return response()->json([
            'message' => 'Notification has been sent successfully',
            'response' => json_decode($response, true),
        ]);
    }


}
