<?php

namespace App\Imports;

use App\Models\Transcript;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class TranscriptImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
     public function onRow(Row $row)
    {
        $row = $row->toArray();
       
            Transcript::create(
                ['regno'    => $row['regno'] ?? null,
                'code'      => $row['code'] ?? null,                                    
                'title'     => $row['title'] ?? null,
                'units'     => $row['units'] ?? null,
                'type'      => $row['type'] ?? null,
                'score'     => $row['score'] ?? null,
                'starred'   => $row['starred'] ?? null,                         
                'completed' => $row['completed'] ?? null,            
                'approve_date'  => $row['approve_date'] ?? null,
                'created_by'    => $row['created_by'] ?? null
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
