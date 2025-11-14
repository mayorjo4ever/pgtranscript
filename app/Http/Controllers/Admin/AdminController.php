<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use function admin_info;
use function greetings;
use function redirect;
use function view;

class AdminController extends Controller
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

      public function dashboard() {
       Session::put('page','dashboard');
       Session::put('page_title',greetings()." ". admin_info(Auth::id())['fullname']);
       #$page_info = ['title'=>'Welcome,  '.Auth::guard('admin')->user()->name,'icon'=>'pe-7s-home','sub-title'=>'Education is the best legacy'];
       $page_info = ['title'=> greetings(),'icon'=>'pe-7s-home','sub-title'=>'Education is the best legacy'];

       return view('admin.dashboard',compact('page_info'));
    }

    public function managePassword(Request $request) {
          Session::put('page','manage_password');
          $page_info = ['title'=>'Manage Password','icon'=>'pe-7s-user','sub-title'=>'When you noticed vunerability, please always change your password, and subsequently every 3 months '];
           Session::put('page_title','Password Management');

        //    $request->validate([
        //     'current_password'=>'required',
        //     'new_password'=>'required|min:6'
        //    ]);


          if($request->isMethod('post')){
            $data = $request->all(); // print "<pre>";
            // var_dump($data); die;
             if(!Hash::check($data['current_password'],Auth::guard('admin')->user()->password))
             {
                 return redirect()->back()->with('error_message','Your current password is incorrect');
             }
             else {
                 if($data['confirm_password'] == $data['new_password']){
                     Admin::where('id',Auth::guard('admin')->user()->id)->update(['password'=>Hash::make($data['new_password']),'password_updated_at'=>now()]);

                      Auth::guard('admin')->logoutOtherDevices($data['current_password']);
                    return redirect()->back()->with('success_message','Your password has been updated');
                 }
                 else {
                     return redirect()->back()->with('error_message','New password and Confirm password does not match');
                 }
             }
        }
        $adminDetails = Admin::where('email', Auth::guard('admin')->user()->email)->first()->toArray();
        return view('admin.settings.manage_password')->with(compact('adminDetails','page_info'));
    }

}
