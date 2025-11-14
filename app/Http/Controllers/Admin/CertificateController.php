<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CertificateDataExport;
use App\Exports\ConvocationDataExport;
use App\Exports\UncompletedCertificateExport;
use App\Http\Controllers\Controller;
use App\Imports\CertificateDataImport;
use App\Models\CertificateApprovalDate;
use App\Models\CertificateData;
use App\Models\Degree;
use App\Models\Programme;
use App\Models\TranscriptPrintout;
use App\Models\TranscriptReport;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use function asset;
use function collect;
use function extractDegreeInfo;
use function get_current_approve_date;
use function optional;
use function public_path;
use function response;
use function smartSwapName;
use function swapName;
use function view;


class CertificateController extends Controller
{
    protected $approve_date; 
    protected $approve_date_id; 
    protected $sourceFolder;
    
    public function __construct() {
        $approve_date = get_current_approve_date(); // get the approved date id
        $this->approve_date = $approve_date->app_date; // 
        $this->approve_date_id = $approve_date->id; // 
        $this->sourceFolder = $this->setSourceFolder($this->approve_date);
    }


    public function cert_setting_index(){
        Session::put('page','certificates');  Session::put('tab','cert_setup');
        Session::put('page_title','Certificate Setups');
        $page_info = ['title'=> "Certificate Setups",'icon'=>'pe-7s-person_add','sub-title'=>'View, and Setup All Certificate'];
        $approval_dates = CertificateApprovalDate::all()->map(function($row) {
            return [
                'id' => $row->id,  'app_date' => $row->app_date,
                'description' => $row->description,
                'cur_date'=>$row->is_current];
                });
        $degrees = Degree::all()->map(function($row) {
            return [
                'id' => $row->id, 'abbrev' => $row->short_name,
                'name' => $row->full_name];
                });
        $programmes = Programme::all()->map(function($row) {
            return [
                'id' => $row->id, 'prog_name' => $row->name,
                'deg_id' => $row->degree_id];
                }); 

       return view('admin.certificate.settings',compact('page_info','programmes','approval_dates','degrees'));

    }
    
    public function cert_data_upload_view(){
        Session::put('page','certificates');  Session::put('tab','upload_certs_data');
        Session::put('page_title','Certificate Data Uploading');
        $page_info = ['title'=> "Certificate Data UploadsData Uploading",'icon'=>'database_upload','sub-title'=>'Upload Certificate Images and Names'];         
       return view('admin.certificate.data_uploads',compact('page_info'));  
    }            
   
    // normal library  
    
    public function uploadMultipleImages(Request $request) {
    
     $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
    ]);

    $image = $request->file('file');
    $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    # $extension = strtolower($image->getClientOriginalExtension());

    $destinationPath = $this->setSourceFolder($this->approve_date);

    if (!File::exists($destinationPath)) {
        File::makeDirectory($destinationPath, 0755, true);
    }

    // Generate new filename with .jpg extension
    $newName = $this->autoSplitCamelCase($originalName);
    $saveName = $newName . '.jpg';
    $savePath = $destinationPath . '/' . $saveName;

    // Use the ImageManager via service container
    // $manager = app(ImageManager::class);
     $manager = new ImageManager(new Driver()); 
    // Read and process the image    
    $imageInstance = $manager->read($image->getPathname());
    $imageInstance
        ->resize(360, 400)
            // Sharpen (0–100, higher = stronger effect)
        ->toJpeg(100)
        ->save($savePath);

    return response()->json([
        'message' => 'Image resized and saved as JPG!',
        'path' => $savePath,
    ]);
}

    
    public function renamePassport(Request $request)  {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string',
        ]);
        $dateFolder = $this->setSourceFolder($this->approve_date);
        $basePath = public_path($dateFolder);

        $oldPath = $basePath . '/' . $request->old_name;
        $newPath = $basePath . '/' . $request->new_name; 

        if (!File::exists($oldPath)) {
            return response()->json(['type' => 'error', 'message' => 'Old file not found']);
        }

        if (File::exists($newPath)) {
            return response()->json(['type' => 'error', 'message' => 'Target filename already exists']);
        }

        try {
            File::move($oldPath, $newPath); $src = $dateFolder."/".$request->new_name;
            return response()->json(['type' => 'success','message' => 'Passport Renamed Successfully',
                 'view'=>(String)View::make('admin.certificate.ajax_new_passport')->with(compact('src'))]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => 'Rename failed: ' . $e->getMessage()]);
        }
    }


    protected function setSourceFolder($date) {
        $parentFolder = "certificates/";
        $children = explode("-",$date);
        $senior = $children[0]."/"; 
        $directory = $parentFolder.$senior.$date;
        return $directory;  # e.g = certificates/2024 
    }
    
    public function getSourceFolder() {            
        return $this->sourceFolder;
    }
    
    public function uploadExcelDocs(Request $request){
         $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            ]);
        
        $import = new CertificateDataImport;
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => 'Import completed!',
            'inserted' => $import->inserted,
            'updated' => $import->updated,
        ]);
        
      #  Excel::import(new CertificateDataImport, $request->file('file'));
      #  return response()->json([
      #     'message' => 'Certificate data imported successfully!',
      # ]);
         
    }
    
    public function cert_data_process_view(){
      Session::put('page','certificates');  Session::put('tab','process_certs');
      Session::put('page_title','Certificate Data Processing');
      $page_info = ['title'=> "Certificate Data Processing",'icon'=>'database_upload','sub-title'=>'Process Certificate Data, Images and Programmes'];                    
      return view('admin.certificate.data_processor',compact('page_info'));      
    }            
    
    public function cert_data_search_view(){
      Session::put('page','certificates');  Session::put('tab','cert_search');
      Session::put('page_title','Certificate Record Searching');
      $page_info = ['title'=> "Certificate Record Searching",'icon'=>'database_upload','sub-title'=>'Search Certificate Records, Images and Programmes'];                    
      return view('admin.certificate.data_search',compact('page_info'));      
    }            
            
    public function load_uploaded_cert_programmes(Request $request){
        if($request->ajax()):
           $data = $request->all(); 
          # $uploaded_programmes = CertificateData::select('raw_programme')->distinct()->where('approve_date_id',$data['approval_date_id'])->get(); 
           
          $uploaded_programmes = CertificateData::select('raw_programme', DB::raw('COUNT(*) as total'),
                  DB::raw('COUNT(CASE WHEN completed = 1 THEN 1 END) as total_completed'),
                  DB::raw('ROUND(100 * COUNT(CASE WHEN completed = 1 THEN 1 END) / COUNT(*), 2) as percentage_completed'))
                    ->groupBy('raw_programme')
                    ->where('approve_date_id',$data['approval_date_id'])
                    ->get();
            ##  print "<pre>"; print_r($uploaded_programmes);
           return response()->json([
                'view'=>(String)View::make('admin.certificate.ajax_load_uploaded_programmes')->with(compact('uploaded_programmes'))
            ]);
        endif;
    }
    
    public function load_completed_cert_programmes(Request $request){
        if($request->ajax()):
           $data = $request->all(); 
          # $uploaded_programmes = CertificateData::select('raw_programme')->distinct()->where('approve_date_id',$data['approval_date_id'])->get(); 
           
          $uploaded_programmes = CertificateData::select('raw_programme', DB::raw('COUNT(*) as total'),
                  DB::raw('COUNT(CASE WHEN completed = 1 THEN 1 END) as total_completed'),
                  DB::raw('ROUND(100 * COUNT(CASE WHEN status_uploaded = 1 THEN 1 END) / COUNT(*), 2) as percentage_uploaded'),
                  DB::raw('ROUND(100 * COUNT(CASE WHEN completed = 1 THEN 1 END) / COUNT(*), 2) as percentage_completed'))
                    ->groupBy('raw_programme')
                    ->where('approve_date_id',$data['approval_date_id'])
                    ->get();
            ##  print "<pre>"; print_r($uploaded_programmes);
           return response()->json([
                'view'=>(String)View::make('admin.certificate.ajax_load_completed_programmes')->with(compact('uploaded_programmes'))
            ]);
        endif;
    }
    
    public function load_uploaded_student_groups(Request $request){
        if($request->ajax()):
          $data = $request->all();             
          $uploaded_programmes = CertificateData::select('raw_programme', DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN completed = 1 THEN 1 END) as total_completed'))
                ->groupBy('raw_programme')
                ->where('approve_date_id',$data['approval_date_id'])
                ->get();

        ##  print "<pre>"; print_r($uploaded_programmes);
           return response()->json([
                'view'=>(String)View::make('admin.certificate.ajax_load_uploaded_student_groups')->with(compact('uploaded_programmes'))
            ]);
        endif;
    }
         # print_r($processed); die; 
            /*$imageFiles = collect(File::files($imagePath))
                ->filter(fn($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png']))
                ->map(fn($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME)); // No extension
              */
    
    public function load_uploaded_student_by_programme_original(Request $request){
        if($request->ajax()):
          $data = $request->all();             
          $img_dir = $this->sourceFolder."/";
          $records = CertificateData::where(['approve_date_id'=>$data['approval_date_id'],'raw_programme'=>$data['selected_programme']])
           ->orderBy('completed')
           ->paginate(100); # ->get();
          
          ## source for uploaded pictures 
          $imagePath = $this->sourceFolder;           
           $imageFiles = collect(File::files($imagePath))->mapWithKeys(function ($file) {
                $clean = strtolower(preg_replace('/[^a-z0-9]/i', '', pathinfo($file->getFilename(), PATHINFO_FILENAME)));
                return [$clean => $file->getFilename()];
            });          
             # print "<pre>"; print_r($imageFiles); die; 
             $processed = $records->map(function ($record) use ($imageFiles) {
             $expected = $this->normalizeName($record->pix_name);

             $matchedFile = $imageFiles[$expected] ?? null;

                // Find possible similar matches (containing all parts of the name)
                $suggested = [];
                if (!$matchedFile) {
                    foreach ($imageFiles as $key => $file) {
                        similar_text($expected, $key, $percent);
                        if ($percent >= 80) { // threshold
                            $suggested[] = $file;
                        }
                    }
                }

            return [
                'record' => $record,
                'expected' => $record->pix_name,
                'normalized' => $expected,
                'photo' => $matchedFile,
                'matched' => !is_null($matchedFile),
                'suggestions' => $suggested,
                ];
            }); 

           /**
            return response()->json([
                'view'=>(String)View::make('admin.certificate.ajax_load_uploaded_students_by_programme')->with(compact('processed','img_dir'))
            ]);**/
             return response()->json([
            'view' => (string)View::make('admin.certificate.ajax_load_uploaded_students_by_programme')
                ->with(compact('processed', 'img_dir')),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'next_page_url' => $records->nextPageUrl(),
                    'prev_page_url' => $records->previousPageUrl()
                ]
                ]);
            endif;
    }
    
    public function load_uploaded_student_by_programme(Request $request)
    {
    if ($request->ajax()):

        $data = $request->all();             
        $img_dir = $this->sourceFolder . "/";        
        $accuracy = $data['accuracy'] ?? 80; 
        # print "<pre>"; print_r($data); exit;
        $records = CertificateData::where([
        'approve_date_id' => $data['approval_date_id']])
        ->whereIn('raw_programme', $data['selected_programme'])
        ->leftJoin('users', 'certificate_data.regno', '=', 'users.regno') // adjust column name if needed
        ->select(
            'certificate_data.*',
            'users.first_name',
            'users.middle_name',
            'users.last_name',
            'users.phone',
            'users.programme',
        )
        ->orderBy('certificate_data.completed')
        ->paginate(200);
        #  print_r($records->toArray()); 
         # exit;
        // Image mapping – keep the actual filename (without extension)
        $imagePath = $this->sourceFolder;
        $imageFiles = collect(File::files($imagePath))->mapWithKeys(function ($file) {
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME); // no extension
            return [$filename => $file->getFilename()]; // keep original key, no normalization
        });

        // Map the paginated items and keep the paginator
        $processedItems = $records->getCollection()->map(function ($record) use ($imageFiles,$accuracy) {
            $expected = pathinfo($record->pix_name, PATHINFO_FILENAME); // get base name only, no normalize
            $matchedFile = $imageFiles[$expected] ?? null;

            $suggested = [];  

            if (!$matchedFile) {
                foreach ($imageFiles as $key => $file) {
                    similar_text($expected, $key, $percent);
                    if ($percent >= $accuracy ) {
                        $suggested[] = $file;
                    }
                }
            }

            return [
                'record' => $record,
                'expected' => $record->pix_name,
                'photo' => $matchedFile,
                'matched' => !is_null($matchedFile),
                'suggestions' => $suggested,
            ];
        });

        // Replace paginator's collection with the processed one
        $records->setCollection($processedItems);

        return response()->json([
            'view' => (string) View::make('admin.certificate.ajax_load_uploaded_students_by_programme')
                ->with([
                    'processed' => $records,
                    'img_dir' => $img_dir
                ]),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'next_page_url' => $records->nextPageUrl(),
                'prev_page_url' => $records->previousPageUrl(),
                'accuracy'=> $accuracy # e.g 80%
            ]
        ]);

    endif;
}

    public function normalize_cert_names(Request $request) {
        if($request->ajax()):
            $data = $request->all();
            #$swappedNames = [];
            foreach($data['students'] as $id):
                $stud_info = CertificateData::select('raw_name','regno')->where('id',$id)->first();
                if($request->toSwap=="no") : 
                    $new_name = $stud_info->raw_name;
                else :
                    $new_name = swapName($stud_info->raw_name);
                endif;
                
                $pix_name = $new_name."".str_replace("/","",$stud_info->regno);
                CertificateData::where('id',$id)->where('completed',0)->update(['name'=>$new_name,'name_swapped'=>1,'pix_name'=>$pix_name]); 
            endforeach;
            return response()->json([
                'type'=>'success','message'=>'Names normalized Successfully',              
            ]);
        endif; # ajax        
    }
    
    public function modify_uploaded_cert_data(Request $request) {
        if($request->ajax()):
            $data = $request->all();
             /*    print "<pre>"; print_r($data); die; 
              [value] => 02/55EA026, Folashade Omolola ADEWOYE
                [ref] => 38
                [param_type] => regno, name, programme
             */ 
             if($data['param_type'] === "programme") :
                 CertificateData::where([
                     'approve_date_id'=>$this->approve_date_id,
                     'raw_programme'=>$data['ref']]
                     )->update(['raw_programme'=>$data['value'],'degree_id'=>null,'programme_id'=>null]);
                ## updated - return msg
                $msg = "Programme Changed From ".$data['ref'] ." To ".$data['value'];
                $type = "success";
                 else : 
                     
             $init_record = CertificateData::where('id',$data['ref'])->first(); 
             ## check if already finalized before editing 
             if( $init_record->completed == 1):
                 $msg = "Data Can No More Be Modifed After Been Finalized"; 
                 $type = "error";
                else :
                 switch($data['param_type']):
                    case "regno" : 
                        $msg = "Matric No. Updated Successfully ";                     
                        $newPixName = $init_record->name ."".str_replace("/","",$data['value']);
                        CertificateData::where('id',$data['ref'])->update(['modified_regno'=>$init_record->regno,'regno'=>$data['value'],'pix_name'=>$newPixName,'completed'=>0]); 
                        $msg .= " From ".$init_record->regno." To ".$data['value'];
                        $type = "success";  
                        break; 
                    case  "name" : 
                        $msg = "Name Updated Successfully";
                        $newPixName = $data['value'].str_replace("/","",$init_record->regno);
                        CertificateData::where('id',$data['ref'])->update(['name'=>$data['value'],'pix_name'=>$newPixName,'completed'=>0]); 
                        $type = "success";  
                        break;                     
                endswitch; 
               endif; ## end not completed
              
               endif;
               
                return response()->json([
                   'type'=>$type,'message'=>$msg,              
               ]);
        endif; # ajax        
    }
    
    function normalizeName($name) {
            return strtolower(preg_replace('/[^a-z0-9]/i', '', pathinfo($name, PATHINFO_FILENAME)));
        }
        
     private function autoSplitCamelCase($filename) {
            // Attempt to split by space first
            $parts = explode(' ', $filename);

            // If no space, it means it's not split yet
            if (count($parts) <= 2 ) {
                // Try to split camel case in first name part only
                if (preg_match('/^([A-Z][a-z]+)([A-Z][a-z]+)(.*)$/', $filename, $matches)) {
                    $first = $matches[1];
                    $middle = $matches[2];
                    $rest = trim($matches[3]);

                    // Merge back rest of string (likely last name or regno)
                    return trim("{$first} {$middle} {$rest}");
                }
            }
            // Return as-is if already spaced
            return $filename;
        }
     
    
    public function finalize_cert_names(Request $request) {
        if($request->ajax()):
            $data = $request->all();  # print ""; print_r($data); die;            
            CertificateData::whereIn('id',$data['students'])->update(['completed'=>1]);
            return response()->json([
                'type'=>'success','message'=>'Processing Finalized Successfully',              
            ]);
        endif; # ajax        
    }    

    public function set_default_cert_approval_date(Request $request) {
        if($request->ajax()):
            $data = $request->all();  # print ""; print_r($data); die;            
            CertificateApprovalDate::where('is_current',1)->update(['is_current'=>0]);
            CertificateApprovalDate::where('id',$data['current_date'])->update(['is_current'=>1]);
           return response()->json([
                'type'=>'success','message'=>'New Default Date Successfully Set',              
            ]);
        endif; # ajax        
    }    
   
    public function add_update_cert_approve_date(Request $request) {
        if($request->ajax()):
            $data = $request->all(); #  print ""; print_r($data); die;            
           
            if(empty($data['ref'])):
                $setdate = new CertificateApprovalDate;
                $msg = "New Date Successfully Saved";
                else:
                $setdate = CertificateApprovalDate::find($data['ref']);
                $oldDate = $setdate->app_date;
                $newDate = Carbon::parse($data['value'])->toDateString();
                 #$imagePath = $this->renamePassportFolder($oldDate, $newDate);
                /*if($imagePath['type']=="error"):
                    return response()->json([
                        'type'=>'error','message'=>$imagePath['message']
                    ]);
                endif;*/
                $msg = "Date Successfully Updated";
            endif;
            
            $setdate->app_date = Carbon::parse($data['value'])->toDateString();
            $setdate->save(); 
                       
           return response()->json([
                'type'=>'success','message'=>$msg
            ]);
        endif; # ajax        
    }    
    
    
    public function definalize_cert_names(Request $request) {
        if($request->ajax()):
            $data = $request->all();  # print ""; print_r($data); die;            
            CertificateData::whereIn('id',$data['students'])->update(['completed'=>0]);
            return response()->json([
                'type'=>'success','message'=>'Data Finalization Reversed Successfully'
            ]);
        endif; # ajax        
    }    
    
    protected function renamePassportFolder($oldDate,$newDate){
        $currentFolder =  public_path($this->setSourceFolder($oldDate)); 
        $newFolder = public_path($this->setSourceFolder($newDate));
        if(File::exists($currentFolder)):
            if(!File::exists($newFolder)):
                File::moveDirectory($currentFolder, $newFolder);
                return ['type'=>'success','message'=>'Folder Renamed Successfully'];
                else:
                  return ['type'=>'error','message'=>'Target Folder Already Exist'];  
            endif;
            else:
                 return ['type'=>'error','message'=>"$oldDate Source Folder Does Not Exist"];
        endif;
    }
    
    public function download_certificate_data(Request $request) {
         if($request->ajax()):
            $data = $request->all(); #  print "<pre>"; 
            # print_r($data); die;   
            // we have programmes [] and data_type : excel / passport / excel-convo           
            # now process the type of data needed
            #=====================================
            # $students = CertificateData::select('regno','name','programme_id','pix_name')
            if($data['data_type']==="passport"):
                $approve_date = get_current_approve_date(); // get the approved date id             
                $students = CertificateData::select('pix_name')
                    ->whereIn('raw_programme',$data['programmes'])
                    ->where('approve_date_id',$approve_date->id)
                    ->where('completed',1)->get()->toArray();
                foreach($students as $student):
                $filenames[] = $student['pix_name'].".jpg";
                endforeach;    
               #print_r($filenames);
                $zipFilePath = $this->downloadPassports($filenames, false); // false = don't return download    
                return response()->json(['type'=>'success',
                    'message'=>'Download Successful',
                   'file_url' => asset(basename($zipFilePath)) // return the URL for JS to handle
               ]);
                ## for excel
                
                elseif($data['data_type']==="excel"):
                    
                    $approve_date = get_current_approve_date();
                    $fn = Carbon::parse($approve_date->app_date)->format('M_j_Y');
                    $filename = 'CERTIFICATE_' . $fn. '.xlsx';
                    #$export = new CertificateDataExport($data['programmes'], $approve_date->id);
                    #Excel::store($export, $filename, 'excel_public'); 
                    Excel::store(new CertificateDataExport($data['programmes'], $approve_date->id), $filename, 'excel_public');
                    
                    return response()->json([
                        'type' => 'success','message'=>'Download Successful',
                        'file_url' => asset('exports/'.$filename)
                    ]);
                    
                    
                 elseif($data['data_type']==="excel-convo"):
                    $approve_date = get_current_approve_date();
                    $fn = Carbon::parse($approve_date->app_date)->format('M_j_Y');
                    $filename = 'CONVOCATION_' . $fn. '.xlsx';
                    #$export = new CertificateDataExport($data['programmes'], $approve_date->id);
                    #Excel::store($export, $filename, 'excel_public'); 
                    Excel::store(new ConvocationDataExport($data['programmes'], $approve_date->id), $filename, 'excel_public');
                    
                    return response()->json([
                        'type' => 'success','message'=>'Download Successful',
                        'file_url' => asset('exports/'.$filename)
                    ]);
                    
                    ## to update if convocation record has been uploaded on the portal                    
                    elseif($data['data_type']=="graduation-status-update"):
                     $approve_date = get_current_approve_date(); // get the approved date id             
                     $date_uploaded = Carbon::now(); 
                     CertificateData:: whereIn('raw_programme',$data['programmes'])
                        ->where('approve_date_id',$approve_date->id)
                        ->where('completed',1)
                        ->update(['status_uploaded'=>true,'date_status_uploaded'=>$date_uploaded]); 
                
                    return response()->json([
                        'type' => 'success','message'=>"Student's Graduation Status Has Been Updated Successfully"                        
                    ]);   
            endif;      
        endif; # ajax  
    }
  
    public function download_uncompleted_data(Request $request){
        if($request->ajax()):
            $data = $request->all();  //  print "<pre>"; 
             $approve_date = get_current_approve_date();                
                $filename = 'uncompleted_' . $approve_date->app_date . '.xlsx';
                #$export = new CertificateDataExport($data['programmes'], $approve_date->id);
                #Excel::store($export, $filename, 'excel_public'); 
                Excel::store(new UncompletedCertificateExport($approve_date->id), $filename, 'excel_public');
                              
                return response()->json([
                    'type' => 'success','message'=>'Download Successful',
                    'file_url' => asset('exports/'.$filename)
                ]);                   
        endif; 
    }
    
    public function downloadPassports(array $filenames, $returnDownload = true)
        {
            if (!is_array($filenames) || empty($filenames)) {
                return response()->json(['error' => 'No files selected.'], 422);
            }
            $folderPath = public_path($this->getSourceFolder()); 
            $missingPsp = [];
            $fn = Carbon::parse($this->approve_date ?? time())->format('M_j_Y');
            $zipFileName = 'PASSPORTS_' . $fn. '.zip';
            $zipFilePath = public_path($zipFileName);

            $zip = new ZipArchive;
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($filenames as $file) {
                    $filePath = $folderPath . '/' . $file;
                    if(File::exists($filePath)):
                        $zip->addFile($filePath, $file);
                    else:
                      $missingPsp[]=$file;
                    endif;
                }
                $zip->close();
                
                if(!empty($missingPsp)):
                    Session::put('missingPsp',$missingPsp);
                    else:
                    Session::put('missingPsp',null);
                endif;
                
            } else {
                return response()->json(['error' => 'Failed to create ZIP.'], 500);
            }

            if ($returnDownload) {
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            }

            return $zipFilePath; // Just return path for AJAX
        }

        public function check_programme_compatibility(Request $request){
            if($request->ajax()):
                $data = $request->all();         
                $info = extractDegreeInfo($data['programme']);                
                #print "<pre>"; print_r($info); die;
                    
                return response()->json([
                     'view'=>(String)View::make('admin.certificate.ajax_cert_prog_setup')->with(compact('info','data')),
                    'type'=>'success','message'=>'Data Finalization Reversed Successfully'
                ]);
            endif; # ajax 
        }
       
        public function create_programme_template(Request $request){
            if($request->ajax()): # to create programmes. eg. M.Sc Animal Production
                $data = $request->all();
                
               Programme::updateOrCreate(['degree_id' => $data['deg_id'],'name' => $data['name']]); 
                   
                return response()->json([
                     'view'=>(String)View::make('admin.certificate.ajax_cert_prog_setup')->with(compact('info','data')),
                    'type'=>'success','message'=>'Programme Successfully Saved'
                ]);
            endif; # ajax 
        }
                
        public function configure_programme_template(Request $request){
            if($request->ajax()): # to update the programme id in certificte data. eg. M.Sc Animal Production
                $data = $request->all();  //   print "<pre>"; print_r($data);
                $approve_date_id = get_current_approve_date(); // get the approved date id 
                    
               CertificateData::where(['raw_programme' => $data['prog_name'],'approve_date_id' => $approve_date_id->id])
                   ->update(['programme_id'=>$data['prog_id']]);
                 
                $this->set_degree_id($data['prog_id']); 
                
                return response()->json([
                     #'view'=>(String)View::make('admin.certificate.ajax_cert_prog_setup')->with(compact('info','data')),
                    'type'=>'success','message'=>'Programme Successfully Configured'
                ]);
            endif; # ajax 
        }
        
      protected function set_degree_id($programme_id){
          $deg = Programme::where('id',$programme_id)->get()->first(); 
          $approve_date = get_current_approve_date();
           // print_r( $deg ); exit; 
          CertificateData::where('programme_id',$programme_id)
           ->where('approve_date_id',$approve_date->id)
            ->update(['degree_id'=>$deg->degree_id]);
      }  
    
   
    
    public function phd_graduands() {
        $approveDateIds = [ 9,10,11,12,13,14,15,17]; // example multiple approve_date_ids
        // ->where('completed',1)
        $students = CertificateData::with(['app_date','degree'])
            ->where('degree_id', 2)
            ->whereIn('approve_date_id', $approveDateIds)
            ->select('regno', 'raw_name','name', 'programme_id','degree_id', 'approve_date_id', 'pix_name')
            // ->orderBy('name', 'asc')
            ->get();
        // Then sort the collection using our smartSwapName logic
        $students = $students->sortBy(function ($student) {
            return smartSwapName($student->name ?? $student->raw_name);
        });
        
        # print "<pre>"; print_r($students->toArray()); die;
        Session::put('page','certificates');  Session::put('tab','graduands');
        Session::put('page_title','Ph.D. Graduands');
        $page_info = ['title'=> "Ph.D. Graduands",'icon'=>'users','sub-title'=>'All List of Ph.D. Graduands'];         
       return view('admin.certificate.graduands',compact('page_info','students'));  
        
    }
    
    public function master_graduands() {
        $approveDateIds = [ 9,10,11,12,13,14,15,17]; // example multiple approve_date_ids
        // 
        $students = CertificateData::with(['app_date','degree'])
            ->where('degree_id', '!=', 2)
            ->where('completed',1)
            ->whereIn('approve_date_id', $approveDateIds)
            ->select('regno', 'raw_name','name', 'programme_id','degree_id', 'approve_date_id', 'pix_name')
            // ->orderBy('name', 'asc')
            ->get();
        $students->each(function ($student) {
            $appDate = optional($student->app_date)->app_date; // from certificate_approval_dates
            $student->transcript_produced = TranscriptReport::where('regno', $student->regno)
                ->where('approve_date', $appDate)
                ->exists();
        $student->transcript_printed = TranscriptPrintout::where('regno', $student->regno)
            ->where('purpose', 'Convocation')
            ->where('printed', true)
            ->exists();
            
        });        
        // Then sort the collection using our smartSwapName logic
        $students = $students->sortBy(function ($student) {
            return smartSwapName($student->name ?? $student->raw_name);
        });
        
        # print "<pre>"; print_r($students->toArray()); die;
        Session::put('page','certificates');  Session::put('tab','graduands');
        Session::put('page_title','Ph.D. Graduands');
        $page_info = ['title'=> "Masters and PGD. Graduands",'icon'=>'users','sub-title'=>'All List of Masters and PGD. Graduands'];         
       return view('admin.certificate.master_graduands',compact('page_info','students'));  
        
    }
    public function uploadNyscImages(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Try to extract matric number from the filename
            if (preg_match('/([0-9]{4,}[A-Z]{1,3}[0-9]{2,})/i', $originalName, $matches)) {
                $matric = strtoupper($matches[1]);
                $newName = $matric . '.' . $extension;
            } else {
                // fallback if no match found
                $newName = time() . '_' . uniqid() . '.' . $extension;
            }

            // Save file into /storage/app/public/passports
            $path = $file->storeAs('public/passports', $newName);

            return response()->json([
                'success' => true,
                'new_name' => $newName,
                'path' => Storage::url($path)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }
    
} // end className
    