<?php

namespace App\Console\Commands;

use App\Models\TranscriptsRequest;
use Illuminate\Console\Command;

class NormalizeDriveLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:normalize-drive-links';
     

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $fields = [
        'certificate_url',
        'rrr_receipt_url',
        'courier_receipt_url',
        'pgschool_receipt_url',
    ];

    TranscriptsRequest::chunk(200, function ($rows) use ($fields) {

        foreach ($rows as $row) {
            $changed = false;

            foreach ($fields as $field) {
                $new = normalizeDriveLink($row->$field);

                if ($new && $new !== $row->$field) {
                    $row->$field = $new;
                    $changed = true;
                }
            }

            if ($changed) {
                $row->save();
            }
        }
    });

    $this->info('✅ Google Drive links normalized successfully.');
}

}
