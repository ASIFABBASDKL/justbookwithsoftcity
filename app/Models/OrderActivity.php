<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderActivity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
}
