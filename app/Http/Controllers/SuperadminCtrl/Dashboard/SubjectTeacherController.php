<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class SubjectTeacherController extends Controller
{
    public function getTeacherSubject($class_id, $subject_id)
    {
        $teacherList = ClassSubject::where('class_id', $class_id)
            ->where('id', $subject_id)
            ->with('teacher')
            ->get();
       
        $response = [];
        foreach ($teacherList as $teacherEntry) {
            $response[] = [
                'teacher_name' => $teacherEntry->teacher ? $teacherEntry->teacher->fullname : 'Tidak ada guru',
            ];
        }


        if ($teacherList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Teacher not found',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $response,
        ], 200);
    }

    public function addTeacher(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' => 'required|integer',
                    'subject_id' => 'required|integer',
                    'teacher_id' => 'required|integer|exists:teachers,id',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $exists = subjectaddTeacher::where('subject_id', $request->subject_id)
                ->where('teacher_id', $request->teacher_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher already exists in this subject',
                ], 422);
            }

            $teacher = Teacher::find($request->teacher_id);

            $data = [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
            ];

            $s = subjectaddTeacher::create($data);

            $subject = ClassSubject::find($s->subject_id);
            $class = SchoolClass::find($s->class_id);
            $teacher = Teacher::find($s->teacher_id);

            $success['subject_name'] = $subject->subject_name;
            $success['class_name'] = $class ? $class->class_name : null;
            $success['teacher_name'] = $teacher ? $teacher->fullname : null;

            return response()->json([
                'status' => true,
                'message' => 'Teacher added successfully',
                'data' => $success,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
