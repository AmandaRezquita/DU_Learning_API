<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Student\Auth\StudentGender;
use App\Models\Student\Auth\StudentImage;
use Illuminate\Http\Request;

class SearchStudentController extends Controller
{
    public function SearchStudent(Request $request)
    {
        try {
            $search = $request->query('search');

            $studentList = Student::where(function ($query) use ($search) {
                $query->where('fullname', 'LIKE', '%' . $search . '%')
                    ->orWhere('nickname', 'LIKE', '%' . $search . '%')
                    ->orWhere('student_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            })->get();

            $studentsData = [];

            foreach ($studentList as $student) {
                $gender = StudentGender::find($student->gender_id);
                $image = StudentImage::find($student->student_image_id);

                $studentsData[] = [
                    'fullname' => $student->fullname,
                    'nickname' => $student->nickname,
                    'username' => $student->nickname,  
                    'birth_date' => $student->birth_date,
                    'student_number' => $student->student_number,
                    'gender' => $gender ? $gender->name : null,
                    'phone_number' => $student->phone_number,
                    'email' => $student->email,
                    'image' => $image ? $image->image : null,
                    'role_id' => $student->role_id,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully fetched student data',
                'data' => $studentsData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
