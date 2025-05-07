<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FcmController;
use App\Models\FcmUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiLoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_type' => 'required',
            'user_type' => 'required',
            'email' => 'required',
            'password' => 'required',
            'secret_key' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password', 'secret_key');

        $guard = $this->getGuard($request->building_type, $request->user_type);

        if (! $guard) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid building type or user type.',
            ], 400);
        }

        $userModel = Auth::guard($guard)->getProvider()->getModel();
        $user = $userModel::withTrashed()->where('email', $request->email)->first();

        if ($user && $user->deleted_at) {
            return response()->json([
                'status' => 403,
                'message' => 'This account has been deleted.',
            ], 403);
        }

        if (! Auth::guard($guard)->attempt($credentials)) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::guard($guard)->user();

        // Generate a static token without using Sanctum
        $token = base64_encode(hash('sha256', $user->id.now().'static_secret_key', true));

        \App\Models\TokenApi::create([
            'token' => $token,
            'current_session_tocken' => Str::random(40),
            'user_id' => $user->id,
            'user_type' => $request->building_type.'_'.$request->user_type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $fcm_data = [
            'user_id' => $user->id,
            'user_table_id' => $user->id,
            'user_type' => $request->building_type.'_'.$request->user_type,
            'fcm_token' => $request->fcm_token,
        ];

        // Call the function properly
        (new FcmController)->updateDeviceTokenManually($fcm_data);
        foreach ($user->getAttributes() as $key => $value) {
            if (is_null($value)) {
                $user->setAttribute($key, '');
            }
        }
        if (! empty($user->tenant_photo)) {
            $user->tenant_photo = 'assets/images/'.$user->tenant_photo;
        }

        if (! empty($user->tenant_id_proof)) {
            $user->tenant_id_proof = 'assets/images/'.$user->tenant_id_proof;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'token' => $token,
            'user_data' => $user,
            'building_type' => $request->building_type,
            'building_id' => $request->building_type === 'school' ? $user->added_by : ($user->building_id ?? null),
        ], 200);
    }

    private function getGuard($buildingType, $userType)
    {
        $guards = [
            'building_security' => 'buildingSecutityadmin',
            'building_tenant' => 'buildingtenant',
            'school_security' => 'schoolsecurity',
            'school_admin'  =>  'buildingadmin',
        ];

        $key = strtolower($buildingType.'_'.$userType);

        return $guards[$key] ?? null;
    }

    public function logout(Request $request)
    {
        $fcm_token = $request->fcm_token;
        if (! empty($fcm_token)) {
            FcmUser::where('fcm_token', $fcm_token)->delete();
        }

        Auth::logout();

        session()->flush();

        session()->regenerate();

        return response()->json(['status' => true, 'message' => 'User logged out successfully']);
    }
}
