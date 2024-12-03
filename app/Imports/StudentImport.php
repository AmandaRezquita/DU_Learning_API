<?php

namespace App\Imports;

use App\Models\Student\Auth\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Student::create([
                'fullname' => $row[0],
                'nickname' => $row[1],
                'birth_date' => $row[2],
                'phone_number' => $row[3],
                'email' => $row[4],
                'student_avatar_id' => $row[5],
                'role_id' => $row[6],
            ]);
        }
    }
}
