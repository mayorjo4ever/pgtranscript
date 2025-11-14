<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CertificateApprovalDate;
use App\Models\CertificateData;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Transcript;
use App\Models\TranscriptCoverLetter;
use App\Models\TranscriptOfficial;
use App\Models\TranscriptPrintout;
use App\Models\TranscriptReport;
use App\Models\TranscriptsImport;
use App\Models\TranscriptsRequest;
use App\Services\GoogleSheetService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use function admin_info;
use function back;
use function extractDegreeInfo;
use function program_available;
use function redirect;
use function response;
use function swapName;
use function view;

class TranscriptRequestController extends Controller
{
    public function __construct() {
        $faculties = Faculty::all();        
        Session::put('faculties',$faculties);
    }
    public function import_requests(Request $request){
        Session::put('page','transcripts');  Session::put('tab','import');
        Session::put('page_title','Import New Transcript Requests');

       $page_info = ['title'=> "Transcript Requests",'icon'=>'pe-7s-person_add','sub-title'=>'Education is the best legacy'];

       return view('admin.transcripts.importer',compact('page_info'));
    }

      public function pending_requests(Request $request,$param = null){
        Session::put('page','transcripts');  Session::put('tab','pending');
        Session::put('page_title','Pending Transcript Requests');

       $page_info = ['title'=> "Transcript Requests",'icon'=>'pe-7s-person_add','sub-title'=>'Education is the best legacy'];       
       $pendings = TranscriptsRequest::orderBy('id','desc')->orderBy('request_status','asc')->paginate(100);
        
       if($request->isMethod('post')){
           // print "<pre>"; print_r($request->all()); die;           
           $param = $request->input('search'); 
           Session::put('transcript_search',$param);
           $pendings = TranscriptsRequest::
                  Where(DB::raw("CONCAT_WS(' ', surname, middle_name)"), 'LIKE', "%{$param}%")
                    ->orWhere('regno', 'LIKE', "%{$param}%")                   
                    ->orWhere('rrr', 'LIKE', "%{$param}%")
                    ->orWhere('applicant_email', 'LIKE', "%{$param}%")
                    ->paginate(100); 
       }
        
       return view('admin.transcripts.pending',compact('page_info','pendings'));
    }
    
    public function process_requests($param){
        $info = explode("|",base64_decode($param)); 
        $request = TranscriptsRequest::findOrFail($info[0]); 
        # print "<pre>"; print_r($request->toarray());  die; 
        Session::put('page','transcripts');  Session::put('tab','pendings');
        Session::put('page_title','Process Transcript Requests');
        $page_info = ['title'=> $request->regno. " - ". $request->surname. "  ". $request->middle_name. " - Transcript Requests ",'icon'=>'pe-7s-person_add','sub-title'=>''];       
        
        return view('admin.transcripts.processing',compact('page_info','request'));    
    }

    public function completed_requests(Request $request){
        Session::put('page','transcripts');  Session::put('tab','completed');
        Session::put('page_title','Completed Transcript Requests');

        $page_info = ['title'=> "Transcript Requests",'icon'=>'pe-7s-person_add','sub-title'=>'Education is the best legacy'];

       return view('admin.transcripts.completed',compact('page_info'));
    }

    public function sync_requests(Request $request){
       if($request->ajax()){
           $history = TranscriptsImport::latest()->first();
           $newRow = (empty($history)) ? 2 :  2 + $history['cum_total'];

           # connect to google sheet and get new records
           $range = "Sheet1!A{$newRow}:A";
           $service = new GoogleSheetService($range);
           $counts = $service->countRows();
           return response($counts);
       }
    }

    ###################
     public function import_latest_requests(Request $request){
       if($request->ajax()){  $data = $request->all();
           $history = TranscriptsImport::latest()->first();
           $newRow = (empty($history)) ? 2 : 2 + $history['cum_total'];

           $tofetch = ($data['maxno'] <= 30) ? $data['maxno'] : 30;
           $lastRow = $tofetch + $newRow - 1;
           $range = "Sheet1!A{$newRow}:AD{$lastRow}";
           # connect to google sheet and get new records
           $service = new GoogleSheetService($range);
           $values = $service->readSheet();

           ## calculate initial sum of records in history
           $sum = TranscriptsImport::sum('rows');
           $newsum = $sum + $tofetch;

           ## unwanted purpose
           $unwanted_op = "(to be sent directly to an institution/establishment )";
           $unwanted_sp = "(personal copy)";

          DB::beginTransaction();
           try{
           # print "<pre>";
           foreach($values as $row):
               $row13 = str_replace($unwanted_op,"",$row[13]??"");
               $row13 = str_replace($unwanted_sp,"",$row13);
               
            # print_r($row); 
               /***/
               TranscriptsRequest::create([
                'request_time' => $row[0],
                'request_email' => $row[2],
                'applicant_email' => $row[3] ?? "",
                'regno' => $row[4]??"",
                'surname' => $row[5]??"",
                'middle_name' => $row[6]??"",
                'year_of_entry' => $row[7]??"",
                'year_of_graduation' => $row[8]??"",
                'degree_awarded' => $row[9]??"",
                'faculty' => $row[10]??"",
                'department' => $row[11]??"",
                'request_type' => $row[12]??"",
                'request_purpose' =>$row13 ??"",
                'reference_number' => $row[14]??"",
                'destination_address' => $row[15]??"",
                'rrr' => str_replace("-", "", $row[16]),
                'mode_of_postage' => $row[17]??"",
                'applicant_phone' => $row[18]??"",
                'courier_agent' => $row[19]??"",
                'receiving_body_email' => $row[20]??"",
                'obtained_transcript_before' => $row[21]??"",
                'date_obtained' => $row[22]??"",
                'certificate_url' => $row[23]??"",
                'rrr_receipt_url' => $row[24]??"",
                'courier_receipt_url' => $row[25]??"",
                'pgschool_receipt_url' => $row[26]??"",
                'applicant_dob' => $row[28]??"",
                'applicant_dob_cert' => $row[29]??"",
                ]);   
              endforeach;

           ## now update the import history
              /***/
            TranscriptsImport::create([
               'rows'=>$tofetch,
               'cum_total'=>$newsum,
               'created_by'=>Auth::id(),
           ]);   
         DB::commit(); 
         return response()->json(['status'=>'success','message'=>"{$tofetch} Records Imported Successfully "],
          Response::HTTP_OK);
           
        }
        catch(Exception $e){
               DB::rollBack();
               return response()->json(['status'=>'error','message'=> $e->getMessage()],
                    Response::HTTP_INTERNAL_SERVER_ERROR);
           }
       } ## enf if Ajax
    } ## end Function

    public function search_transcript(Request $request){
       # print "<pre>"; 
       ## print_r($request->all()); die; 
        $request_id = $request->request_id;
        $request_type = $request->request_type; // official / student
        $reports = TranscriptReport::where('regno',$request->regno)
                ->where('type','pgd_master')->get();
        $printout = TranscriptPrintout::where('request_id',$request_id)->first();
        $cover_letter = TranscriptCoverLetter::where('request_id',$request_id)->first();
        
        # print_r($reports->toarray()); die; 
        
        return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.request.search_result')
                 ->with(compact('reports','request_id','request_type','printout','cover_letter'))
            ]);
         // transcript        
    }
    
    public function search_phd_transcript(Request $request){
//        print "<pre>"; 
//        print_r($request->all()); die; 
        $request_id = $request->request_id;
        $regno = $request->regno;
        $request_type = $request->request_type; // official / student
        $reports = TranscriptReport::where('regno',$request->regno)
                ->where('type','phd')->get();                
        $printout = TranscriptPrintout::where('request_id',$request_id)->first();
        $cover_letter = TranscriptCoverLetter::where('request_id',$request_id)->first();
        
        # print_r($reports->toarray()); die; 
        
        return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.request.phd_search_result')
                 ->with(compact('reports','request_id','regno','request_type','printout','cover_letter'))
            ]);
         // transcript        
    }
    
    public function schedule_request(Request $request, $param=null){
       $request_info = explode("|",base64_decode($param)); 
       $info = array_map('base64_decode',$request_info); 
       # $info = ( $regno | $purpose | $approve_date | $request_id | $type=official ) 
       $report = TranscriptReport::where('regno',$info[0])
               ->where('approve_date',$info[2])
               ->first(); 
       #  print "<pre>";    
       $approve_date_id = CertificateApprovalDate::firstOrCreate(['app_date'=>$info[2]]);
       #print "Approve date : ".  $approve_date_id->id;
       $degree = extractDegreeInfo($report->programme);
       $progid = program_available($degree['id'],$degree['field']);
       $year = explode('-',$info[2])[0]; 
       $fullName = swapName($report->name);

       $programme = CertificateData::updateOrCreate(
            ['regno'=>$report->regno,
                'approve_date_id'=>$approve_date_id->id,
              'raw_programme'=>$report->programme],[
              'raw_name'=>$fullName,'name'=>$fullName,             
              'degree_id'=>$degree['id'],'programme_id'=>$progid['id'],
              'completed'=>1, 'status'=>1,'year'=>$year]);
                     
        $faculties = Faculty::all();        
        Session::put('faculties',$faculties);
        Session::put('purpose',$info[1]);
        Session::put('request_id',$info[3]);
        Session::put('type',strtolower($info[4]));
        
        
       Session::put('page','transcripts');  Session::put('tab','pendings');
       Session::put('page_title','Process Transcript Requests');
       $page_info =  ['title'=>$report->name,'icon'=>'pe-7s-person_add','sub-title'=>''];       
          
            
        if($request->isMethod('post')):          
            #print "<pre>";  
            #print_r($request->all());  die;
             $request->validate([
                'first_reg_date' => 'required|date',
                'approve_date' => 'required|date',
            ]);
            $firstReg = Carbon::parse($request->first_reg_date);
            $approve = Carbon::parse($request->approve_date);
            $author_id = Auth('admin')->user()->id; $admin = Admin::find($author_id); 
            $dean = TranscriptOfficial::where(['post'=>'dean','is_current'=>1])->first();
            $secretary = TranscriptOfficial::where(['post'=>'secretary','is_current'=>1])->first();
            
            if ($approve->lessThan($firstReg->addYear())):
                return back()->withErrors([
                    'approve_date' => 'First Registration Date  must be at least one year before the Approval Date .'
                ])->withInput();
            ### complete the transcript            
            endif;
            
                Transcript::where('regno',$request->regno)
                    ->where('approve_date',$request->approve_date)
                     ->update(['completed'=>1]); 
                
                TranscriptReport::updateOrCreate(
                    ['regno'=>$request->regno,'approve_date'=>$request->approve_date],
                    ['name'=>$request->stud_name,
                        'fact_id'=>$request->faculty,
                        'dept_id'=>$request->department,
                        'programme'=>$request->programme,
                        'first_reg_date'=>$request->first_reg_date,
                        'author_id'=>$author_id,
                        'created_by'=>$admin->regno                    
                    ]);
                 TranscriptPrintout::updateOrCreate(
                    ['regno'=>$request->regno,'approve_date'=>$request->approve_date,
                        'purpose'=>$request->purpose,'request_id'=>$request->request_id],
                        ['sec_id'=>$secretary->regno,                           
                        'dean_id'=>$dean->regno,
                        'type'=>$request->transcript_type,
                        'author_id'=>Auth::id(),
                        'created_by'=>$admin->regno                    
                    ]);   
                    TranscriptsRequest::where('id',$request->request_id)
                            ->update(['request_status'=>'Treated']);
                    
            return redirect()->back()->with('success_message',$request->stud_name."'s Transcript Successfully Completed");
           # print "<pre>";  print_r($request->all());  die;
              
        endif;
        
        return view('admin.transcripts.request.processing',compact('page_info','request','programme','report')); 
        
    }
     
    public function transcript_request_memo(Request $request, $param=null){
       $request_info = explode("|",base64_decode($param)); 
       $info = array_map('base64_decode',$request_info); 
       # $info = ( $regno | $purpose | $approve_date | $request_id | $type=official ) 
       $report = TranscriptReport::where('regno',$info[0])
               ->where('approve_date',$info[2])
               ->first(); 
       $student_request = TranscriptsRequest::with('cover_letter')->find($info[3]);
       $page_info =  ['title'=>$report->name,'icon'=>'pe-7s-person_add','sub-title'=>''];       
       
       if($request->isMethod('post')){
//        print "<pre>";     
//        print_r($request->all()); die; 
//           
           $secretary = TranscriptOfficial::where(['post'=>'secretary','is_current'=>1])->first();
           $author_id = Auth('admin')->user()->id; $admin = Admin::find($author_id); 
           
           TranscriptCoverLetter::updateOrCreate(
                   ['regno'=>$request->regno,'name'=>$request->name,
                       'request_id'=>$request->request_id],
                   ['destination_address'=>$request->receiving_body,
                       'wes_ref_no'=>$request->wes_ref_no ?? "",
                    'sec_id'=>$secretary->regno,'author_id'=>Auth::id(),
                    'created_by'=>$admin->regno]
           );
           return redirect()->back()->with('success_message',$request->regno."'s Transcript Memo Successfully Completed");
       }
       return view('admin.transcripts.request.memo',compact('page_info','student_request','report')); 
    }
    
    public function schedule_phd_transcript(Request $request, $param=null){
      
       $request_info = explode("|",base64_decode($param)); 
       $info = array_map('base64_decode',$request_info); 
       # $info = ( $regno | $purpose | $request_id | $type=official ) 
       $report = TranscriptReport::where('regno',$info[0])
               ->where('type','phd')
               ->first(); 
       $student_request = TranscriptsRequest::with('cover_letter')->find($info[2]);
       $phd_progs = Programme::where('degree_id',2)->orderBy('name','asc')->get();
       
       $page_info =  ['title'=>'Schedule PhD Transcript For : '.$info[0],'icon'=>'pe-7s-person_add','sub-title'=>''];       
        
      $printouts = TranscriptCoverLetter::with('request')->where('regno',$info[0])->get(); 
      # print "<pre>";  print_r($printouts->toarray()); die;  
       
      if($request->isMethod('post')){    
           
           $secretary = TranscriptOfficial::where(['post'=>'secretary','is_current'=>1])->first();
           $author_id = Auth('admin')->user()->id; $admin = Admin::find($author_id); 
           
         TranscriptReport::updateOrCreate(
                    ['regno'=>$request->regno,'approve_date'=>$request->approve_date],
                    ['name'=>$request->name,
                        'fact_id'=>$request->faculty,
                        'dept_id'=>$request->department,
                        'programme'=>$request->programme,
                        'type'=>'phd',
                        'author_id'=>$author_id,
                        'created_by'=>$admin->regno                    
                    ]);           
           
           $coverLetter = TranscriptCoverLetter::updateOrCreate(
                   ['regno'=>$request->regno,'name'=>$request->name,
                       'request_id'=>$request->request_id],
                   ['destination_address'=>$request->receiving_body,
                       'wes_ref_no'=>$request->wes_ref_no ?? "",
                    'sec_id'=>$secretary->regno,'author_id'=>Auth::id(),
                    'created_by'=>$admin->regno]
           );
           
          TranscriptsRequest::where('id',$request->request_id)
                ->update(['request_status'=>'Treated','transcript_cover_letter_id'=>$coverLetter->id]);
           return redirect()->back()->with('success_message',$request->regno."'s Transcript Successfully Completed");
       }
       # 
       return view('admin.transcripts.request.phd_transcript',compact('page_info','printouts','student_request','report','info','phd_progs')); 
    }
    
    public function memo_printing($url) {
        $durl = base64_decode($url); # decode url
        $info = explode('|',$durl); # matricno, memo_id  
       # print "<pre>"; print_r($info); 
       
        $memo = TranscriptCoverLetter::findorFail($info[1]);
       # print_r($memo->toArray()); 
        
        return View('admin.transcripts.request.memo_printout',
                compact('memo')); 
    }
    
    public function memo_printing_phd($url) {
        $durl = base64_decode($url); # decode url
        $info = explode('|',$durl); # matricno, memo_id  
        # print "<pre>"; # print_r($info); 
       
        $memo = TranscriptCoverLetter::findorFail($info[1]);
        $transcript = TranscriptReport::where('regno',$info[0])
                ->where('type','phd')->first(); 
       
        #print_r($memo->toArray()); 
       #  print_r($printouts->toArray()); 
       
       # die; 
        
        return View('admin.transcripts.request.phd_transcript_printout',
                compact('memo','transcript')); 
    }
    
    public function memoPrintoutUpdate($id) {
        $printout = TranscriptCoverLetter::findOrFail($id);
        $initial_count = $printout->print_count; 
        $new_count = $initial_count + 1; 
        $admin = admin_info(Auth::id());
        ## do update
        $printout->printed = 1; 
        $printout->print_count = $new_count; 
        $printout->printed_by = $admin['regno'];
        $printout->save(); 
        
        $msg = "$new_count Memo Has Been Printed Successfully For ".$printout->regno; 
        $msg.= " By ".$admin['regno'];
        return response()->json([
            'type'=>'success',
            'message'=>$msg
        ]);
    }
}
