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
                'teacher_number' => $row[$header->search('teacher_number')],
                'gender_id' => $row[$header->search('gender_id')],
                'phone_number' => $row[$header->search('phone_number')],
                'email' => $row[$header->search('email')],
            ];

            Teacher::create($data);
        }
    }
}
