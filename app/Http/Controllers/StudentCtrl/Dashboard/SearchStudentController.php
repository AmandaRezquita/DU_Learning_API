<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
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

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $studentList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}
