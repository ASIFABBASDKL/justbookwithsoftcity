<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_user_id',
        'service_provider_id',
        'payment_method',
        'account_holder_name',
        'account_name',
        'account_title',
        'account_number',
        'sort_no',
    ];

    /**
     * 🔹 Relation: Payment belongs to a ServiceUser
     */
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }

    /**
     * 🔹 Relation: Payment belongs to a ServiceProvider
     */
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
