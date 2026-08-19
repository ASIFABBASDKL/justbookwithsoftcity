<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_user_id',
        'service_provider_id',
        'services_and_pricing_id',
        'booking_date',
        'booking_time',
        'address',
        'frequency',
        'describe',
        'location_img',
        'special_request',
        'price',
        'subtotal',
        'discount',
        'tax',
        'service_charges',
        'emergency_booking',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
    ];

    /* --------------------------
     | 🔹 Relationships
     -------------------------- */

    // Booking belongs to a Service User (Customer)
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }

    // Booking belongs to a Service Provider
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    // Booking belongs to Services & Pricing
    public function servicesAndPricing()
    {
        return $this->belongsTo(ServiceAndPricing::class, 'services_and_pricing_id');
    }
}
