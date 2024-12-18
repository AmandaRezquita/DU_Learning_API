<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function deleteStudent(Request $request, $id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student not authenticated'
                ], 401);
            }

            $student->delete();
            
            auth()->guard('student')->logout();

            return response()->json([
                'status' => true,
                'message' => 'Student account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteTeacher(Request $request, $id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher not authenticated'
                ], 401);
            }

            $teacher->delete();
            
            auth()->guard('teacher')->logout();

            return response()->json([
                'status' => true,
                'message' => 'Teacher account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deletePrincipal(Request $request, $id)
    {
        try {
            $principal = Principal::find($id);

            if (!$principal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Principal not authenticated'
                ], 401);
            }

            $principal->delete();
            
            auth()->guard('principal')->logout();

            return response()->json([
                'status' => true,
                'message' => 'Principal account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
