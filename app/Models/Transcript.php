<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    //
    protected $fillable =['regno','code','title','units','type','level',
        'score','approve_date','semester','starred','author_id','created_by'];
  
    public function course(){
            return $this->belongsTo(Course::class, 'code', 'code');
        }
    
}
