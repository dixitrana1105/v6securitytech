<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BuildingAdminTenant;
use App\Models\Security_Master;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileUpdateController extends Controller
{
    public function updateProfile(Request $request)
    {
        try {
            // Validate initial inputs
            $request->validate([
                'building_type' => 'required|in:security,tenant',
                'building_id' => 'required',
                'newpsw' => 'nullable|string|min:8',
                'tenant_id' => 'required_if:type,tenant|exists:building_tenant,tenant_id',
                'security_id' => 'required_if:type,security|exists:security_master,security_id',
            ]);

            // Determine type and set validation rules
            $type = $request->building_type;
            $validationRules = [
                'logo' => 'nullable',
                'business_name' => 'nullable|string|max:255',
                'current_address_1' => 'nullable|string|max:255',
                'current_address_2' => 'nullable|string|max:255',
            ];

            if ($type === 'security') {
                $validationRules = array_merge($validationRules, [
                    'name' => 'required|string|max:255',
                    'contact' => 'required|string|max:15',
                    'email' => 'required|email|max:255',
                ]);
            } elseif ($type === 'tenant') {
                $validationRules = array_merge($validationRules, [
                    'contact_person' => 'required|string|max:255',
                    'contact_number' => 'required|string|max:15',
                    'email' => 'required|email|max:255',
                ]);
            }

            $validatedData = $request->validate($validationRules);

            // Fetch user based on type
            $user = null;
            if ($type === 'security') {
                $user = Security_Master::where('security_id', $request->security_id)->first();
            } elseif ($type === 'tenant') {
                $user = BuildingAdminTenant::where('tenant_id', $request->tenant_id)->first();
            }

            if (! $user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Verify old password

            // Update password
            if ($request->newpsw && $request->newpsw != null) {
                $user->password = Hash::make($request->newpsw);
            }

            // Update other fields
            $user->business_name = $request->business_name;
            $user->current_address_1 = $request->current_address_1;
            $user->current_address_2 = $request->current_address_2;

            if ($type === 'security') {
                $user->name = $request->name;
                $user->contact = $request->contact;
                $user->email = $request->email;
            } elseif ($type === 'tenant') {
                $user->contact_person = $request->contact_person;
                $user->contact_number = $request->contact_number;
                $user->email = $request->email;
            }

            if ($request->hasFile('logo')) {
                $destinationPath = public_path('assets/images/');
                $logoFileName = time().'_'.$request->file('logo')->getClientOriginalName();
                $request->file('logo')->move($destinationPath, $logoFileName);

                $user->logo = 'assets/images/'.$logoFileName;
            }

            $user->save();
            // dd($user);
            if (! empty($user->tenant_photo)) {
                $user->tenant_photo = 'assets/images/'.$user->tenant_photo;
            }

            if (! empty($user->tenant_id_proof)) {
                $user->tenant_id_proof = 'assets/images/'.$user->tenant_id_proof;
            }

            return response()->json(['message' => 'Profile updated successfully.', 'user' => $user], 200);

        } catch (\Exception $e) {
            // Log error for debugging
            // \Log::error('Update Profile Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteProfile(Request $request)
    {
        try {
            // dd($request->all());
            // Validate inputs
            $request->validate([
                'type' => 'required',
                'building_id' => 'required',
                'tenant_id' => 'required',
            ]);

            // Determine type
            $type = $request->type;

            // Fetch user based on type
            $user = null;
            if ($type === 'security') {
                return response()->json(['error' => 'Security cannot be deleted.'], 403);
                // $user = Security_Master::where('security_id', $request->security_id)->first();
            } elseif ($type === 'tenant') {
                // dd('ok');
                $user = BuildingAdminTenant::where('tenant_id', $request->tenant_id)->first();
            }

            // dd($user);
            if (! $user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Perform soft delete
            $user->delete();

            return response()->json(['message' => 'Profile deleted successfully.'], 200);

        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Delete Profile Error: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function restoreProfile(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required',
                'building_id' => 'required',
                'tenant_id' => 'required',
            ]);

            $type = $request->type;

            $user = null;
            if ($type === 'security') {
                return response()->json(['error' => 'Security cannot be restored.'], 403);
            } elseif ($type === 'tenant') {
                $user = BuildingAdminTenant::withTrashed()
                    ->where('tenant_id', $request->tenant_id)
                    ->first();
            }

            if (! $user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            $user->restore();

            return response()->json(['message' => 'Profile restored successfully.'], 200);

        } catch (\Exception $e) {
            \Log::error('Restore Profile Error: '.$e->getMessage());

            return response()->json(['error' => 'Something went wrong. Please try again later.'], 500);
        }
    }
}
