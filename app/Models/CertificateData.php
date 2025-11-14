<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateData extends Model
{
   protected $table = 'certificate_data';
   
   protected $fillable = [
        'pix_name', 'filename','pix_path',
        'size','mime_type','regno','name',
        'raw_name','raw_programme',
        'approve_date_id','degree_id',
        'year','programme_id','degree_class'         
    // if you want to associate with users
    ];
     
    protected $casts = [
        'size' => 'integer',
    ];
    

    // Accessor for full URL
    public function getFullUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
    
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }
    
    public function degree()
    {
        return $this->belongsTo(Degree::class, 'degree_id');
    }
   
    public function app_date(){
        return $this->belongsTo(CertificateApprovalDate::class, 'approve_date_id');
    }
    

    public function user(){
        return $this->belongsTo(User::class, 'regno');
    }
    
    public function transcriptReport(){
        return $this->hasOne(TranscriptReport::class,'regno','regno')
            ->whereColumn('transcript_reports.approve_date','certificate_approval_dates.app_date');
    }
    
    public function printouts(){
        return $this->hasMany(TranscriptPrintout::class,'regno','regno');
    }
    
    public function getPassportPathAttribute()
    {
        // Get the approval date string, e.g. "2024-10-05"
        $approveDate = $this->app_date?->app_date;

        if (!$approveDate || !$this->pix_name) {
            // Return a default placeholder file path if missing
            return public_path('img/default-user.png');
        }

        // Extract the year (e.g. "2024")
        $year = explode('-', $approveDate)[0];

        // Build the directory structure like certificates/2024/2024-10-05
        ##$directory = "certificates/{$year}/{$approveDate}";
        $directory = "certificates/{$year}/{$approveDate}";
        $fullPath = asset("{$directory}/{$this->pix_name}.jpg");
        
        // Return absolute path to the file in storage
        
        # $fullPath = public_path("{$directory}/{$this->pix_name}{$ext}");

        // If file doesn't exist, return default placeholder
//        if (!file_exists($fullPath)) {
//            return public_path('img/default-user.png');
//        }

        return $fullPath;
    }
}
