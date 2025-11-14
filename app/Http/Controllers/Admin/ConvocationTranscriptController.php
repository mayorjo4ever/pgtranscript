<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateApprovalDate;
use App\Models\CertificateData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use function get_current_approve_date;
use Illuminate\Support\Facades\DB;
use function view;
use Carbon\Carbon; 
class ConvocationTranscriptController extends Controller
{
    protected $approve_date; 
    protected $approve_date_id;  
    
    public function __construct() {
        $approve_date = get_current_approve_date(); // get the approved date id
        $this->approve_date = $approve_date->app_date; // 
        $this->approve_date_id = $approve_date->id; //        
    }

    public function pending_transcripts(Request $request){
        Session::put('page','transcripts');  Session::put('tab','pending-grads');
        Session::put('page_title','All Graduating Students Pending Transcripts');
        $page_info = ['title'=> "All Graduating Students Pending Transcripts",'icon'=>'book','sub-title'=>'View All '];                
        
        $approve_dates = CertificateApprovalDate::all(); 
        
        if (!session()->exists('approveDateId')) {
            Session::put('approveDateId', $this->approve_date_id); // set default once
        }
        if ($request->filled('approve_date_id')) {
            Session::put('approveDateId', $request->input('approve_date_id')); // only update if user chooses
        }
        $approveDateId = session('approveDateId');      
        $search = $request->input('search'); // get search from request
        Session::put('transcript_search',$search);
        $students = CertificateData::select(
        'regno',
        'raw_name',
        'programme_id',
        'approve_date_id',
        'raw_programme'
            )
            ->where('degree_id', '!=', 2)
            ->when($approveDateId, function ($query, $approveDateId) {
                $query->where('approve_date_id', $approveDateId); // ✅ limit dataset
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('regno', 'like', "%{$search}%")
                      ->orWhere('raw_name', 'like', "%{$search}%");
                });
            })
            ->whereNotIn('regno', function ($query) {
                $query->select('regno')
                      ->from('transcript_reports'); // exclude already done transcripts
            })
            ->distinct() // ✅ ensure uniqueness instead of groupBy
            ->orderBy('approve_date_id', 'desc')
            ->orderBy('raw_programme')
            ->paginate(100);
              
        /**$students = CertificateData::select(
                'regno',
                'raw_name',
                'programme_id',
                'approve_date_id',
                'raw_programme'
            )
            ->where('degree_id', '!=', 2)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('regno', 'like', "%{$search}%")
                      ->orWhere('raw_name', 'like', "%{$search}%");
                });
            })
            ->whereNotIn('regno', function ($query) {
                $query->select('regno')
                      ->from('transcript_reports'); // ✅ exclude students already in transcript_reports
            })
            ->groupBy('regno', 'raw_name', 'programme_id', 'approve_date_id', 'raw_programme')
            ->orderBy('approve_date_id', 'desc')
            ->orderBy('raw_programme')
            ->paginate(100);
            # print "<pre>"; print_r($students);  die; 
        */
        return view('admin.transcripts.convo.pending_transcripts',compact('page_info','students','approve_dates'));  
   
    }
    
     public function completed_transcripts(Request $request){
        Session::put('page','transcripts');  Session::put('tab','completed-grads');
        Session::put('page_title','All Graduating Students Completed Transcripts');
        $page_info = ['title'=> "All Graduating Students completed Transcripts",'icon'=>'book','sub-title'=>'View All '];                
        
        $approve_dates = CertificateApprovalDate::all();         
        if (!session()->exists('approveDateId')) {
            Session::put('approveDateId', $this->approve_date_id); // set default once
        }
        if ($request->filled('approve_date_id')) {
            Session::put('approveDateId', $request->input('approve_date_id')); // only update if user chooses
        }
        $approveDateId = session('approveDateId');      
        $search = $request->input('search'); // get search from request
        
        $students = CertificateData::with('printouts')
            ->select('regno', 'raw_name', 'programme_id', 'approve_date_id', 'raw_programme')
            ->where('degree_id', '!=', 2)
            ->when($approveDateId, function ($query, $approveDateId) {
                $query->where('approve_date_id', $approveDateId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('regno', 'like', "%{$search}%")
                      ->orWhere('raw_name', 'like', "%{$search}%");
                });
            })
            ->whereIn('regno', function ($sub) {
                $sub->select('cd.regno')
                    ->from('certificate_data as cd')
                    ->join('certificate_approval_dates as ad', 'ad.id', '=', 'cd.approve_date_id')
                    ->join('transcript_reports as tr', function ($join) {
                        $join->on('tr.regno', '=', 'cd.regno')
                             ->whereColumn('tr.approve_date', '=', 'ad.app_date'); // ✅ match date
                    })
                    ->select('cd.regno');
            })
            ->distinct()
            ->orderBy('approve_date_id', 'desc')
            ->orderBy('raw_programme')
            ->paginate(100);

        /**
        $students = CertificateData::with('printouts')->select(
        'regno',
        'raw_name',
        'programme_id',
        'approve_date_id',
        'raw_programme'
            )
            ->where('degree_id', '!=', 2)
            ->when($approveDateId, function ($query, $approveDateId) {
                $query->where('approve_date_id', $approveDateId); // ✅ limit dataset
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('regno', 'like', "%{$search}%")
                      ->orWhere('raw_name', 'like', "%{$search}%");
                });
            })
            ->whereIn('regno', function ($query) {
                $query->select('regno')
                      ->from('transcript_reports'); // load already done transcripts
            })
            ->distinct() // ✅ ensure uniqueness instead of groupBy
            ->orderBy('approve_date_id', 'desc')
            ->orderBy('raw_programme')
            ->paginate(100);
            */
        return view('admin.transcripts.convo.completed_transcripts',compact('page_info','students','approve_dates'));  
   
    }
}
