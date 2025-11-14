<?php

namespace App\Http\Controllers\Admin;
#use App\Imports\UsersImport;

# use App\Imports\UsersImport;
use App\Imports\TranscriptImport;


use App\Http\Controllers\Controller;
# use App\Imports\TranscriptReportImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
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
}
