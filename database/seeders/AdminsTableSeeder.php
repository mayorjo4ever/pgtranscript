<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
//     ['id'=>1,'regno'=>'s6068','title'=>'Prof.', 'surname'=>'Ojo','firstname'=>'Isaac','othername'=>'Mayowa',
//                'mobile'=>'07030577951','image'=>'','status'=>1,'email'=>'mayorjo82@yahoo.com','password'=> Hash::make('1215'),'confirm'=>'yes']
//            ];
//    ['id'=>3,'regno'=>'S6204','title'=>'Mr..', 'surname'=>'FAKUNLE','firstname'=>'Kazeem','othername'=>'Abiodun',
//                'mobile'=>'08066534068','image'=>'','status'=>1,'email'=>'fakunle.ka@unilorin.edu.ng','password'=> Hash::make('08066534068'),'confirm'=>'yes']
//            ];
//    ['id'=>4,'regno'=>'agamata','title'=>'Mr..', 'surname'=>'OKEKE','firstname'=>'Emma','othername'=>'Obum',
//                'mobile'=>'09067367351','image'=>'','status'=>1,'email'=>'eookeke3@gmail.com','password'=> Hash::make('emmasystem123'),'confirm'=>'yes']
//            ];
            /*
            ['id'=>5,'regno'=>'joy','title'=>'Mrs..', 'surname'=>'Osademe','firstname'=>'Joy','othername'=>'Oreka',
                'mobile'=>'08036208250','image'=>'','status'=>1,'email'=>'osademe.ja@gmail.com','password'=> Hash::make('osademe'),'confirm'=>'yes']
            ];
             ['id'=>6,'regno'=>'codemaster','title'=>'Mr..', 'surname'=>'Awofesobi','firstname'=>'Peace','othername'=>'Kolade',
                'mobile'=>'08116405518','image'=>'','status'=>1,'email'=>'awofesobipeace@gmail.com','password'=> Hash::make('2207'),'confirm'=>'yes']
            ];
         
             * 
             *              */
    public function run(): void
    {
         $adminRecords = [
            ['id'=>7,'regno'=>'gloria','title'=>'Miss..', 'surname'=>'Popoola','firstname'=>'Gloria','othername'=>'Tolulope',
                'mobile'=>'07087017291','image'=>'','status'=>1,'email'=>'gloriaoladuni03@gmail.com','password'=> Hash::make('Gloria@Server22'),'confirm'=>'yes']
            ];
         
        Admin::insert($adminRecords);
    }
}
