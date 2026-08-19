<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatWithAi extends Model
{
    use HasFactory;

    protected $table = 'chat_with_ai';

    protected $fillable = [
        'user_id',
        'voice_path',
        'transcribed_text',
        'message',
        'response_text',
    ];

    /**
     * Cast JSON fields to array
     */
    protected $casts = [
        'voice_path'       => 'array',
        'transcribed_text' => 'array',
        'message'          => 'array',
        'response_text'    => 'array',
    ];

    /**
     * Relation → belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
