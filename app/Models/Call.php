<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'service_user_id',
        'duration',
        'type',
        'call_time',
    ];

    // 🔹 Relations
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
