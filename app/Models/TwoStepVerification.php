<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TwoStepVerification extends Model
{
    use HasFactory;

    protected $table = 'two_step_verifications';

    protected $fillable = [
        'user_id',
        'email',
        'phone_number',
        'device_id',
        'device_name',
        'old_device_id',
        'old_device_name',
        'email_otp',
        'phone_otp',
        'email_expires_at',
        'phone_expires_at',
        'email_verified',
        'phone_verified',
        'status',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'status' => 'boolean',
        'email_expires_at' => 'datetime',
        'phone_expires_at' => 'datetime',
    ];

    /**
     * Relation with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email OTP is expired
     */
    public function isEmailOtpExpired(): bool
    {
        return $this->email_expires_at ? Carbon::now()->greaterThan($this->email_expires_at) : true;
    }

    /**
     * Check if phone OTP is expired
     */
    public function isPhoneOtpExpired(): bool
    {
        return $this->phone_expires_at ? Carbon::now()->greaterThan($this->phone_expires_at) : true;
    }

    /**
     * Update overall status (both verified)
     */
    public function updateStatus(): void
    {
        $this->status = $this->email_verified && $this->phone_verified;
    }
}
