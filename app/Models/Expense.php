<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'expenses_date',
        'colocation_id',
        'payer_id',
        'category_id'
    ];
}
