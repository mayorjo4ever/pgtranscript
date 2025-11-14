<?php

use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
     return view('admin.login');
    //return redirect('portal/login');
});


## login page
##===================
Route::prefix('/portal')->namespace('App\Http\Controllers\Portal')->group(function(){
    Route::match(['get','post'],'login','LoginController@login');
    Route::match(['get','post'],'forgot-password','LoginController@forgot_password');
    Route::get('logout','LoginController@logout');
});

// Admin dashboard without admin
 Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(function(){

    Route::group(['middleware'=>['admin']],function(){

        Route::get('dashboard','AdminController@dashboard');
        Route::get('/test-google-sheet', function (GoogleSheetService $googleSheetService) {
            dd($googleSheetService);
        });
        
        // transcript-requests
        Route::post('sync-transcript-requests','TranscriptRequestController@sync_requests');
        Route::post('import-latest-transcript-requests','TranscriptRequestController@import_latest_requests');
        Route::match(['get','post'],'import-transcript-requests','TranscriptRequestController@import_requests');
        Route::match(['get','post'],'pending-transcript-requests','TranscriptRequestController@pending_requests');        
        Route::match(['get','post'],'process-transcript-requests/{param}','TranscriptRequestController@process_requests');        
        Route::match(['get','post'],'completed-transcript-requests','TranscriptRequestController@completed_requests');
        Route::match(['get','post'],'transcripts','TranscriptRequestController@completed_requests');
        Route::post('search-my-transcript','TranscriptRequestController@search_transcript');
        Route::post('search-my-phd-transcript','TranscriptRequestController@search_phd_transcript');
        Route::match(['get','post'],'schedule-transcript-request/{param}','TranscriptRequestController@schedule_request'); // 
        Route::match(['get','post'],'schedule-transcript-memo/{param}','TranscriptRequestController@transcript_request_memo');
        
        // phd transcript 
        Route::match(['get','post'],'schedule-phd-transcript/{param}','TranscriptRequestController@schedule_phd_transcript');
        
        
        // transcripts for graduating students         
        Route::match(['get','post'],'pending-graduation-transcripts/','ConvocationTranscriptController@pending_transcripts');
        Route::match(['get','post'],'completed-graduation-transcripts/','ConvocationTranscriptController@completed_transcripts');
        Route::match(['get','post'],'transcript-processing/{param}','TranscriptController@process_transcripts');        
        Route::post('search-this-course','TranscriptController@search_course');
        Route::post('add-this-course','TranscriptController@add_course');
        Route::post('remove-this-course','TranscriptController@remove_course');
        Route::post('load-my-transcript','TranscriptController@load_transcript');
        Route::post('load-departments','TranscriptController@load_departments');
        
        ## printing of transcript
        Route::get('print-transcript/{url}','TranscriptController@printing');        
        Route::get('print-memo/{url}','TranscriptRequestController@memo_printing');
        Route::get('print-phd-transcript/{url}','TranscriptRequestController@memo_printing_phd');
        Route::post('update-transcript-printout/{id}','TranscriptController@printoutUpdate');
        Route::post('update-memo-printout/{id}','TranscriptRequestController@memoPrintoutUpdate');
       
        ## CERTIFICATES
        Route::get('cert-settings','CertificateController@cert_setting_index');
        Route::get('cert-data-upload','CertificateController@cert_data_upload_view');
        Route::get('cert-data-processing','CertificateController@cert_data_process_view');
        Route::get('cert-data-search','CertificateController@cert_data_search_view');
        Route::post('upload-certificate-passports','CertificateController@uploadMultipleImages');
        Route::post('upload-nysc-passports','CertificateController@uploadNyscImages');
        #Route::post('upload-certificate-passports','CertificateController@uploadMultiplePassports');
        #Route::post('upload-certificate-passports','CertificateController@uploadPassports');
        Route::post('upload-certificate-excel-docs','CertificateController@uploadExcelDocs');
        Route::get('phd-graduands','CertificateController@phd_graduands');
        Route::get('master-graduands','CertificateController@master_graduands');
                
        // PROCESSING CERTIFICATES
        Route::post('load-uploaded-cert-programmes','CertificateController@load_uploaded_cert_programmes'); 
        Route::post('load-completed-cert-programmes','CertificateController@load_completed_cert_programmes'); 
        Route::post('load-uploaded-cert-student-groups','CertificateController@load_uploaded_student_groups'); 
        Route::post('load-uploaded-cert-student-by-programme','CertificateController@load_uploaded_student_by_programme'); 
        Route::post('normalize-cert-names','CertificateController@normalize_cert_names'); 
        Route::post('finalize-cert-names','CertificateController@finalize_cert_names'); 
        Route::post('definalize-cert-names','CertificateController@definalize_cert_names'); 
        Route::post('renamePassport','CertificateController@renamePassport'); 
        Route::post('modify-uploaded-cert-data','CertificateController@modify_uploaded_cert_data'); 
        Route::get('download-passports-zip', 'CertificateController@downloadPassportsZip');
        Route::post('download-selected-passports', 'CertificateController@downloadSelectedPassports');
        
        //certificate download 
        Route::post('download-certificate-data','CertificateController@download_certificate_data');
        Route::post('download-uncompleted-data','CertificateController@download_uncompleted_data');
        
        // certificate settings
        Route::post('set-default-cert-approval-date', 'CertificateController@set_default_cert_approval_date'); // 
        Route::post('add-update-cert-approve-date', 'CertificateController@add_update_cert_approve_date'); // 
        Route::post('check-programme-compatibility', 'CertificateController@check_programme_compatibility'); // 
        Route::post('create-programme-template','CertificateController@create_programme_template');
        Route::post('configure-programme-template','CertificateController@configure_programme_template');
        
        ## COURSES        
        Route::get('courses','CourseController@courses');
        Route::get('upload-courses','CourseController@course_upload_view');
        Route::post('upload-courses-excel-docs','CourseController@uploadExcelDocs');
        #  
        
        # Account Management
          Route::match(['get','post'], 'manage-password',
            'AdminController@managePassword');
          
         Route::get('import-users','UsersController@import_users');
         Route::get('date-conversion','GeneralController@uploader');
         Route::post('download-clean-dates','GeneralController@downloadCleanDates');
         
         Route::post('upload-new-student-data','UsersController@import');
         #upload-new-student-data
    });
  });


/**
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
**/

require __DIR__.'/auth.php';
