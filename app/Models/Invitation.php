<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'colocation_id',
        'token',
        'expires_at',
        'accepted_at',
        'refused_at'
    ];
}
