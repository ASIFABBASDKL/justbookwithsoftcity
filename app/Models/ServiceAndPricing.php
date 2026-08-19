<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAndPricing extends Model
{
    use HasFactory;

    protected $table = 'services_and_pricing'; // custom table name

    protected $fillable = [
        'service_provider_id',
        'category',
        'subcategory',
        'service_name',
        'description',
        'img',
        'service_price',
        'service_duration',
    ];

    // 🔹 Relation with ServiceProvider
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

}
