<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerProfile extends Model
{
    use HasFactory;

    protected $table = 'buyer_profiles';

    protected $fillable = [
        'user_id',
        'img',
        'gender',
        'preferred_language',
        'location',
        'enable_ai_voice_assistant',
        'notifications',
        'recommendations',
        'company',
        'total_spent',
        'jobs_posted',
    ];

    protected $casts = [
        'enable_ai_voice_assistant' => 'boolean',
        'notifications' => 'boolean',
        'recommendations' => 'boolean',
        'total_spent' => 'decimal:2',
        'jobs_posted' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_user_id');
    }
}
