<?php

namespace App\Imports;

use App\Models\Teacher\Auth\Teacher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TeacherImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Teacher::created([
                'fullname' => $row[0],
                'nickname' => $row[1],
                'birth_date' => $row[2],
                'gender_id' => $row[3],
                'phone_number' => $row[4],
                'email' => $row[5],
                'role_id' => $row[6],
            ]);
        }
    }
}
