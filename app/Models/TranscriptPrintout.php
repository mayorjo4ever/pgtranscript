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
    public function request(){
        return $this->belongsTo(TranscriptsRequest::class,'request_id');
    }
   
    public function report()
    {
        return TranscriptReport::where('regno', $this->regno)
            ->whereDate('approve_date', $this->approve_date)
            ->first();
    }

//   public function report()
//    {
//        return $this->hasOne(
//            TranscriptReport::class,
//            'regno',   // FK on reports
//            'regno'    // local key on printouts
//        )->whereColumn(
//            'transcript_reports.approve_date',
//            'transcript_printouts.approve_date'
//        );
//    }



}
