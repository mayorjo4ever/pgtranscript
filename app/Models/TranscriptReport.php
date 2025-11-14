<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptReport extends Model
{
   
    protected $table = 'transcript_reports'; 
     
//regno	name	fact_id	dept_id	programme	first_reg_date	approve_date	created_by

    protected $fillable = 
            [
                'regno','name','fact_id','dept_id',
                'programme','first_reg_date','approve_date',
                'created_by','type','author_id'
            ];
    
     public function certificateData(){
        return $this->belongsTo(CertificateData::class,'regno','regno');
    }
    
    public function printouts(){
        return $this->hasMany(TranscriptPrintout::class,'regno','regno'); 
    }
    
    
}
