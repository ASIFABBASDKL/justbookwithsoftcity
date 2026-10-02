<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\Notification;

class NotificationController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    // 🔹 Send & Store Notification
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'device_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $this->requireOwnUserId((int) ($request->user_id ?? $this->authUser()->id)); 
        return $this->firebase->sendNotification(
            $request->device_token,
            $request->title,
            $request->body,
            $request->user_id
        );
    }
    
    // 🔹 Get All Notifications of Single User
    public function getUserNotifications($userId)
    {
        $this->requireOwnUserId((int) $userId);

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
}
