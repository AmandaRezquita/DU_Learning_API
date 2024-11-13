<?php

namespace App\Imports;

use App\Models\Principal\Auth\Principal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PrincipalImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Principal::create([
                'name' => $row[0],
                'phone_number' => $row[1],
                'email' => $row[2],
                'principal_avatar_id' => $row[3],
            ]);
        }
    }
}
