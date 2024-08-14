<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class CSVImport implements ToCollection, WithCustomCsvSettings
{
    public function collection(Collection $rows){
        
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ";",
            'input_encoding' => 'ISO-8859-1'
        ];
    }
}