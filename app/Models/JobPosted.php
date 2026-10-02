<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosted extends Model
{
    protected $table = 'jobs_posted';

    protected $guarded = [];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skill', 'job_id');
    }

    public function attachments()
    {
        return $this->hasMany(JobAttachment::class, 'job_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'job_id');
    }
}
