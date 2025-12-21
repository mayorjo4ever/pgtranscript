<?php

namespace App\Http\Controllers\Admin;
// use ZipStream\File;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use function abort;
use function back;
use function base_path;
use function config;
use function env;
use function redirect;
use function session;
use function storage_path;
use function str_ends_with;
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
         $request->validate([
           'backup_key' => 'required|string',
        ]);
        
         $hash = Hash::make(env('DB_PASSKEY'));
        
        if(!password_verify($request->backup_key, $hash)):
            return back()->with('error_message', 'Invalid Backup Credentials');
        endif;
        
        // Run database-only backup
        set_time_limit(0);
        
        
        
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
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
    
    
    ## restore with sql file
    public function restoreSql(Request $request){
        
        set_time_limit(0);
          
          $request->validate([
            'sql_file' => 'required|file|mimes:zip',
            'restore_key'=>'required|string'
        ]);
          
        $hash = Hash::make(env('DB_PASSKEY'));
        
        if(!password_verify($request->restore_key, $hash)):
            return back()->with('error_message', 'Invalid Restore Credentials');
        endif;

        $file = $request->file('sql_file');
        $path = storage_path('app/restore');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $filePath = $file->move($path, $file->getClientOriginalName());

        // If ZIP → extract
        if ($file->getClientOriginalExtension() === 'zip') {
            $zip = new \ZipArchive;
            if ($zip->open($filePath) === true) {
                $zip->extractTo($path);
                $zip->close();
            } else {
                return back()->withErrors('Failed to extract zip file');
            }
            // Find SQL file inside ZIP
            $sqlFiles = File::glob($path . '/*.sql');

            if (empty($sqlFiles)) {
                return back()->withErrors('No SQL file found in zip');
            }

            $sqlFile = $sqlFiles[0];
        } else {
            $sqlFile = $filePath;
        }

        // Database credentials
        $dbHost = config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbName = config('database.connections.mysql.database');

        // Restore command
        $command = sprintf(
            'mysql -h%s -u%s %s %s < "%s"',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $dbPass ? '-p' . escapeshellarg($dbPass) : '',
            escapeshellarg($dbName),
            $sqlFile
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            return back()->withErrors('Database restore failed');
        }

        return back()->with('success_message', 'Database restored successfully');
    }
     
}
