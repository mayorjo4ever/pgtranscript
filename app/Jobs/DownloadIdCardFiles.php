<?php

namespace App\Jobs;

use App\Models\GoogleIdCardForm;
use Exception;
use Illuminate\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadIdCardFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function handle()
    {
        set_time_limit(0);

        GoogleIdCardForm::whereIn('id', $this->ids)->each(function ($record) {

            if ($record->passport) {
                $this->download($record->passport, 'passports', $record->regno);
            }

            if ($record->signature) {
                $this->download($record->signature, 'signatures', $record->regno);
            }
        });
    }

    private function download($url, $folder, $regno)
    {
        try {
            $response = Http::timeout(60)->get($url);

            if ($response->successful()) {
               $filename = strtoupper(str_replace("/","",$regno)). '.jpg';
                Storage::put("public/{$folder}/{$filename}", $response->body());
            }
        } catch (Exception $e) {
            \Log::error('Download failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }
}
