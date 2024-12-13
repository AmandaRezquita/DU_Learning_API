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
                'student_number' => $row[3],
                'gender_id' => $row[4],
                'phone_number' => $row[5],
                'email' => $row[6],
                'role_id' => $row[7],
            ]);
        }
    }
}
