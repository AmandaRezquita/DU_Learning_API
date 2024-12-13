<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;

class SearchTeacherController extends Controller
{
    public function SearchTeacher(Request $request)
    {
        try {
            $search = $request->query('search');

            $teacherList = Teacher::where(function ($query) use ($search) {
                $query->where('fullname', 'LIKE', '%' . $search . '%')
                    ->orWhere('nickname', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            })->get();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $teacherList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
