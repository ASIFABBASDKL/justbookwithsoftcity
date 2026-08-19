<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricLogin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'biometric_token',
        'is_active',
    ];

    /**
     * Casts for attributes.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
