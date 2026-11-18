<?php

namespace App\Imports;

use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\ProductMenu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductMenu([
            //
        ]);
    }

    public function dealImportData($row)
    {

    }
}
