<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CleanDateImport implements ToCollection
{
    public $rows;

    public function collection(Collection $collection)
    {
        // Store all rows for later use in export
        $this->rows = $collection;
    }
}
