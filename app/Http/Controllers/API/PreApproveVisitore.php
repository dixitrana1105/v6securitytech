<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TenantVisitor;
use App\Models\Visitor_Master;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class PreApproveVisitore extends Controller
{
    public function preStoreVisitor(Request $request)
    {
        // dd('ok');
        $validatedData = $request->validate([
            'tenant_flat_office_no' => '',
            'VisitorId' => '',
            'visitor_id' => 'nullable',
            'date' => '',
            'full_name' => '',
            'in_time' => '',
            'out_time' => 'nullable',
            'visiter_purpose' => '',
            'photo' => 'nullable',
            'id_proof' => 'nullable',
            'mobile' => 'nullable',
            'whatsapp' => 'nullable',

        ]);

        // dd($request->all());

        $destinationPath = public_path('assets/images/');
        $idProofPath = null;
        $photoPath = null;

        if ($request->file('id_proof')) {
            $idProofFileName = time().'_'.$request->file('id_proof')->getClientOriginalName();
            $request->file('id_proof')->move($destinationPath, $idProofFileName);
            $idProofPath = 'assets/images/'.$idProofFileName;
        }

        if ($request->file('photo')) {
            $photoFile = time().'_'.$request->file('photo')->getClientOriginalName();
            $request->file('photo')->move($destinationPath, $photoFile);
            $photoPath = 'assets/images/'.$photoFile;
        }

        // Create and save visitor data
        $visitorData = Visitor_Master::create([
            'tenant_flat_office_no' => $validatedData['tenant_flat_office_no'],
            'visitor_id' => $validatedData['VisitorId'],
            'date' => $validatedData['date'],
            'full_name' => $validatedData['full_name'],
            'mobile' => $validatedData['mobile'],
            'whatsapp' => $validatedData['whatsapp'],
            'in_time' => now(),
            'visitor_remark' => 'Preapproved',
            'pre_approve_tenant_visitore_id' => $validatedData['VisitorId'],
            'out_time' => $validatedData['out_time'] ?? null,
            'visitor_id_detected' => $validatedData['visitor_id'] ?? null,
            'visiter_purpose' => $validatedData['visiter_purpose'],
            'building_id' => $request->building_id,
            'status' => 1,
            'photo' => $photoPath,
            'id_proof' => $idProofPath,
            'added_by' => $request->building_id,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor data successfully saved.',
            'data' => $visitorData,
        ], 201);
    }

    /**
     * Handle Base64 image upload and save to the destination path.
     *
     * @param  string  $base64Image
     * @param  string  $destinationPath
     * @param  string  $prefix
     * @return string|null
     */
    private function handleBase64Upload($base64Image, $destinationPath, $prefix)
    {
        $imageParts = explode(';base64,', $base64Image);

        if (count($imageParts) === 2) {
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);
            $fileName = time()."_{$prefix}.".$imageType;

            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            file_put_contents($destinationPath.$fileName, $imageBase64);

            return 'assets/images/'.$fileName;
        }

        return null;
    }

    public function getPreapproveVisitore(Request $request)
    {
        // dd($request);
        $building_id = $request->building_id;
        $tenant_flat_office_no = $request->tenant_flat_office_no;
        $date_from = $request->date_from
        ? Carbon::parse($request->date_from)->startOfDay()->setTimezone('UTC')
        : Carbon::now()->startOfDay()->setTimezone('UTC');

        $date_to = $request->date_to
            ? Carbon::parse($request->date_to)->endOfDay()->setTimezone('UTC')
            : Carbon::now()->endOfDay()->setTimezone('UTC');

        $visitorMasterIds = Visitor_Master::where('building_id', $building_id)
            ->where('created_at', '>=', $date_from)
            ->where('created_at', '<=', $date_to)
            ->pluck('visitor_id');

        $visitors = TenantVisitor::where('building_id', $building_id)->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to);

        if ($tenant_flat_office_no) {
            $visitors->where('tenant_flat_office_no', $tenant_flat_office_no);
        }

        $visitors = $visitors->whereNotIn('visitor_id', $visitorMasterIds)
            ->get(['full_name', 'visitor_id']);

        return response()->json($visitors);
    }

    public function storePreapproveVisitorByTenant(Request $request)
    {
        try {
            // dd('ok');

            // Validate the request
            $validatedData = $request->validate([
                'building_id' => 'required|integer',
                'tenant_flat_office_no' => 'required|string',
                'full_name' => 'required|string',
                'visiter_purpose' => 'required|string',
            ]);

            // Extract building_id from request
            $building_id = $validatedData['building_id'];

            // Get the last visitor ID for the given building
            $lastVisitor = TenantVisitor::where('visitor_id', 'like', 'TENA'.$building_id.'%')
                ->orderBy('visitor_id', 'desc')
                ->first();

            if ($lastVisitor) {
                $lastVisitorIdNumeric = (int) str_replace('TENA', '', $lastVisitor->visitor_id);
                $nextVisitorId = $lastVisitorIdNumeric + 1;
            } else {
                $nextVisitorId = $building_id * 1000 + 1;
            }

            $nextVisitorId = 'TENA'.$nextVisitorId;

            // Create new visitor entry
            $data = new TenantVisitor;
            $data->tenant_flat_office_no = $validatedData['tenant_flat_office_no'];
            $data->visitor_id = $nextVisitorId;
            $data->date = now()->format('Y-m-d');
            $data->full_name = $validatedData['full_name'];
            $data->in_time = now()->format('H:i');
            $data->visiter_purpose = $validatedData['visiter_purpose'];
            $data->building_id = $building_id;
            $data->status = 1;
            $data->created_at = now();
            $data->save();

            // dd($data);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Visitor pre-approved successfully.',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
