<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(DeliveryFile::class, 'delivery_id');
    }
}
