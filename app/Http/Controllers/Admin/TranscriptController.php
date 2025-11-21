<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CertificateApprovalDate;
use App\Models\CertificateData;
use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Transcript;
use App\Models\TranscriptOfficial;
use App\Models\TranscriptPrintout;
use App\Models\TranscriptReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use function admin_info;
use function back;
use function extractDegreeInfo;
use function program_available;
use function redirect;
use function response;
use function swapName;
use function view;


class TranscriptController extends Controller
{
    public function process_transcripts(Request $request,$param) {
        Session::put('page','transcripts');  Session::put('tab','pending-grads');
        $transcript_info = $this->get_transcript_info($param); 
        $title = $transcript_info['regno']." | ".$transcript_info['convocation']['name']; 
        $title.= " | ".ucwords($transcript_info['purpose'])."'s Transcript"; 
        # print "<pre>";  print_r($transcript_info);  die; 
        Session::put('page_title',$title);
        $page_info = ['title'=> $title,'icon'=>'book','sub-title'=>'View All '];
        
        $faculties = Faculty::all();        
        Session::put('faculties',$faculties);
        Session::put('purpose',$transcript_info['purpose']);
        Session::put('type','student'); // student copy by default - for 
           
        if($request->isMethod('post')):          
          #  print "<pre>";  
            # print_r();  die;
             $request->validate([
                'first_reg_date' => 'required|date',
                'approve_date' => 'required|date',
            ]);
            $firstReg = Carbon::parse($request->first_reg_date);
            $approve = Carbon::parse($request->approve_date);
            $author_id = Auth::id(); $admin = Admin::find($author_id); 
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
                        'purpose'=>$request->purpose],
                    ['sec_id'=>$secretary->regno,                           
                        'dean_id'=>$dean->regno,
                        'type'=>$request->transcript_type,
                        'author_id'=>Auth::id(),
                        'created_by'=>$admin->regno                    
                    ]);    
            return redirect()->back()->with('success_message',$request->stud_name."'s Transcript Successfully Completed");
           # print "<pre>";  print_r($request->all());  die;
              
        endif;
        
        return view('admin.transcripts.convo.processor',compact('page_info','transcript_info'));   
    }
   
    protected function get_transcript_info($param){
        $url = base64_decode($param); // joined by | - so split
        $infos = explode("|",$url); // default = regno | purpos(convocation , request) 
        $regno = base64_decode($infos[0]);
        $purpose = base64_decode($infos[1]);
        $approve_date_id = base64_decode($infos[2]);
        $convocation = "";
        switch($purpose){
            case "convocation" : {
                $convocation = CertificateData::with('user')->where('regno',$regno)
                    ->where('approve_date_id',$approve_date_id)
                    ->first()->toArray();
              } break;  
        }
        
        return ['regno'=>$regno,'purpose'=>$purpose,'approve_date_id'=>$approve_date_id,'convocation'=>$convocation];
    }
    
    
    public function search_course(Request $request) {
        $data = $request->all();
        $regno = $data['regno'];
        $search = $data['code'];
        $courses = Course::with(['transcripts' => function($q) use ($regno) {
            $q->where('regno', $regno);
        }])
        ->where(function($q) use ($search) {
            $q->where('code','LIKE','%'.$search.'%')
              ->orWhere('title','LIKE','%'.$search.'%');
        })
        ->get();
        return response()->json([
            'type'=>'success',
            'view' => (string) View::make('admin.transcripts.convo.course_loader')->with(compact('courses'))
        ]);
    }
//    
///**
//    public function search_course(Request $request) {
//        $data = $request->all();
//        $regno = $data['regno']; // student’s regno passed from request
//        $courses = Course::where('code','LIKE','%'.$data['code']."%")
//                ->orWhere('title','LIKE','%'.$data['code']."%")->get()
//                ->map(function ($course) use ($regno) {
//                // attach transcript for this student
//                $course->student_transcript = $course->transcripts()
//                    ->where('regno', $regno)
//                    ->first();
//                return $course;
//            });
//           # print "<pre>";  print_r($courses->toArray()); die; 
//        return response()->json(['type'=>'success',
//            'view'=>(String)View::make('admin.transcripts.convo.course_loader')->with(compact('courses'))
//            ]);
//    }
     
    
    public function add_course(Request $request) {
        $data = $request->all();
         ## print "<pre>";  #print_r($data);  
         $course = Course::where('code',$data['code'])->first(); 
         $author_id = Auth::id(); $admin = Admin::find($author_id);
         Transcript::updateOrCreate(
                 ['regno'=>$data['regno'],
                     'code'=>$course->code,
                     'approve_date'=>$data['approve_date']
                 ], [
                     'title'=>$course->title,
                     'units'=>$course->units,
                     'type'=>$course->type,
                     'level'=>$course->level,
                     'semester'=>$course->semester,
                     'score'=>$data['score'],
                     'starred'=>($data['starred']=='true')?1:0,
                     'author_id'=>$author_id,
                     'created_by'=>$admin->regno
                 ]
         );
         
         // print_r($course->toarray()); die;
        return response()->json(['type'=>'success',
            'message'=>$course->title.' successfully saved '
            ]);
    }
    // load transcript
    
    public function load_departments(Request $request) {
        $data = $request->all();
        $user_dept = $data['user_dept'];
         # print "<pre>";  print_r($data);  exit;                   
        $departments = Department::where('fact_id',$data['fact_id'])->get();
         
       return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.convo.department_loader')
                 ->with(compact('departments','user_dept'))
            ]);
    }
    
    public function remove_course(Request $request) {
        $data = $request->all();
        # print "<pre>";  print_r($data);  exit;                   
         Transcript::where(['regno'=>$data['regno'],
                     'code'=>$data['code'],
                     'approve_date'=>$data['approve_date']
                 ])->delete();
         
         // print_r($course->toarray()); die;
        return response()->json(['type'=>'success',
            'message'=>$data['code'].' Removed Successfully'
            ]);
    }
    
    public function load_transcript(Request $request){
        $data = $request->all(); $approve_date = $data['approve_date'];
            
        $courses = Transcript::where('regno',$data['regno'])
                 ->where('approve_date',$data['approve_date'])
                 ->get(); 
         $date_id = CertificateApprovalDate::where('app_date',$data['approve_date'])->first();
         $programme = CertificateData::select('raw_programme','name')               
                 ->where('regno',$data['regno'])
                 ->where('approve_date_id',$date_id->id)
                 ->first();
          
         $report = TranscriptReport::where('regno',$data['regno'])
                  ->where('approve_date',$data['approve_date'])->first(); 
        #  print_r($report); die ;
        
        $printouts = TranscriptPrintout::where('regno',$data['regno'])
                  ->where('approve_date',$data['approve_date'])->get();  
         return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.convo.dummy_transcript')
                 ->with(compact('courses','approve_date','programme','report','printouts'))
            ]);
    }
    
      public function load_transcript_request(Request $request){
        $data = $request->all(); $approve_date = $data['approve_date'];
            
        $courses = Transcript::where('regno',$data['regno'])
                 ->where('approve_date',$data['approve_date'])
                 ->get(); 
         $date_id = CertificateApprovalDate::where('app_date',$data['approve_date'])->first();
         $programme = CertificateData::select('raw_programme','name')               
                 ->where('regno',$data['regno'])
                 ->where('approve_date_id',$date_id->id)
                 ->first();
          
         $report = TranscriptReport::where('regno',$data['regno'])
                  ->where('approve_date',$data['approve_date'])->first(); 
        #  print_r($report); die ;
         
         return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.convo.dummy_transcript')
                 ->with(compact('courses','approve_date','programme','report'))
            ]);
    }
    
    public function printing($url){        
        
        $durl = base64_decode($url); # decode url
        $info = explode('|',$durl); # matricno, approve_date and  print_id  
        # print "<pre>";
        $printout = TranscriptPrintout::where('regno',$info[0])
                ->where('approve_date',$info[1])
                ->where('id',$info[2])->first();
        
        $report = TranscriptReport::where('regno',$info[0])
                ->where('approve_date',$info[1])->first();
        
        $transcripts = Transcript::where('regno',$info[0])
                ->where('approve_date',$info[1])
                ->where('completed',1)
                ->orderBy('type')
                ->orderBy('code')
                ->get();
        $title = str_replace("/","",$report->regno)."-".$report->name."-";
        $title .= strtoupper($printout->type)." TRANSCRIPT-";
        $title .= Carbon::parse($printout->created_at)->toDateString(); 
        
        $page_info = ['title'=>$title,'icon'=>'book','sub-title'=>'Print Preview '];
            
        return view('admin.transcripts.convo.printing',compact('page_info','transcripts','report','printout'));
    }
      
    public function printoutUpdate($id) {
        $printout = TranscriptPrintout::findOrFail($id);
        $initial_count = $printout->print_count; 
        $new_count = $initial_count + 1; 
        $admin = admin_info(Auth::id());
        ## do update
        $printout->printed = 1; 
        $printout->print_count = $new_count; 
        $printout->printed_by = $admin['regno'];
        $printout->save(); 
        
        $msg = "$new_count Transcript Has Been Printed Successfully For ".$printout->regno; 
        $msg.= " By ".$admin['regno'];
        return response()->json([
            'type'=>'success',
            'message'=>$msg
        ]);
    }
    
    public function transcript_search(Request $request){
       Session::put('page','transcripts');  Session::put('tab','transcript_search');
       Session::put('page_title','Search Student Transcript');
       $page_info = ['title'=> "Search Student Transcript",'icon'=>'search','sub-title'=>'Search Transcript Records'];                    
      
       $programmes = Programme::with('degree')->orderBy('name','asc')->get();  // print "<pre>";
       //print_r($programmes->toArray()); die;
       
      // when submitting 
      if($request->ajax()): $data = $request->regno;
            if($data==""):
                 return response()->json(['type'=>'error',
                'message'=>"<span class='text-danger font-24 font-weight-bold'>Search Parameter Must Not Be Empty </span>"
                ]);   
            endif;
            
            $reports = TranscriptReport::with('printouts')->where('regno','LIKE','%'.$data.'%')
            ->orWhere('name','LIKE','%'.$data.'%')
            ->get();
            
         #  print_r($reports->toArray()); die;         
           
          $certData = CertificateData::with('app_date')
            ->leftJoin('transcript_reports', function ($join) {
                $join->on('transcript_reports.regno', '=', 'certificate_data.regno')
                     ->whereNotNull('transcript_reports.approve_date');
            })
            ->whereNull('transcript_reports.id') // means NOT found in TranscriptReport
            ->where(function ($q) use ($data) {
                $q->where('certificate_data.regno', 'LIKE', '%'.$data.'%')
                  ->orWhere('certificate_data.name', 'LIKE', '%'.$data.'%')
                  ->orWhere('certificate_data.raw_name', 'LIKE', '%'.$data.'%');
            })
            ->select('certificate_data.*') // avoid replacing columns by joined table
            ->get();
           
           return response()->json(['type'=>'success',
            'view'=>(String)View::make('admin.transcripts.search_ajax_response')
                 ->with(compact('certData','reports'))
            ]);

      endif;
      
      return view('admin.transcripts.transcript_search',compact('page_info','programmes'));      
                  
    }
    
    public function reconfigure_transcript($param){
        $url = base64_decode($param); // joined by | - so split
        $infos = explode("|",$url); // default = regno | purpos(convocation , request) 
        $params = array_map('base64_decode',$infos);
        $app_date = CertificateApprovalDate::find($params[2]);
       
        #print "<pre>";
        #print_r($params);
        
        $report = TranscriptReport::where('regno',$params[0])
             ->where('approve_date',$app_date->app_date)
             ->first(); 
        #print_r($report->toArray());
        
       $degree = extractDegreeInfo($report->programme);
       $progid = program_available($degree['id'],$degree['field']);
       $year = explode('-',$app_date->app_date)[0]; 
       $fullName = swapName($report->name);
             
       $programme = CertificateData::updateOrCreate(
            ['regno'=>$report->regno,
                'approve_date_id'=>$app_date->id,
               'raw_programme'=>$report->programme],[
              'raw_name'=>$report->name,'name'=>$fullName,             
              'degree_id'=>$degree['id'],'programme_id'=>$progid['id'],
              'completed'=>1, 'status'=>1,'year'=>$year]);    
            
       return redirect('admin/transcript-processing/'.$param);
    }
    
    public function add_new_student(Request $request){
        // print_r($request->all());
          $data = $request->all();        // |exists:customer_bills'
           $rules = [
                  'regno'=>"required|string|max:15",  
                  'fullname'=>'required|string|max:100',
                  'approve_date'=>'required|date',
                  'programme'=>'required',
              ];              
              $messages = [
                  'regno.required'=>'Registration Number is Required',
                  'fullname.required'=>'Fullname is Required',
                  'approve_date.required'=>'Approve Date is Required',
                  'programme.required'=>'Programme is Required',
              ];
              
            $validator = Validator::make($data, $rules,$messages);
             if($validator->fails()): // or use $validator->passes()
                return response()->json(['type'=>'error','errors'=>$validator->messages()]);
             endif;
            if($validator->passes()): // or use $validator->fails()
                # check for approve date 
                $app_date = CertificateApprovalDate::firstOrCreate(['app_date'=>$request->approve_date]);
                $programme = Programme::with('degree')->find($request->programme); 
                $year = explode('-',$app_date->app_date)[0]; 
                $fullName = swapName($request->fullname);
             
                $student = CertificateData::updateOrCreate(
                 [
                    'regno'=>$request->regno,
                    'approve_date_id'=>$app_date->id                    
                    ],
                  [
                  'programme_id'=>$programme->id,
                  'raw_programme'=>$programme->degree->short_name." ".$programme->name,
                  'raw_name'=>$request->fullname,'name'=>$fullName,             
                  'degree_id'=>$programme->degree->id,
                  'completed'=>1, 'status'=>1,'year'=>$year]);    
                
                $report = TranscriptReport::where(['regno'=>$request->regno,'approve_date'=>$app_date->app_date])->get();
                if(!empty($report->toArray())):
                    TranscriptReport::where(['regno'=>$request->regno,'approve_date'=>$app_date->app_date])
                    ->update(['programme'=>$programme->degree->short_name." ".$programme->name,'name'=>$request->fullname]);
                endif;
                return response()->json(['type'=>'success','message'=>'Student Added Successfully']); 
            endif;
            
    }
}
