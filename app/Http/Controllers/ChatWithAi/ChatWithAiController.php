<?php

namespace App\Http\Controllers\ChatWithAi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatWithAi;
use Illuminate\Support\Facades\Http;

class ChatWithAiController extends Controller
{
    public function chatWithAiStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'voice' => 'nullable|file|mimes:mp3,wav,m4a',
        ]);

        $reply = "I'm here to help you.";
        $transcribedText = null;
        $voicePath = null;

        // 🔹 Agar voice file aayi hai to save aur Gemini ko bhejo
        if ($request->hasFile('voice')) {
            $file = $request->file('voice');
            $voicePath = $file->store('voices', 'public');
            $voiceFilePath = storage_path("app/public/" . $voicePath);

            if (file_exists($voiceFilePath)) {
                $geminiApiKey = env('GEMINI_API_KEY');
                $audioBase64 = base64_encode(file_get_contents($voiceFilePath));

                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiApiKey}", [
                    "contents" => [
                        [
                            "parts" => [
                                [
                                    "inline_data" => [
                                        "mime_type" => "audio/mp3",
                                        "data" => $audioBase64
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if ($rawText) {
                        // 🔹 Clean transcription → sirf main wording nikal lo
                        if (preg_match('/"([^"]+)"/', $rawText, $matches)) {
                            $transcribedText = strtolower(trim($matches[1]));
                        } else {
                            $words = preg_split('/\s+/', $rawText);
                            $transcribedText = strtolower(trim($words[0] ?? ''));
                        }
                    }
                }
            }
        }

        // 🔹 Agar message text aaya hai to usko bhi transcribedText treat karo
        if ($request->message) {
            $transcribedText = strtolower(trim($request->message));
        }

        // 🔹 Reply Logic (common for both voice & text)
        if ($transcribedText) {
            if ($transcribedText === "hello") {
                $reply = "How can I assist you?";
            } elseif ($transcribedText === "hi") {
                $reply = "Hello! How’s your day going?";
            } elseif ($transcribedText === "bye") {
                $reply = "Goodbye! Have a nice day.";
            } else {
                $reply = "I got your message: \"$transcribedText\"";
            }
        }

        // 🔹 Check if record exists
        $chat = ChatWithAi::where('user_id', $request->user_id)->first();

        if ($chat) {
            $chat->update([
                'voice_path' => array_merge($chat->voice_path ?? [], $voicePath ? [$voicePath] : []),
                'transcribed_text' => array_merge($chat->transcribed_text ?? [], $transcribedText ? [$transcribedText] : []),
                'message' => array_merge($chat->message ?? [], $request->message ? [$request->message] : []),
                'response_text' => array_merge($chat->response_text ?? [], [$reply]),
            ]);
        } else {
            $chat = ChatWithAi::create([
                'user_id' => $request->user_id,
                'voice_path' => $voicePath ? [$voicePath] : [],
                'transcribed_text' => $transcribedText ? [$transcribedText] : [],
                'message' => $request->message ? [$request->message] : [],
                'response_text' => [$reply],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Chat stored successfully',
            'data' => $chat
        ], 200);
    }






}
