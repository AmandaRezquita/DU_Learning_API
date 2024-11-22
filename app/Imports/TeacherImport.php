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
                'phone_number' => $row[3],
                'email' => $row[4],
                'teacher_avatar_id' => $row[5],
                'role_id' => $row[6],
            ]);
        }
    }
}
