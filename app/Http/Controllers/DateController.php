<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DateController extends Controller
{
    public function index()
    {
        $page_info = ['title'=> "Date Conversion Made Easy",'icon'=>'pe-7s-person_add','sub-title'=>'We Love Simplicity'];
        return view('front.date.index',compact('page_info'));
    }
}
