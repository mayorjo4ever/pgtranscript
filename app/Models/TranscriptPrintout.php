<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptPrintout extends Model
{
    protected $fillable = [
        'regno','approve_date','type','purpose','printed','print_count',
        'sec_id','dean_id','auhor_id','created_by',
        'request_id'
    ];
}
