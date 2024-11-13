<?php

namespace App\Imports;

use App\Models\Student;
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
                'name' => $row[0],
                'phone_number' => $row[1],
                'email' => $row[2],
                'student_avatar_id' => $row[3],
            ]);
        }
    }
}
