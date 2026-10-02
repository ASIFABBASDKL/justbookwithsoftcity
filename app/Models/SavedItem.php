<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedItem extends Model
{
    protected $guarded = [];

    public function saveable()
    {
        return $this->morphTo();
    }
}
