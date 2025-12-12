<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function back;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function redirect;
use function route;
use function storage_path;
use function view;

class DriveDownloadController extends Controller
{
    private function getClient()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setRedirectUri(route('google.callback'));

        $tokenPath = storage_path('app/google/token.json');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($newToken));
            } else {
                return redirect($client->createAuthUrl());
            }
        }

        return $client;
    }

    private function getDriveService()
    {
        $client = $this->getClient();
        return new Drive($client);
    }

    private function extractFileId($url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        return $params['id'] ?? null;
    }

    private function downloadFile($service, $fileId, $savePath)
    {
        try {
            $file = $service->files->get($fileId, ['alt' => 'media']);
            $content = $file->getBody()->getContents();

            Storage::put($savePath, $content);

            return "Downloaded: " . $savePath;
        } catch (Exception $e) {
            return "Error downloading {$fileId}: " . $e->getMessage();
        }
    }

    public function index()
    {
        Session::put('page','id_card');  Session::put('tab','id_card');
        Session::put('page_title','Google ID Card Data Download');
        $page_info = ['title'=> "Google ID Card Data Download",'icon'=>'pe-7s-person_add','sub-title'=>'Downloading of Passports and Signature'];            
       
        ## return view('drive.form');
         return view('admin.general.id_card_form',compact('page_info'));
    }

    public function uploadExcel(Request $request)
    {
        ini_set('max_execution_time', 0);
    set_time_limit(0);

    $request->validate([
        'excel' => 'required|file|mimes:xlsx,xls',
        'folder' => 'required|string'
    ]);

    $filePath = $request->file('excel')->store('temp');
    $spreadsheet = IOFactory::load(storage_path('app/' . $filePath));
    $rows = $spreadsheet->getActiveSheet()->toArray();

    $service = $this->getDriveService();

    $output = [];

    foreach ($rows as $row) {

        // Ensure name column exists
        if (!isset($row[1])) continue;

        $fileName = str_replace("/", "", trim($row[1])) . ".jpg";

        // Detect correct column based on selected folder
        if ($request->folder === 'passports') {
            $driveUrl = $row[0] ?? null;   // passport column
        } else {
            $driveUrl = $row[2] ?? null;   // signature column
        }

        $driveUrl = trim($driveUrl);

        if ($driveUrl) {
            $fileId = $this->extractFileId($driveUrl);

            if ($fileId) {
                $savePath = $request->folder . '/' . $fileName;
                $output[] = $this->downloadFile($service, $fileId, $savePath);
            } else {
                $output[] = "Invalid URL: $driveUrl";
            }
        }
    }


        return back()->with('success_message', "Download Successful");
    }

    public function authCallback(Request $request)
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->addScope(Drive::DRIVE);
        $client->setRedirectUri(route('google.callback'));

        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        file_put_contents(storage_path('app/google/token.json'), json_encode($token));

        return redirect('admin/google-id-card')->with('success_message', 'Google authenticated successfully!');
    }
}
