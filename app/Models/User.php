<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fullname',
        'email',
        'phone_number',
        'password',
        'role',
        'email_verified_at',
        'phone_verified_at',
        'device_token', // ✅ new field
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /* --------------------------------
     | 📱 Phone Verification Helpers
     -------------------------------- */
    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => now(),
        ])->save();
    }

    /* --------------------------------
     | 📧 Email Verification Helpers
     -------------------------------- */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    /* --------------------------------
     | 🔗 Relations
     -------------------------------- */
    public function serviceUser()
    {
        return $this->hasOne(ServiceUser::class, 'user_id');
    }
    
    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class, 'user_id');
    }
    public function role()
{
    return $this->belongsTo(Role::class, 'role_id');
}

}
