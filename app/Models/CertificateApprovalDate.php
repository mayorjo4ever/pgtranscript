<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateApprovalDate extends Model
{
    //
    protected $table = 'certificate_approval_dates'; 
    protected $fillable = ['app_date']; 
    
    public function certificateData(){
        return $this->hasMany(CertificateData::class,'approve_date_id');
    }
}
