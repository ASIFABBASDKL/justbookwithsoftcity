<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    use HasFactory;

    protected $table = 'seller_profiles';

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
        'headline',
        'bio',
        'hourly_rate',
        'languages',
        'level',
        'response_time_mins',
        'completion_rate',
        'on_time_rate',
        'total_earnings',
    ];

    protected $casts = [
        'languages' => 'array',
        'hourly_rate' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'on_time_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'service_provider_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_provider_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'seller_skill', 'seller_id');
    }

    public function gigs()
    {
        return $this->hasMany(Gig::class, 'seller_id');
    }

    public function servicesAndPricing()
    {
        return $this->hasMany(ServiceAndPricing::class, 'service_provider_id');
    }
}
