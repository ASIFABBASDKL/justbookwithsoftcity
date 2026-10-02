<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectBalance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_refill_at' => 'datetime',
    ];
}
