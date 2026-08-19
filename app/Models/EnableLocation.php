<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnableLocation extends Model
{
    use HasFactory;

    protected $table = 'enable_locations';

    protected $fillable = [
        'service_provider_id',
        'is_location_enabled',
        'latitude',
        'longitude',
    ];

    /**
     * 🔹 Relation: EnableLocation belongs to a ServiceProvider
     */
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
