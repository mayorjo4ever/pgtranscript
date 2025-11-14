<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranscriptsImport extends Model
{
    use HasFactory;
    protected $fillable = [
        'rows',
        'cum_total',
        'created_by'
        ];
}
