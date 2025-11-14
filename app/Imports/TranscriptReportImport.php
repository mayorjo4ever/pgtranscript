<?php

namespace App\Imports;

use App\Models\TranscriptReport;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class TranscriptReportImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
     public function onRow(Row $row)
    {
        $row = $row->toArray();
       ## 				
       TranscriptReport::create(
                ['regno'    => $row['regno'] ?? null,
                'name'      => $row['name'] ?? null,                                    
                'fact_id'     => $row['fact_id'] ?? null,
                'dept_id'     => $row['dept_id'] ?? null,
                'programme'      => $row['programme'] ?? null,
                'first_reg_date'     => $row['first_reg_date'] ?? null,
                'approve_date'   => $row['approve_date'] ?? null,                         
                'created_by' => $row['created_by'] ?? null
                ]
            ); 
    }   

    /**
     * Process in chunks (for large files).
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
