<?php

namespace Database\Seeders;

use App\Models\CertificateApprovalDate;
use Illuminate\Database\Seeder;

class CertificateApprovalDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id'=>1,'app_date'=>'2022-03-22','description'=>'Senate'],
            ['id'=>2,'app_date'=>'2023-10-11','description'=>'Senate'],
            ['id'=>3,'app_date'=>'2023-11-14','description'=>'Senate'],
            ['id'=>4,'app_date'=>'2024-02-12','description'=>'Senate'],
            ['id'=>5,'app_date'=>'2024-05-02','description'=>'Senate'],
            ['id'=>6,'app_date'=>'2024-08-28','description'=>'Senate'],
            ['id'=>7,'app_date'=>'2024-09-20','description'=>'Senate']
            ];
         
            CertificateApprovalDate::insert($rows);
    }
}
