<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\Schedule;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use App\Models\Teacher\Dashboard\AddMaterials;
use App\Models\Teacher\Dashboard\AddTask;
use Illuminate\Http\Request;
use Storage;

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
                ], 422);
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
                ], 422);
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
                ], 422);
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
                ], 422);
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
            $class = ClassSubject::find($id);

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher not found'
                ], 422);
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
                ], 422);
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
                ], 422);
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

    public function deleteSchedule(Request $request, $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return response()->json([
                    'status' => false,
                    'message' => 'Schedule not found'
                ], 422);
            }

            $schedule->delete();

            return response()->json([
                'status' => true,
                'message' => 'Schedule deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteMaterial(Request $request, $id)
    {
        try {
            $material = AddMaterials::find($id);

            if (!$material) {
                return response()->json([
                    'status' => false,
                    'message' => 'Material not found'
                ], 422);
            }

            if ($material->file && Storage::disk('public')->exists($material->file)) {
                Storage::disk('public')->delete($material->file);
            }

            $material->delete();

            return response()->json([
                'status' => true,
                'message' => 'Material deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteTask (Request $request, $id)
    {
        try {
            $task = AddTask::find($id);

            if (!$task) {
                return response()->json([
                    'status' => false,
                    'message' => 'Task not found'
                ], 422);
            }

            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }

            $task->delete();

            return response()->json([
                'status' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteStudentTask (Request $request, $id)
    {
        try {
            $task = StudentTask::find($id);

            if (!$task) {
                return response()->json([
                    'status' => false,
                    'message' => 'Task not found'
                ], 422);
            }

            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }

            $task->delete();

            return response()->json([
                'status' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
