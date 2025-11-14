<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class CourseImport implements OnEachRow, WithHeadingRow, WithChunkReading
{ 
    public int $inserted = 0;
    public int $updated = 0;

    public function onRow(Row $row)
    {
        $r = $row->toArray();
       # $current_approval = get_current_approve_date();
       #  $year = explode('-',$current_approval->app_date)[0];         

        if (!isset($r['code'])) return;

        $existing = Course::where('code', $r['code'])              
                ->first(); 
        if ($existing) {
            $existing->update([
                'title' => $r['title'] ?? '',
                'units' => $r['units'] ?? '',
                'type' => $r['type'] ?? '',
                'level'=> $r['level'] ?? '',
                'semester'=> $r['level'] ?? '',
                'host_department'=> $r['host_department'] ?? ''
            ]);
            $this->updated++;
        } else {
            Course::create([
               'code' => $r['code'] ?? '',
               'title' => $r['title'] ?? '',
               'units' => $r['units'] ?? '',
               'type' => $r['type'] ?? '',
               'level'=> $r['level'] ?? '',
               'semester'=> $r['semester'] ?? '',
               'host_department'=> $r['host_department'] ?? ''
            ]);
            $this->inserted++;
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Tune this based on your server
    }
}
