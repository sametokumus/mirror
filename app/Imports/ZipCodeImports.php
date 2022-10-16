<?php

namespace App\Imports;

use App\Models\ImportZipCode;
use Maatwebsite\Excel\Concerns\ToModel;


class ZipCodeImports implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row){
        return new ImportZipCode([
            'il' => $row[0],
            'ilce' => $row[1],
            'semt' => $row[2],
            'mahalle' => $row[3],
            'pk' => $row[4]
        ]);
    }
}
