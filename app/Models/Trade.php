<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'symbol',
        'side',
        'price',
        'quantity',
        'order_id',
        'executed_at',  
    ];
}
