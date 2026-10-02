<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'fullname',
        'username',
        'email',
        'phone_number',
        'password',
        'is_buyer',
        'is_seller',
        'email_verified_at',
        'phone_verified_at',
        'device_token',
        'avatar',
        'country',
        'timezone',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'password' => 'hashed',
        'is_buyer' => 'boolean',
        'is_seller' => 'boolean',
    ];

    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => now(),
        ])->save();
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    public function buyerProfile()
    {
        return $this->hasOne(BuyerProfile::class, 'user_id');
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class, 'user_id');
    }

    /** @deprecated Use buyerProfile() */
    public function serviceUser()
    {
        return $this->buyerProfile();
    }

    /** @deprecated Use sellerProfile() */
    public function serviceProvider()
    {
        return $this->sellerProfile();
    }

    public function ensureBuyerProfile(): BuyerProfile
    {
        $this->forceFill(['is_buyer' => true])->save();

        return $this->buyerProfile()->firstOrCreate(['user_id' => $this->id]);
    }

    public function becomeSeller(): SellerProfile
    {
        $this->forceFill([
            'is_buyer' => true,
            'is_seller' => true,
        ])->save();

        $this->ensureBuyerProfile();

        $profile = $this->sellerProfile()->firstOrCreate(
            ['user_id' => $this->id],
            ['level' => 'new']
        );

        Wallet::firstOrCreate(
            ['service_provider_id' => $profile->id],
            [
                'total_amount' => 0.00,
                'total_available_amount' => 0.00,
                'total_withdrawal_amount' => 0.00,
                'pending_clearance' => 0.00,
            ]
        );

        app(\App\Services\ConnectService::class)->ensure($profile);

        return $profile->load('wallet');
    }
}
