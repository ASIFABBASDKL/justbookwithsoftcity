<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigPackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
    ];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
