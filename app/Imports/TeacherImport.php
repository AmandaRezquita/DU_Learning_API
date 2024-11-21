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
                'name' => $row[0],
                'phone_number' => $row[1],
                'email' => $row[2],
                'teacher_avatar_id' => $row[3],
            ]);
        }
    }
}
