<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use function env;
use function storage_path;
use function str_ends_with;
class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {zip}';
  
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
        set_time_limit(0);

        $zipPath = $this->argument('zip');
        $tempDir = storage_path('app/restore-temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive;
        $zip->open($zipPath);

        $sqlFile = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with(strtolower($name), '.sql')) {
                $sqlFile = $name;
                break;
            }
        }

        if (!$sqlFile) {
            $this->error('No SQL file found.');
            return;
        }

        $zip->extractTo($tempDir, $sqlFile);
        $zip->close();

        $sqlPath = $tempDir . DIRECTORY_SEPARATOR . $sqlFile;

        DB::disconnect();

        $mysql = env('MYSQL_BINARY', 'mysql');

        $command = sprintf(
            '"%s" -u%s -p%s %s < "%s"',
            $mysql,
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            $sqlPath
        );

        exec($command);

        // Cleanup
        unlink($sqlPath);
        unlink($zipPath);
    }
}
