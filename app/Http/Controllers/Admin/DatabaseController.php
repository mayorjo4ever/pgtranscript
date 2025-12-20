<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use function abort;
use function config;
use function view;

class DatabaseController extends Controller
{
    //
    public function backup_restore(){
        Session::put('page','database');  Session::put('tab','database');
        Session::put('page_title','Databse Backup and Restore');
        $page_info = ['title'=> "Database Backup and Restore",'icon'=>'pe-7s-storage','sub-title'=>''];            
       
        ## return view('drive.form');
         return view('admin.general.database_backup',compact('page_info'));
    }
    
    public function backup_db(Request $request){
         // Run database-only backup
        Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        // Backup path (default Spatie path)
        $backupDisk = config('backup.backup.destination.disks')[0];
        $backupPath = config('backup.backup.name');

        $files = Storage::disk($backupDisk)->files($backupPath);

        if (empty($files)) {
            abort(404, 'Backup file not found');
        }

        // Get the latest backup file
        rsort($files);
        $latestBackup = $files[0];

        return Storage::disk($backupDisk)->download($latestBackup);
        
    }
}
