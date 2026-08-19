<?php

namespace App\Http\Controllers\Availability;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Store or Update Availability API
     */
    public function storeAvailabilityApi(Request $request, $providerId)
{
    // ✅ Ensure provider exists
    $provider = ServiceProvider::findOrFail($providerId);

    // ✅ Check if availability already exists
    $existing = Availability::where('service_provider_id', $provider->id)->first();
    if ($existing) {
        return response()->json([
            'status' => false,
            'message' => 'Availability already exists. You can update only.',
            'data' => $existing
        ], 200); 
    }

    // ✅ Validate input
    $data = $request->validate([
        'monday' => 'boolean',
        'monday_start' => 'nullable|date_format:H:i',
        'monday_end' => 'nullable|date_format:H:i',

        'tuesday' => 'boolean',
        'tuesday_start' => 'nullable|date_format:H:i',
        'tuesday_end' => 'nullable|date_format:H:i',

        'wednesday' => 'boolean',
        'wednesday_start' => 'nullable|date_format:H:i',
        'wednesday_end' => 'nullable|date_format:H:i',

        'thursday' => 'boolean',
        'thursday_start' => 'nullable|date_format:H:i',
        'thursday_end' => 'nullable|date_format:H:i',

        'friday' => 'boolean',
        'friday_start' => 'nullable|date_format:H:i',
        'friday_end' => 'nullable|date_format:H:i',

        'saturday' => 'boolean',
        'saturday_start' => 'nullable|date_format:H:i',
        'saturday_end' => 'nullable|date_format:H:i',

        'sunday' => 'boolean',
        'sunday_start' => 'nullable|date_format:H:i',
        'sunday_end' => 'nullable|date_format:H:i',
    ]);

    // ✅ Create new availability
    $availability = Availability::create(array_merge(
        ['service_provider_id' => $provider->id],
        $data
    ));

    return response()->json([
        'status' => true,
        'message' => 'Availability created successfully',
        'data' => $availability,
    ], 200);
}

    public function updateAvailabilityApi(Request $request, $providerId)
    {
    // ✅ Ensure provider exists
        $provider = ServiceProvider::findOrFail($providerId);

    // ✅ Validate input
      $data = $request->validate([
        'monday' => 'boolean',
        'monday_start' => 'nullable|date_format:H:i',
        'monday_end' => 'nullable|date_format:H:i',

        'tuesday' => 'boolean',
        'tuesday_start' => 'nullable|date_format:H:i',
        'tuesday_end' => 'nullable|date_format:H:i',

        'wednesday' => 'boolean',
        'wednesday_start' => 'nullable|date_format:H:i',
        'wednesday_end' => 'nullable|date_format:H:i',

        'thursday' => 'boolean',
        'thursday_start' => 'nullable|date_format:H:i',
        'thursday_end' => 'nullable|date_format:H:i',

        'friday' => 'boolean',
        'friday_start' => 'nullable|date_format:H:i',
        'friday_end' => 'nullable|date_format:H:i',

        'saturday' => 'boolean',
        'saturday_start' => 'nullable|date_format:H:i',
        'saturday_end' => 'nullable|date_format:H:i',

        'sunday' => 'boolean',
        'sunday_start' => 'nullable|date_format:H:i',
        'sunday_end' => 'nullable|date_format:H:i',
    ]);

    // ✅ Check if availability already exists
    $availability = Availability::where('service_provider_id', $provider->id)->first();

    if (!$availability) {
        return response()->json([
            'status' => false,
            'message' => 'Availability not found for this provider'
        ], 404);
    }

    // ✅ Update availability
    $availability->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Availability updated successfully',
        'data' => $availability,
    ], 200);
    }

    /**
     * Get Availability by Service Provider ID
     */
   
   public function getAvailabilityApi($providerId)
    {
    // ✅ Ensure provider exists
    $provider = ServiceProvider::findOrFail($providerId);

    // ✅ Get availability by provider_id
    $availability = Availability::where('service_provider_id', $provider->id)->first();

    return response()->json([
        'status' => $availability ? true : false,
        'message' => $availability
            ? "Availability found for provider ID {$providerId}"
            : "No availability found for provider ID {$providerId}",
        'data' => $availability,
    ], 200);
    }


}
