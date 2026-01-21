<?php

namespace App\Http\Controllers\Admin;
#use App\Imports\UsersImport;

# use App\Imports\UsersImport;

# use App\Imports\TranscriptReportImport;


use App\Http\Controllers\Controller;
use App\Imports\TranscriptImport;
use App\Models\GoogleIdCardForm;
use App\Models\TranscriptsImport;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use ZipStream\Exception;
use function response;
use function view;

class UsersController extends Controller
{
     public function import(Request $request)
        {       
         /* for user */
            /**$request->validate([
             'file' => 'required|mimes:xlsx,xls,csv|max:5120',
             ]);            
            $import = new UsersImport;
            $import = new TranscriptImport;
            Excel::import($import, $request->file('file')); **/ 

          /* use it for transcript import */
            $request->validate([
             'file' => 'required|mimes:xlsx,xls,csv|max:105120',
             ]);    
            
            $import = new TranscriptImport;
            Excel::import($import, $request->file('file'));
//            
//            $import = new TranscriptReportImport;
//            Excel::import($import, $request->file('file'));
//            
            return response()->json([
                'message' => 'Import completed!'
                //'inserted' => $import->inserted,
                //'updated' => $import->updated,
            ]);
        ####################
        }
        
        public function import_users(){
            Session::put('page','users');  Session::put('tab','import_users');
            Session::put('page_title','Import Students');
            $page_info = ['title'=> "Import Students",'icon'=>'pe-7s-person_add','sub-title'=>'Import All Available Students'];            
            return view('admin.students.import_students',compact('page_info'));
        }
        
        public function id_card_requests(){
            Session::put('page','users');  Session::put('tab','id_card_requests');
            Session::put('page_title','Students ID Card Request');
            $page_info = ['title'=> "Students ID Card Request",'icon'=>'pe-7s-card','sub-title'=>'Downloading of Passports and Signature'];            

            ## return view('drive.form');
            $records = GoogleIdCardForm::orderBy('id','desc')->orderBy('request_status','asc')->paginate(100);
            
            return view('admin.general.id_card_requests',compact('page_info','records'));
        }
        
    public function sync_card_requests(Request $request){
        if ($request->ajax()) {
        $history = TranscriptsImport::where('form_key','id_card')
                ->latest()->first();
        $newRow = empty($history) ? 2 : 2 + $history->cum_total;
        # connect to google sheet and get new records           
        // $range = "Sheet1!A{$newRow}:A";
         $range = "A{$newRow}:A";   die; 
        $service = new GoogleSheetService('id_card');           
        $counts = $service->countRows($range); 
        return $counts;         
        }  
    }
    
   public function import_latest_card_requests(Request $request){
         if($request->ajax()){  $data = $request->all();
           $history = TranscriptsImport::where('form_key','id_card')
                ->latest()->first();
           $newRow = (empty($history)) ? 2 : 2 + $history['cum_total'];

           $tofetch = ($data['maxno'] <= 50) ? $data['maxno'] : 50;
           $lastRow = $tofetch + $newRow - 1;
          $range = "A{$newRow}:M";  
           # connect to google sheet and get new records
           $service = new GoogleSheetService('id_card');           
           #$counts = $service->countRows($range); 
           $values = $service->read($range);
           print "<pre>"; print_r($values); exit;
           ## calculate initial sum of records in history
           $sum = TranscriptsImport::where('form_key','id_card')
                   ->sum('rows');
           $newsum = $sum + $tofetch;

          
          DB::beginTransaction();
           try{
           # print "<pre>";
           foreach($values as $row):
            # print_r($row); 
              
               GoogleIdCardForm::create([
                'request_time' => $row[0],
                'request_email' => $row[1],
                'regno' => $row[3] ?? "",
                'fullname' => $row[4]??"",
                'phone' => $row[5]??"",
                'entry_session' => $row[6]??"",
                'degree' => $row[7]??"",
                'programme' => $row[8]??"",
                'passport' =>  $this->extractFirstDriveLink($row[9]??""),
                'signature' =>  $this->extractFirstDriveLink($row[10]??""),
                'faculty' => $row[11]??"",
                'department' => $row[12]??"",                         
                ]);               
            endforeach;
           ## now update the import history
             
            TranscriptsImport::create([
               'form_key'  => 'id_card',
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
    }
    
    private function extractFirstDriveLink($raw)
    {
        if (empty($raw)) return null;

        // Split by comma or newline
        $links = array_filter(array_map('trim', preg_split("/[\n,]+/", $raw)));

        if (empty($links)) return null;

        // Take first link only
        $first = $links[0];

        // Convert to direct download URL
        return $this->driveDownloadLink($first);
    }

    /**
     * Convert any Google Drive URL to direct download link
     */
    private function driveDownloadLink($url)
    {
        if (!$url) return null;

        // Match /d/FILE_ID pattern
        if (preg_match('/\/d\/([^\/]+)/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        // Match ?id=FILE_ID pattern (like open?id=)
        if (preg_match('/[?&]id=([^&]+)/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }

        // Otherwise, return original URL
        return $url;
    }
  

    
}
