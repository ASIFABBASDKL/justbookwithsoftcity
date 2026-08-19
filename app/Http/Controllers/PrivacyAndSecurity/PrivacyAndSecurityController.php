<?php

namespace App\Http\Controllers\PrivacyAndSecurity;

use App\Http\Controllers\Controller;
use App\Models\PrivacyAndSecurity;
use Illuminate\Http\Request;

class PrivacyAndSecurityController extends Controller
{
    /**
     * Store or Update Privacy & Security Policy
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $policy = PrivacyAndSecurity::updateOrCreate(
            ['id' => 1], // sirf ek record rakha jayega
            ['text' => $data['text']]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Policy text saved successfully!',
            'data'    => $policy,
        ]);
    }

    /**
     * Show Privacy & Security Policy
     */
    public function show()
    {
        $policy = PrivacyAndSecurity::first();

        if (!$policy) {
            return response()->json([
                'status'  => false,
                'message' => 'No policy found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $policy,
        ]);
    }

    /**
     * Accept Policy
     */
    public function accept()
    {
        $policy = PrivacyAndSecurity::first();

        if (!$policy) {
            return response()->json([
                'status'  => false,
                'message' => 'No policy found to accept.',
            ], 404);
        }

        $policy->status = 'agree';
        $policy->save();

        return response()->json([
            'status'  => true,
            'message' => 'Policy has been accepted.',
            'data'    => $policy,
        ]);
    }

    /**
     * Reject Policy
     */
    public function reject()
    {
        $policy = PrivacyAndSecurity::first();

        if (!$policy) {
            return response()->json([
                'status'  => false,
                'message' => 'No policy found to reject.',
            ], 404);
        }

        $policy->status = 'decline';
        $policy->save();

        return response()->json([
            'status'  => true,
            'message' => 'Policy has been rejected.',
            'data'    => $policy,
        ]);
    }
}
