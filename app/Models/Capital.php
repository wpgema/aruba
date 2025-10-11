<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    protected $fillable = [
        'type',
        'date',
        'amount',
        'description',
    ];
}
