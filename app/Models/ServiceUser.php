<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'img',
        'gender',
        'preferred_language',
        'location',
        'enable_ai_voice_assistant',
        'notifications',
        'recommendations',
    ];

    /**
     * 🔹 Relation: ServiceUser belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🔹 Relation: ServiceUser can have many payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
}
