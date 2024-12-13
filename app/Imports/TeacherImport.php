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
                'teacher_number' => $row[3],
                'gender_id' => $row[4],
                'phone_number' => $row[5],
                'email' => $row[6],
            ]);
        }
    }
}
