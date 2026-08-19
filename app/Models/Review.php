<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'booking_id',
        'service_user_id',
        'service_provider_id',
        'rating',
        'satisfaction',
        'response_rate',
        'job_success',
        'reliability',
        'comment',
        'is_visible',
    ];

    /* --------------------------
     | 🔹 Relationships
     -------------------------- */

    // Review belongs to a Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    // Review belongs to a Service User
    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    // Through booking -> service_area -> service_provider
    public function serviceProvider()
    {
        return $this->hasOneThrough(
            ServiceProvider::class,
            ServiceArea::class,
            'id',                   // ServiceArea.id
            'id',                   // ServiceProvider.id
            'booking_id',           // Review.booking_id -> Booking.id
            'service_provider_id'   // ServiceArea.service_provider_id
        );
    }
}
