<?php

namespace App\Imports;

# use App\Models\;


use App\Models\CertificateData;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use function get_current_approve_date;


class CertificateDataImport implements OnEachRow, WithHeadingRow, WithChunkReading
{ 
    public int $inserted = 0;
    public int $updated = 0;

    public function onRow(Row $row)
    {
        $r = $row->toArray();
        $current_approval = get_current_approve_date();
        $year = explode('-',$current_approval->app_date)[0];         

        if (!isset($r['regno'])) return;

        $existing = CertificateData::where('regno', $r['regno'])
                ->where('year', $year)
                ->first();

        if ($existing) {
            $existing->update([
                'raw_name' => $r['name'] ?? '',
                'raw_programme' => $r['programme'] ?? '',
                'approve_date_id' => $current_approval->id,
                'degree_class'=> $r['degree_class'] ?? ''
            ]);
            $this->updated++;
        } else {
            CertificateData::create([
                'regno' => $r['regno'],
                'raw_name' => $r['name'] ?? '',
                'raw_programme' => $r['programme'] ?? '',
                'approve_date_id' => $current_approval->id,
                'year' => $year,
                'degree_class'=> $r['degree_class'] ?? ''
            ]);
            $this->inserted++;
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Tune this based on your server
    }
    
    
    /**
    * @param array $row
    *
    * @return Model|null
    */
    
    /**public function collection(Collection $rows)
    {
        $current_approval = get_current_approve_date();
        $year = explode('-',$current_approval->app_date)[0]; 
        
        foreach ($rows as $row) {
            if (!isset($row['regno'])) {
                continue; // skip rows without regno
            }

            CertificateData::updateOrCreate(
                ['regno' => $row['regno'],'year' => $year], // condition
                [ // update or insert these
                    'raw_name' => $row['name'] ?? '',
                    'raw_programme' => $row['programme'] ?? '',
                    'approve_date_id' => $current_approval->id
                ]
            );
        }
    }
     * 
     */
}