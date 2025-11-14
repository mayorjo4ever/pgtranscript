<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = [
        'code','title','units','type','level','semester','host_department'
        ];
    
    public function transcripts(){
       return $this->hasMany(Transcript::class, 'code', 'code');
    }
    
    
    public function student_transcript($regno){
        return $this->hasOne(Transcript::class, 'code', 'code')
                ->where('regno', $regno);
        }
}
