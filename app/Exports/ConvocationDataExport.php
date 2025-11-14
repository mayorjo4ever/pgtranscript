<?php

namespace App\Exports;

use App\Models\CertificateData;
use App\Models\Programme;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class ConvocationDataExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, ShouldAutoSize
{
     use Exportable;

    protected $programmes;
    protected $approveDateId;

    public function __construct(array $programmes, $approveDateId)
    {
        $this->programmes = $programmes;
        $this->approveDateId = $approveDateId;
    }

    public function query()
    {
       return CertificateData::with(['programme', 'degree', 'app_date'])
        ->leftJoin('users', 'certificate_data.regno', '=', 'users.regno')
        ->whereIn('certificate_data.raw_programme', $this->programmes)
        ->where('certificate_data.approve_date_id', $this->approveDateId)
        ->where('certificate_data.completed', 1)
        ->select(
            'certificate_data.*',
            'users.programme as user_programme'
        );           
    }

    public function headings(): array
    {
        return [
            'student_number',            
            'class_of_degree',
            'date_of_graduation',
            'session_graduated',       
            'programme_graduated_from',
            'presentation',
        ];
    }
    public function map($student): array
    {
        return [
            $student->regno,
            'Graduated',
            Carbon::parse($student->app_date->app_date)->format('d-m-Y'),
            "2024/2025",          
             $student-> user_programme ?? $student->degree->short_name." ".$student->programme->name,                        
            "Presentation I"            
        ];
    }
    
    // 'With',
    // $student->degree_class,

    // Start data from A3 so we can write description in A1
    public function startCell(): string
    {
        return 'A1';  ## A3
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Add description text at A1
               # $sheet->setCellValue('A1', 'UNIVERSITY OF ILORIN, POSTGRADUATE SCHOOL CERTIFICATE DATA  ');
                # $sheet->setCellValue('A1', 'UNIVERSITY OF ILORIN, FACULTY OF ARTS CERTIFICATE DATA ');

                // Merge A1:G1 for better look
               # $sheet->mergeCells('A1:G1'); # I1

                // Style the description
                /**
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '0000FF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]); **/

                // 2. Bold the headings
                /**
                $sheet->getStyle('A1:G1')->applyFromArray([ #I3
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD']
                    ]
                ]); **/

                // 3. Auto adjust columns manually (better than ShouldAutoSize for large datasets)
                foreach (range('A', 'G') as $col) { # I
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
