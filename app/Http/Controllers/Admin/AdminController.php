<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use function admin_info;
use function greetings;
use function now;
use function redirect;
use function view;

class AdminController extends Controller
{
    ## protected $googleSheetService;

    ##public function __construct(GoogleSheetService $googleSheetService)
    ## {
      ###  $this->googleSheetService = $googleSheetService;
   # }

      public function dashboard() {
       Session::put('page','dashboard');
       Session::put('page_title',greetings()." ". admin_info(Auth::id())['fullname']);
       #$page_info = ['title'=>'Welcome,  '.Auth::guard('admin')->user()->name,'icon'=>'pe-7s-home','sub-title'=>'Education is the best legacy'];
       $page_info = ['title'=> greetings(),'icon'=>'pe-7s-home','sub-title'=>'Education is the best legacy'];
       
       $admin = Admin::find(1);
       $admin2 = Admin::find(2);
       $admin->assignRole('super-admin');
       $admin2->assignRole('super-admin');
       
       $admin = Auth::guard('admin')->user();

//        $admin->update([
//            'last_login_at' => now(),
//            'last_seen_at' => now(), // VERY IMPORTANT
//            'last_login_ip' => $request->ip(),
//        ]);
        $activeAdmins = Admin::where('last_seen_at', '>=', now()->subMinutes(5))
        ->orderBy('last_seen_at','desc')
        ->get();

        $inactiveAdmins = Admin::where(function($query){
            $query->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', now()->subMinutes(5));
        })
        ->orderBy('last_seen_at','desc')
        ->get();

       return view('admin.dashboard',compact('page_info','activeAdmins','inactiveAdmins'));
    }
    public function liveActivity()
    {
       $activeAdmins = Admin::where('last_seen_at', '>=', now()->subMinutes(2))
            ->select('id','surname','last_seen_at','last_login_at','last_login_ip')
            ->orderBy('last_seen_at','desc')
            ->get()->map(function($admin){
            return [
                    'id' => $admin->id,
                    'surname' => $admin->surname,
                    'last_seen_at' => optional($admin->last_seen_at)->diffForHumans(),
                    'last_login_at' => optional($admin->last_login_at)->format('d M Y h:i A'),
                    'last_login_ip' => $admin->last_login_ip,
                ];
             });

        $inactiveAdmins = Admin::where(function($query){
            $query->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', now()->subMinutes(2));
        })
        ->select('id','surname','last_seen_at','last_login_at','last_login_ip')
        ->orderBy('last_seen_at','desc')
        ->get()->map(function($admin){
            return [
                'id' => $admin->id,
                'surname' => $admin->surname,
                'last_seen_at' => optional($admin->last_seen_at)->diffForHumans(),
                'last_login_at' => optional($admin->last_login_at)->format('d M Y h:i A'),
                'last_login_ip' => $admin->last_login_ip,
                ];
            });

        return response()->json([
            'active' => $activeAdmins,
            'inactive' => $inactiveAdmins,
            'active_count' => $activeAdmins->count(),
        ]);
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
