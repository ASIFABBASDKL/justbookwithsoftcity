<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\Notification; // 🔹 apna model use karo

class FirebaseService
{
    protected $firebase;

    public function __construct()
    {
        // Initialize Firebase with the credentials file
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));
    }

    public function sendNotification($deviceToken, $title, $body, $userId = null)
    {
        // Get Firebase Messaging instance
        $messaging = $this->firebase->createMessaging();

        // Create a notification message
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(FirebaseNotification::create($title, $body));

        try {
            // 🔹 Send to Firebase
            $messaging->send($message);

            // 🔹 Store in DB
            $notification = Notification::create([
                'user_id'      => $userId,
                'device_token' => $deviceToken,
                'title'        => $title,
                'body'         => $body,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent & stored successfully',
                'data'    => $notification
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
