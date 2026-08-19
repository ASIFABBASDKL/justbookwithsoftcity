<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\Models\ServiceUser;
use App\Models\ServiceUserAndProviderChat;
class ServiceUserAndProviderChatController extends Controller
{
    //
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string', // ab message optional hoga (sirf file bhi send ho sakti hai)
            'sender_type' => 'required|in:user,provider',
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'voice' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
        ]);

        if ($request->sender_type === 'user') {
            $serviceUser = ServiceUser::find($request->sender_id);
            $serviceProvider = ServiceProvider::find($request->receiver_id);

            $service_user_id = $serviceUser?->id;
            $service_provider_id = $serviceProvider?->id;
        } else {
            $serviceProvider = ServiceProvider::find($request->sender_id);
            $serviceUser = ServiceUser::find($request->receiver_id);

            $service_user_id = $serviceUser?->id;
            $service_provider_id = $serviceProvider?->id;
        }

        if (!$service_user_id || !$service_provider_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sender or receiver.',
            ], 404);
        }

        // Default null
        $imagePath = null;
        $voicePath = null;
        $attachmentPath = null;

        // ✅ Image Upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_uploads/images', 'public');
        }

        // ✅ Voice Upload
        if ($request->hasFile('voice')) {
            $voicePath = $request->file('voice')->store('chat_uploads/voices', 'public');
        }

        // ✅ Attachment Upload
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('chat_uploads/attachments', 'public');
        }

        // ✅ Create Chat Record
        $chat = ServiceUserAndProviderChat::create([
            'message' => $request->message,
            'image_path' => $imagePath,
            'voice_path' => $voicePath,
            'attachment_path' => $attachmentPath,
            'sender_type' => $request->sender_type,
            'service_user_id' => $service_user_id,
            'service_provider_id' => $service_provider_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $chat->load(['serviceUser', 'serviceProvider']),
        ]);
    }

    public function getChatsByUserId(Request $request, $serviceUserId)
    {
        // Distinct providers for this user
        $providerIds = ServiceUserAndProviderChat::where('service_user_id', $serviceUserId)
            ->distinct()
            ->pluck('service_provider_id');

        $allChats = ServiceUserAndProviderChat::with(['serviceUser', 'serviceProvider'])
            ->where('service_user_id', $serviceUserId)
            ->whereIn('service_provider_id', $providerIds)
            ->orderBy('service_provider_id')
            ->orderBy('created_at')
            ->get();

        $grouped = $allChats->groupBy('service_provider_id');

        return response()->json([
            'success' => true,
            'message' => 'All chats for user grouped by provider.',
            'data' => $grouped,
        ]);
    }
    public function getChatsByProviderId(Request $request, $serviceProviderId)
    {
        // Distinct users for this provider
        $userIds = ServiceUserAndProviderChat::where('service_provider_id', $serviceProviderId)
            ->distinct()
            ->pluck('service_user_id');

        $allChats = ServiceUserAndProviderChat::with(['serviceUser', 'serviceProvider'])
            ->where('service_provider_id', $serviceProviderId)
            ->whereIn('service_user_id', $userIds)
            ->orderBy('service_user_id')
            ->orderBy('created_at')
            ->get();

        $grouped = $allChats->groupBy('service_user_id');

        return response()->json([
            'success' => true,
            'message' => 'All chats for provider grouped by user.',
            'data' => $grouped,
        ]);
    }
    public function getMessages(Request $request)
    {
        $request->validate([
            'service_user_id' => 'required|exists:service_users,id',
            'service_provider_id' => 'required|exists:service_providers,id',
        ]);

        $chats = ServiceUserAndProviderChat::with(['serviceUser', 'serviceProvider'])
            ->where('service_user_id', $request->service_user_id)
            ->where('service_provider_id', $request->service_provider_id)
            ->orderBy('created_at')
            ->get();

        if ($chats->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No chats found for this user and provider.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chats retrieved successfully.',
            'data' => $chats,
        ]);
    }

}
