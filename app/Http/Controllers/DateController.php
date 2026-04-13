<?php

namespace App\Http\Controllers;

use App\Exports\CleanDateExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class DateController extends Controller
{
    public function index()
    {
        $page_info = ['title'=> "Date Conversion Made Easy",'icon'=>'pe-7s-person_add','sub-title'=>'We Love Simplicity'];
        return view('front.date.index',compact('page_info'));
    }

    public function downloadCleanDates(Request $request){
      $file = $request->file('file'); // from Dropzone
        ob_end_clean(); // clear any stray output buffer
        ob_start();

        return Excel::download(new CleanDateExport($request->file('file')), 'converted_dates.xlsx');     
        
    }
}
