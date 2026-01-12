<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadIdCardFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipStream\Exception;
use function back;


class DownloadController extends Controller
{
    // download id card signature and passports  
    public function download(Request $request)
    {
//        set_time_limit(0); // unlimited
//        ini_set('max_execution_time', 0);
          $ids = explode(',', $request->ids);
            
         DownloadIdCardFiles::dispatch($ids);
         
         return back()->with('success_message', 'Download started in background');
         
        // return back()->with('success', 'Download started in background');

//        $records = GoogleIdCardForm::whereIn('id', $ids)->get();
//
//         foreach ($records as $record) {
//
//            // Passport
//            if ($record->passport) {
//                $this->downloadToStorage(
//                    $record->passport,
//                    'passports',
//                    $record->regno
//                );
//            }
//
//            // Signature
//            if ($record->signature) {
//                $this->downloadToStorage(
//                    $record->signature,
//                    'signatures',
//                    $record->regno
//                );
//            }
//        }       
    }
    
     private function downloadToStorage($url, $folder, $regno)
    {
        try {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                \Log::warning('Invalid URL skipped', ['url' => $url]);
                return;
            }

            $response = Http::timeout(60)->get($url);

            if (!$response->successful()) {
                \Log::error('Download failed', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return;
            }

            // Unique filename
            $filename = strtoupper(str_replace("/","",$regno)). '.jpg';

            Storage::put("public/{$folder}/{$filename}", $response->body());

        } catch (Exception $e) {
            \Log::error('Download exception', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

}
