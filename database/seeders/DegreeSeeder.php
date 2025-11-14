<?php

namespace Database\Seeders;

use App\Models\Degree;
use Illuminate\Database\Seeder;

class DegreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id'=>1,'short_name'=>'D.','full_name'=>'Diploma'],
            ['id'=>2,'short_name'=>'Ph.D','full_name'=>'Doctor of Philosophy'],
            ['id'=>3,'short_name'=>'DVM.','full_name'=>'Doctor of Veterinary Medicine'],
            ['id'=>4,'short_name'=>'M.','full_name'=>'Master'],
            ['id'=>5,'short_name'=>'M.A','full_name'=>'Master of Arts'],
            ['id'=>6,'short_name'=>'H.Comm.H.','full_name'=>'Master of Community Health'],
            ['id'=>7,'short_name'=>'M.Ed.','full_name'=>'Master of Education'],
            ['id'=>8,'short_name'=>'M.Eng.','full_name'=>'Master of Engineering'],
            ['id'=>9,'short_name'=>'LL.M.','full_name'=>'Master of Law'],
            ['id'=>10,'short_name'=>'M.Phil.','full_name'=>'Master of Philosophy'],
            ['id'=>11,'short_name'=>'M.Sc','full_name'=>'Master of Science'],
            ['id'=>12,'short_name'=>'M.Sc.Ed.','full_name'=>'Master of Science Education'],
            ['id'=>13,'short_name'=>'PGD.','full_name'=>'Postgraduate Diploma'],
        ]; 
        Degree::insert($rows);
    }
}
