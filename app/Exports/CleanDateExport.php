<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use ZipStream\Exception;

class CleanDateExport implements FromCollection, WithHeadings
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function collection()
    {
        $spreadsheet = IOFactory::load($this->file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($sheet->toArray(null, true, true, true) as $row) {
            $dob = $row['A'] ?? null; // assuming column A = date_of_birth
             $converted = cleanAndParseDate($dob);
            # $converted = cleanAndParseDate2($dob);

            $rows[] = [                
                'date_of_birth'  => $dob,
                'converted_date' => $converted,
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return ['Date of Birth', 'Converted Date'];
    }
            
}