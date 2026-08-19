<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    protected $fillable = [
        'service_provider_id',
        'total_amount',
        'total_available_amount',
        'total_withdrawal_amount',
    ];

    /**
     * 🔹 Relation: Wallet belongs to a ServiceProvider
     */
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
