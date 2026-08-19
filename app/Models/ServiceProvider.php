<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'category',
        'service_type',
        'id_verification',
        'country',
        'driving_license',
        'passport',
        'gmc_dbs_number',
        'portfolio',
        'experience_years',
        'image',
        'description',
    ];

    /**
     * 🔹 Relation: ServiceProvider belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🔹 Relation: ServiceProvider has one Wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'service_provider_id');
    }

    /**
     * 🔹 Relation: ServiceProvider can have many Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_provider_id');
    }

    /**
     * 🔹 Relation: ServiceProvider has many Services & Pricing
     */
    public function servicesAndPricing()
    {
        return $this->hasMany(ServiceAndPricing::class, 'service_provider_id');
    }
}
