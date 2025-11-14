<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CourseImport;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use function response;
use function view;

class CourseController extends Controller
{
    public function courses() {
        Session::put('page','courses');  Session::put('tab','courses');
        Session::put('page_title','Import New Transcript Requests');

        $page_info = ['title'=> "All Courses",'icon'=>'pe-7s-book','sub-title'=>'All Available Courses'];
        
        $courses = Course::paginate(100); 
        
        return view('admin.courses.courses',compact('page_info','courses'));
    }
    
    public function course_upload_view(){
        Session::put('page','courses');  Session::put('tab','upload-courses');
        Session::put('page_title','Upload Courses');
        $page_info = ['title'=> "Upload Courses",'icon'=>'database_upload','sub-title'=>'Aimport all available courses'];         
       return view('admin.courses.course_uploads',compact('page_info'));  
    }  
    
    public function uploadExcelDocs(Request $request){
         $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            ]);
        
        $import = new CourseImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => 'Import completed!',
            'inserted' => $import->inserted,
            'updated' => $import->updated,
        ]);
    }
}
