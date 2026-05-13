<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranscriptsRequest extends Model
{
    use HasFactory;
    
    protected $fillable = ['form_response_id',
        'request_time', 'request_email',
        'applicant_email', 'regno',
        'surname','middle_name',
        'year_of_entry','year_of_graduation',
        'degree_awarded','faculty',
        'department', 'request_type',
        'request_purpose', 'reference_number',
        'destination_address','request_status',
        'rrr','	rrr_receipt_url',
        'mode_of_postage', 'applicant_phone',
        'courier_agent','receiving_body_email',
        'obtained_transcript_before',
        'date_obtained','certificate_url',
        'courier_receipt_url',
        'pgschool_receipt_url',
        'applicant_dob', 'applicant_dob_cert',
        'transcript_cover_letter_id',
        'bodies','last_viewer','last_viewed',
        'progression','mail_sent','sent_by',
        'date_sent','sent_count','last_sent_email',
        'request_time_dt','printout_url','memo_url'
    ];
    
     public function printout(){
        return $this->hasOne(TranscriptPrintout::class,'request_id'); 
    }
    
     public function cover_letter(){
        return $this->hasOne(TranscriptCoverLetter::class,'request_id'); 
    }
    
    protected $casts = [
    'request_time_dt' => 'datetime',
    ];
    
    public function setRequestTimeAttribute($value)
        {
            $this->attributes['request_time'] = $value;

            $this->attributes['request_time_dt'] =
                \Carbon\Carbon::createFromFormat(
                    'm/d/Y H:i:s',
                    trim($value)
                );
        }


    
    
    
}
