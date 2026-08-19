<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'monday', 'monday_start', 'monday_end',
        'tuesday', 'tuesday_start', 'tuesday_end',
        'wednesday', 'wednesday_start', 'wednesday_end',
        'thursday', 'thursday_start', 'thursday_end',
        'friday', 'friday_start', 'friday_end',
        'saturday', 'saturday_start', 'saturday_end',
        'sunday', 'sunday_start', 'sunday_end',
    ];

    // 🔹 Relation with ServiceProvider
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    // 🔹 Helper function (check availability for a given day)
    public function isAvailableOn($day)
    {
        return (bool) $this->{$day};
    }

    // 🔹 Helper to get time range for a day
    public function getTimeRange($day)
    {
        if ($this->isAvailableOn($day)) {
            return [
                'start' => $this->{$day . '_start'},
                'end'   => $this->{$day . '_end'},
            ];
        }
        return null;
    }
}
