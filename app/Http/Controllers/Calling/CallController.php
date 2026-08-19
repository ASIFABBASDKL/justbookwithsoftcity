<?php

namespace App\Http\Controllers\Calling;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Call;

class CallController extends Controller
{
    /**
     * Store a new call record
     */
    public function storeCall(Request $request)
    {
        $data = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'service_user_id'     => 'required|exists:service_users,id',
            'duration'            => 'nullable|string|max:255',
            'type'                => 'required|in:audio,video',
            'call_time'           => 'nullable|date',
        ]);

        $call = Call::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Call stored successfully 📞',
            'data'    => $call,
        ], 201);
    }

    /**
     * Get all calls by Service Provider ID
     */
    public function getAllByProviderId($providerId)
    {
        $calls = Call::with('serviceUser')
            ->where('service_provider_id', $providerId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $calls,
        ], 200);
    }

    /**
     * Get all calls by Service User ID
     */
    public function getAllByServiceUserId($userId)
    {
        $calls = Call::with('serviceProvider')
            ->where('service_user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $calls,
        ], 200);
    }
}
