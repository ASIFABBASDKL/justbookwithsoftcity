<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $guarded = [];

    public function job()
    {
        return $this->belongsTo(JobPosted::class, 'job_id');
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }

    public function milestones()
    {
        return $this->hasMany(ProposalMilestone::class);
    }
}
