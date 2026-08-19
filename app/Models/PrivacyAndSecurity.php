<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyAndSecurity extends Model
{
    use HasFactory;

    protected $table = 'privacy_and_security';

    protected $fillable = [
        'text',
        'status', // agree / decline
    ];
}
