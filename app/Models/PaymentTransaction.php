<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'wallet_id',
        'booking_id',
        'transaction_id',
        'payment_method',
        'amount',
        'status',
        'account_number', // ✅ new field added
    ];

    /**
     * Casts
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * 🔹 Relation: PaymentTransaction belongs to ServiceProvider
     */
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * 🔹 Relation: PaymentTransaction belongs to Wallet
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * 🔹 Relation: PaymentTransaction belongs to Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * 🔹 Helper methods for status
     */
    public function isIncoming(): bool
    {
        return $this->status === 'incoming';
    }

    public function isWithdraw(): bool
    {
        return $this->status === 'withdraw';
    }
}
