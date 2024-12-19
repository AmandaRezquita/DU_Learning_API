<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
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

    public function deleteClass(Request $request, $id)
    {
        try {
            $class = SchoolClass::find($id);

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Class not found'
                ], 401);
            }

            $class->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Class deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteTeacherClass(Request $request, $id)
    {
        try {
            $class = subjectaddTeacher::find($id);

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher not found'
                ], 401);
            }

            $class->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Teacher deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteStudentClass(Request $request, $id)
    {
        try {
            $class = StudentClass::find($id);

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student not found'
                ], 401);
            }

            $class->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Student deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteClassSubject(Request $request, $id)
    {
        try {
            $class = ClassSubject::find($id);

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subject not found'
                ], 401);
            }

            $class->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Subject deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
