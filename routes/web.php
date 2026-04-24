<?php

use App\Http\Controllers\Admin\DriveDownloadController;
use App\Http\Controllers\Bible\TelegramBotController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DateController;
use App\Models\TranscriptReport;
use App\Models\TranscriptsRequest;
use App\Models\CertificateData;
use App\Models\TranscriptPrintout;
use App\Models\Transcript;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController; 


//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', function () {
    // return view('admin.login');
    return redirect('portal/login');
});

 Route::get('login',function () {
    return redirect('portal/login');
    })->name('login');
    
 Route::get('register',function () {
    return redirect('portal/login');
    }) ->name('register');


## login page
##===================
Route::prefix('/portal')->namespace('App\Http\Controllers\Portal')->group(function(){
    Route::match(['get','post'],'login','LoginController@login');
    Route::match(['get','post'],'forgot-password','LoginController@forgot_password');
    Route::get('logout','LoginController@logout');
});

// Admin dashboard without admin
 Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')
         ->group(function(){

    Route::group(['middleware'=>['auth:admin', 'admin.active']],function(){

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
        Route::match(['get','post'],'sent-transcript-requests','TranscriptRequestController@sent_requests');
        Route::match(['get','post'],'transcripts','TranscriptRequestController@completed_requests');
        Route::post('search-my-transcript','TranscriptRequestController@search_transcript');
        Route::post('search-my-phd-transcript','TranscriptRequestController@search_phd_transcript');
        Route::match(['get','post'],'schedule-transcript-request/{param}','TranscriptRequestController@schedule_request'); // 
        Route::match(['get','post'],'schedule-transcript-memo/{param}','TranscriptRequestController@transcript_request_memo');
        
        ## sending of transcript request mail 
        Route::post('send-transcript-mail','TranscriptRequestController@send_transcript_mail'); 
        
        
        # transcript search 
        Route::match(['get','post'],'transcript-search','TranscriptController@transcript_search');
        Route::get('transcript-reconfiguration/{param}','TranscriptController@reconfigure_transcript');        
        Route::post('add-new-student','TranscriptController@add_new_student');
        # transcript-
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
       
        ## transcript request 
        Route::post('update-transcript-request-body/{id}','TranscriptRequestController@bodyUpdate');
        Route::post('update-transcript-request-issue','TranscriptRequestController@updateRequestIssue');
        Route::post('update-these-requests-as-sent','TranscriptRequestController@updateCompletedToSent');
        
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
         # google-id-card-picturee-download
         Route::get('/google-id-card', 'DriveDownloadController@index');
         Route::post('/google-id-card-upload', 'DriveDownloadController@uploadExcel');
         
        # DATABASE BACKUP & RESTORE 
        Route::get('data-backup-restore','DatabaseController@backup_restore');
        Route::post('backup-db','DatabaseController@backup_db');
        #Route::post('restore-db','DatabaseController@restore_db');
        Route::post('restore-sql','DatabaseController@restoreSql');
        
        Route::post('upload-new-student-data','UsersController@import');
        
        Route::get('id-card-requests','UsersController@id_card_requests');
        Route::post('sync-id-card-requests','UsersController@sync_card_requests');
        Route::post('import-latest-id-card-requests','UsersController@import_latest_card_requests');
       
        
        #managing roles and permission           
        Route::group(['middleware' => ['role:super-admin']], function () {
          Route::get('roles','RoleController@viewRoles');
          Route::get('permissions','RoleController@viewPermissions');
          Route::match(['get','post'],'add-edit-role/{id?}','RoleController@addEditRole');
          Route::match(['get','post'],'add-edit-permission/{id?}','RoleController@addEditPermission');
          Route::get('role-permission','RoleController@rolesPermission');
          Route::post('load-permissions','RoleController@loadPermissions');
          Route::post('change-role-permission','RoleController@changeRolePermission');
         }); ## end middleware
         
        Route::get('activity/live', 'AdminController@liveActivity')->name('admin.activity.live');            
        Route::get('/fix-dates', function () {                               
        /*
        TranscriptPrintout::where('regno','05/66MF075')
                ->update(['approve_date'=>'2011-12-30']); 
        TranscriptReport::where('regno','05/66MF075')
                ->update(['approve_date'=>'2011-12-30']); 
        */
        
        $data = Transcript::where('regno','21/68EZ003')
                    ->get(); 
                    
                // ->update(['approve_date'=>'2011-12-30']);


        # $printout = TranscriptPrintout::where('regno','05/66MF075')->get(); 
        # $report = TranscriptReport::where('regno','05/66MF075')->get();             
        
        //$data = CertificateData::where('regno','11/66MC067')->get(); 
            // CertificateData::whereIn('id',[8351,8350,8353,8352])
            //             ->where('approve_date_id',280)
            //             ->delete(); 

        // $data = CertificateData::whereIn('regno',['14/56ED115',
        //             '12/67QV020',
        //             '21/68OJ013',                    
        //             '14/56ED115',                                      
        //             '18/68QY001'])
        //         ->where('approve_date_id',280)
        //         ->get();         
        

        // $data->update(['approve_date_id'=>104,'year'=>'2014']);

        print "<pre>"; 
        print_r($data->toarray()); 
         /* 
         */
        ## print_r($printout->toarray()); 
        ## print_r($report->toarray()); 
        
        #
        
        # $req = TranscriptsRequest::where('regno','17/AM68002')->get();
        /*
        # TranscriptsRequest::where('regno','14/25PC196')->update(['reference_number'=>'B261ADMBAT']);
        $req = TranscriptsRequest::find(3579);
        $req->update(['regno'=>'17/68AM002']);
            print "<pre>"; 
            print_r($req->toarray());
        # $report = TranscriptReport::where('regno','14/25PC196_error')->update(['regno'=>'14/25PC196']); 
        # $report = TranscriptReport::where('regno','14/25PC196')->get(); #->update(['regno'=>'14/25PC196_error']); 
        # print_r($report->toarray()); 
        */  
                  
     });       
    });
  });

  Route::get('/downloads/passports-signatures', [DownloadController::class, 'download'])
       ->name('downloads.bulk');
  
  Route::get('/google/callback', [DriveDownloadController::class, 'authCallback'])->name('google.callback');
  

  Route::prefix('bible')->group(function () {
    Route::post('/webhook', [TelegramBotController::class, 'webhook'])
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
   });

  Route::get('/date-conversion', [DateController::class, 'index']);
  Route::post('download-clean-dates', [DateController::class, 'download']);
  
    Route::get('/bot/dashboard', fn () => view('bot.dashboard'));

    Route::get('/api/bot/status', [BotController::class, 'status']);
    Route::get('/api/bot/trades', [BotController::class, 'trades']);
    Route::post('/api/bot/settings', [BotController::class, 'updateSettings']);
    Route::get('/api/bot/chart', [BotController::class, 'chart']);

## require __DIR__.'/auth.php';
