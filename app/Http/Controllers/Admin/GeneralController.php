<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CleanDateExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use function view;


class GeneralController extends Controller
{
   public function downloadCleanDates(Request $request){
      $file = $request->file('file'); // from Dropzone
        ob_end_clean(); // clear any stray output buffer
        ob_start();

        return Excel::download(new CleanDateExport($request->file('file')), 'converted_dates.xlsx');     
        
    }

    public function uploader(Request $request){
        Session::put('page','general_page');  Session::put('tab','general_page');
        Session::put('page_title','Date Conversion');
        $page_info = ['title'=> "Date Conversion",'icon'=>'pe-7s-person_add','sub-title'=>'Convert Date From Words To Normal Date Format'];            
        return view('admin.general.date_conversion',compact('page_info'));
    }
    
    private function cleanAndParseDate($value)
        {
            try {
                if (!$value) return null;
                return Carbon::parse($value)->format('Y-m-d');
            } catch (Exception $e) {
                return $value; // fallback if not parsable
            }
        }
}
