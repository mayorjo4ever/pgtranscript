<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptCoverLetter extends Model
{
   protected $fillable = [
        'regno','name','request_id','wes_ref_no',
        'destination_address','sec_id','printed','print_count',
        'printed_by','author_id','created_by'
   ];
   
   public function request (){
       return $this->hasOne(TranscriptsRequest::class);
   }
           
}
