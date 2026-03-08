<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hymn extends Model
{
   protected $fillable = ['number', 'title', 'lyrics'];
}
