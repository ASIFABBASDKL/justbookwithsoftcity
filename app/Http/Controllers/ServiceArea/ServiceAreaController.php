<?php

namespace App\Http\Controllers\ServiceArea;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceArea;


class ServiceAreaController extends Controller
{
    public function storeServiceAreaApi(Request $request)
    {
        $data = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'city' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'live_location' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $serviceArea = ServiceArea::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Service Area created successfully!',
            'data' => $serviceArea,
        ], 200);

    }
    public function updateServiceAreaApi(Request $request, $id)
{
    // ✅ Service area find karo
    $serviceArea = ServiceArea::find($id);

    if (!$serviceArea) {
        return response()->json([
            'status' => false,
            'message' => 'Service area not found',
        ], 404);
    }

    // ✅ Validate input
    $data = $request->validate([
        'city' => 'nullable|string|max:255',
        'building' => 'nullable|string|max:255',
        'apartment' => 'nullable|string|max:255',
        'floor' => 'nullable|string|max:50',
        'street' => 'nullable|string|max:255',
        'live_location' => 'boolean',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
    ]);

    // ✅ Update service area
    $serviceArea->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Service area updated successfully!',
        'data' => $serviceArea,
    ], 200);
}

    public function getServiceAreasByProvider($providerId)
    {
        $serviceAreas = ServiceArea::where('service_provider_id', $providerId)->get();

        if ($serviceAreas->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No services found for this provider.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Service Areas fetched successfully!',
            'data' => $serviceAreas,
        ], 200);
    }
   
   public function deleteServiceAreaApi($id)
{
    // ✅ Service area find karo
    $serviceArea = ServiceArea::find($id);

    if (!$serviceArea) {
        return response()->json([
            'status' => false,
            'message' => 'Service area not found',
        ], 404);
    }

    // ✅ Delete service area
    $serviceArea->delete();

    return response()->json([
        'status' => true,
        'message' => 'Service area deleted successfully!'
    ], 200);
}


}
