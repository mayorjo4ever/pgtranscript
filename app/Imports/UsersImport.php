<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    public function onRow(Row $row)
    {
        $row = $row->toArray();

        try {
            User::updateOrCreate(
                [
                    'regno' => $row['regno'] ?? null,
                    'email' => $row['email_address'] ?? null,
                ],           [         
            'appno'             => $row['appno'] ?? null,
            'first_name'        => $row['first_name'] ?? null,
            'middle_name'       => $row['middle_name'] ?? null,
            'last_name'         => $row['last_name'] ?? null,
            'gender'            => $row['gender'] ?? null,
            'dob'               => isset($row['date_of_birth']) ? cleanAndParseDate($row['date_of_birth']) : null,
            'marital_status'    => $row['marital_status'] ?? null,            
            'phone'             => $row['phone_number'] ?? null,
            'department'        => $row['department'] ?? null,
            'programme'         => $row['programme'] ?? null,
            'level'             => $row['level'] ?? null,
            'admission_session' => $row['admission_session'] ?? null,
            'category'          => $row['category'] ?? null,
            'state_of_origin'   => $row['state_of_origin'] ?? null,
            'local_government'  => $row['local_government'] ?? null,
            'ward'              => $row['ward'] ?? null,
            'address'           => $row['address'] ?? null,
            'status'            => $row['status'] ?? 'Active',
            'account_number'    => $row['account_number'] ?? null,
            'account_name'      => $row['account_name'] ?? null,
            'bank_name'         => $row['bank_name'] ?? null,
            'bvn'               => $row['bvn'] ?? null,
            'nin'               => $row['nin'] ?? null,
            'pvc_number'        => $row['pvc_number'] ?? null,
            'password'          => Hash::make('password'), // default password
         ]
        );
   
        } catch (\Exception $e) {
            \Log::error("Row import failed: " . $e->getMessage(), ['row' => $row]);
        }
    }
    /**
     * Use regno to prevent duplicate inserts.
     */
//    public function uniqueBy()
//    {
//        return 'regno';  // unique key column
//    }

    /**
     * Process in chunks (for large files).
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
