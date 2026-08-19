<?php

namespace App\Http\Controllers\EnableLocation;

use App\Http\Controllers\Controller;
use App\Models\EnableLocation;
use Illuminate\Http\Request;

class EnableLocationController extends Controller
{
    /**
     * Store or Update Enable Location (API).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'is_location_enabled' => 'required|boolean',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
        ]);

        // ✅ Agar record already exist karega to update ho jayega
        $enableLocation = EnableLocation::updateOrCreate(
            ['service_provider_id' => $data['service_provider_id']],
            [
                'is_location_enabled' => $data['is_location_enabled'],
                'latitude'            => $data['latitude'] ?? null,
                'longitude'           => $data['longitude'] ?? null,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Location settings updated successfully!',
            'data'    => $enableLocation,
        ]);
    }

    /**
     * Show Enable Location by Service Provider ID.
     */
    public function show($service_provider_id)
    {
        $location = EnableLocation::where('service_provider_id', $service_provider_id)->first();

        if (!$location) {
            return response()->json([
                'status'  => false,
                'message' => 'Location not found for this service provider.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'data'    => $location,
        ]);
    }
}
