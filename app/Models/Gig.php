<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'avg_rating' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (Gig $gig) {
            if (! $gig->slug) {
                $gig->slug = Str::slug($gig->title).'-'.Str::random(6);
            }
        });
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function packages()
    {
        return $this->hasMany(GigPackage::class);
    }

    public function extras()
    {
        return $this->hasMany(GigExtra::class);
    }

    public function gallery()
    {
        return $this->hasMany(GigGallery::class);
    }

    public function faqs()
    {
        return $this->hasMany(GigFaq::class);
    }

    public function requirements()
    {
        return $this->hasMany(GigRequirement::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'gig_skill');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
