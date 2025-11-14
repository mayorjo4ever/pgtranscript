<?php

namespace App\Exports;

use App\Models\CertificateData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class UncompletedCertificateExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    use Exportable;
    
     protected $approveDateId;
     
     public function __construct($approveDateId)
        {            
            $this->approveDateId = $approveDateId;
        }
        
        public function query()
        {
            return CertificateData::with('app_date')               
                ->where('approve_date_id', $this->approveDateId)
                ->where('completed', 0);            
        }
        
        
    public function headings(): array
    {
        return [
            'REG NO',
            'NAME',            
            "IN",
            'PROGRAMME',       
            'WITH',
            'CLASS OF DEGREE',
            'APPROVAL DATE',
            'PASSPORT',
        ];
    }

    public function map($student): array
    {
        return [
            $student->regno,
            $student->name, 
            "In",
            $student->raw_programme,
            'With',
            $student->degree_class,
            Carbon::parse($student->app_date->app_date)->format('jS F, Y'),
            $student->pix_name.".jpg",
        ];
    }

    // Start data from A3 so we can write description in A1
    public function startCell(): string
    {
        return 'A3';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Add description text at A1
                #$sheet->setCellValue('A1', 'UNIVERSITY OF ILORIN, POSTGRADUATE SCHOOL CERTIFICATE DATA  ');
                $sheet->setCellValue('A1', 'UNIVERSITY OF ILORIN, FACULTY OF ARTS CERTIFICATE DATA ');

                // Merge A1:G1 for better look
                $sheet->mergeCells('A1:H1'); #G1

                // Style the description
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '0000FF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]);

                // 2. Bold the headings
                $sheet->getStyle('A3:H3')->applyFromArray([ #G3
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD']
                    ]
                ]);

                // 3. Auto adjust columns manually (better than ShouldAutoSize for large datasets)
                foreach (range('A', 'H') as $col) { # G
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
