<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Services\ModerationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id;
        $rows = Conversation::where('buyer_id', $id)->orWhere('seller_id', $id)
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (Conversation $c) use ($id) {
                $unread = $c->messages()->whereNull('read_at')->where('sender_id', '!=', $id)->count();
                $c->setAttribute('unread_count', $unread);

                return $c;
            });

        return ApiResponse::success($rows);
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'seller_user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);
        $convo = Conversation::firstOrCreate([
            'buyer_id' => $request->user()->id,
            'seller_id' => $data['seller_user_id'],
            'order_id' => $data['order_id'] ?? null,
        ]);

        return ApiResponse::success($convo);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->assertMember($conversation, $request->user()->id);
        $conversation->messages()->whereNull('read_at')->where('sender_id', '!=', $request->user()->id)->update(['read_at' => now()]);

        return ApiResponse::success($conversation->load('messages'));
    }

    public function send(Request $request, Conversation $conversation, ModerationService $mod)
    {
        $this->assertMember($conversation, $request->user()->id);
        $request->validate(['body' => 'nullable|string', 'file' => 'nullable|file|max:10240']);
        $attachments = [];
        if ($request->hasFile('file')) {
            $attachments[] = $request->file('file')->store('chat', 'public');
        }
        $flagged = $mod->detectsOffPlatform($request->body) || $mod->containsBannedWords($request->body);
        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'body' => $request->body,
            'attachments' => $attachments ?: null,
            'flagged' => $flagged,
        ]);
        $conversation->update(['last_message_at' => now()]);

        return ApiResponse::success($msg, $flagged ? 'Message sent but flagged for review' : 'Sent', 201);
    }

    private function assertMember(Conversation $conversation, int $userId): void
    {
        if ((int) $conversation->buyer_id !== $userId && (int) $conversation->seller_id !== $userId) {
            $this->deny();
        }
    }
}
