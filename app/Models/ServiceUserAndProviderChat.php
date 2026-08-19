<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUserAndProviderChat extends Model
{
    use HasFactory;

    protected $table = 'service_user_and_provider_chats';

    protected $fillable = [
        'message',
        'image_path',
        'voice_path',
        'attachment_path',
        'sender_type',
        'service_user_id',
        'service_provider_id',
    ];

    // 🔹 Relation with ServiceUser
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    // 🔹 Relation with ServiceProvider
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
    public function isImage(): bool
    {
        return !is_null($this->image_path);
    }

    /**
     * ✅ Check if message is voice
     */
    public function isVoice(): bool
    {
        return !is_null($this->voice_path);
    }
    /**
     * ✅ Check if message is attachment (pdf, doc etc.)
     */
    public function isAttachment(): bool
    {
        return !is_null($this->attachment_path);
    }
}
