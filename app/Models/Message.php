<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
        'flagged' => 'boolean',
    ];
}
