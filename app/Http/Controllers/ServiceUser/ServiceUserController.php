<?php

namespace App\Http\Controllers\ServiceUser;

use App\Http\Controllers\Controller;
use App\Models\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceUserController extends Controller
{
    public function updateServiceUserApi(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'img' => 'nullable|string',
            'gender' => 'nullable|string',
            'preferred_language' => 'nullable|string',
            'location' => 'nullable|string',
            'enable_ai_voice_assistant' => 'boolean',
            'notifications' => 'boolean',
            'recommendations' => 'boolean',
        ]);

        // Find service user by ID
        $serviceUser = ServiceUser::findOrFail($id);

        // Update record
        $serviceUser->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Service User details updated successfully ✅',
            'data' => $serviceUser,
        ], 200);
    }
    public function userSummary($serviceUserId)
    {
        // ✅ Service User find karo with relation to User
        $serviceUser = ServiceUser::with('user')->findOrFail($serviceUserId);

        return response()->json([
            'status' => true,
            'service_user_id' => $serviceUser->id,
            'fullname' => $serviceUser->user->fullname ?? null,
            'email' => $serviceUser->user->email ?? null,
            'phone_number' => $serviceUser->user->phone_number ?? null,
            'image' => $serviceUser->img ?? null,
            'location' => $serviceUser->location ?? null,
            'preferred_language' => $serviceUser->preferred_language ?? null, // 👈 Add kiya
        ]);
    }



    public function updateProfileSummary(Request $request, $serviceUserId)
    {
        // 🔹 Service user find with related user
        $serviceUser = ServiceUser::with('user')->findOrFail($serviceUserId);
        $user = $serviceUser->user;

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Related user not found ❌'
            ], 200); // 👈 Always 200
        }

        // ✅ Custom validation
        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|unique:users,phone_number,' . $user->id,
            'gender' => 'nullable|string|max:20',
            'preferred_language' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'img' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('email')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already exists ❌'
                ], 200); // 👈 Always 200
            }

            if ($errors->has('phone_number')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Phone number already exists ❌'
                ], 200); // 👈 Always 200
            }

            return response()->json([
                'status' => false,
                'message' => $errors->first()
            ], 200); // 👈 Always 200
        }

        // ✅ User table update
        $user->fill($request->only(['fullname', 'email', 'phone_number']));
        $user->save();

        // ✅ ServiceUser table update
        $serviceUser->fill($request->only(['img', 'gender', 'preferred_language', 'location']));
        $serviceUser->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully ✅',
            'data' => [
                'service_user_id' => $serviceUser->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'image' => $serviceUser->img,
                'gender' => $serviceUser->gender,
                'preferred_language' => $serviceUser->preferred_language,
                'location' => $serviceUser->location,
            ]
        ], 200); // 👈 Always 200
    }
}
