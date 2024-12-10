<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;

class TotalController extends Controller
{
    public function getTeacherTotal(){
        try {
            $totalTeacher = Teacher::count();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $totalTeacher
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getStudentTotal(){
        try {
            $totalStudent = Student::count();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $totalStudent
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getPrincipalTotal(){
        try {
            $principal = Principal::count();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $principal
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getClassTotal(){
        try {
            $class = SchoolClass::count();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $class
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
