<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_provider_id',
        'city',
        'building',
        'apartment',
        'floor',
        'street',
        'live_location',
        'latitude',
        'longitude',
    ];

    /**
     * 🔹 Relation: ServiceArea belongs to ServiceProvider
     */
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
