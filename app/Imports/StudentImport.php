<?php

namespace App\Imports;

use App\Models\Student\Auth\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $header = $rows->first();
        $dataRows = $rows->slice(1);

        $header = $header->map(function ($column) {
            return strtolower($column);
        });

        foreach ($dataRows as $row) {
            $data = [
                'fullname' => $row[$header->search('fullname')],
                'nickname' => $row[$header->search('nickname')],
                'birth_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$header->search('birth_date')])->format('Y-m-d'),
                'student_number' => $row[$header->search('student_number')],
                'gender_id' => $row[$header->search('gender_id')],
                'phone_number' => $row[$header->search('phone_number')],
                'email' => $row[$header->search('email')],
            ];

            Student::create($data);
        }
    }
}
