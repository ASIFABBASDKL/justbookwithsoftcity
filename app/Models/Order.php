<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'extras_total' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_earning' => 'decimal:2',
        'requirements_submitted_at' => 'datetime',
        'due_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_complete_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = 'JB-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            }
        });
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function milestones()
    {
        return $this->hasMany(OrderMilestone::class);
    }

    public function requirements()
    {
        return $this->hasMany(OrderRequirement::class);
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class);
    }

    public function revisions()
    {
        return $this->hasMany(OrderRevision::class);
    }

    public function activities()
    {
        return $this->hasMany(OrderActivity::class);
    }

    public function cancellations()
    {
        return $this->hasMany(OrderCancellation::class);
    }

    public function escrows()
    {
        return $this->hasMany(Escrow::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
