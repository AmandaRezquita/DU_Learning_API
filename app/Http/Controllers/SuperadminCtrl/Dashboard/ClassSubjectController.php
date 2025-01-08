<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class ClassSubjectController extends Controller
{
    public function getSubject($class_id)
    {
        $subjects = ClassSubject::where('class_id', $class_id)->get();

        if ($subjects->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Subjects not found for the given class',
            ], 422);
        }

        $response = [];
        foreach ($subjects as $subject) {
            $subjectTeacher = ClassSubject::where('id', $subject->id)->first();
            $teacherName = $subjectTeacher && $subjectTeacher->teacher ? $subjectTeacher->teacher->fullname : 'Tidak ada guru';
            $response[] = [
                'subject_name' => $subject->subject_name,
                'teacher_name' => $teacherName,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subjects and teachers',
            'data' => $response,
        ], 200);
    }


    public function createSubject(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' => 'required|integer',
                    'subject_name' => 'required|string|max:255',
                    'teacher_id' => 'required|integer',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $exists = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_name', $request->subject_name)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subject already exists in this class',
                ], 422);
            }


            $data = [
                'class_id' => $request->class_id,
                'subject_name' => $request->subject_name,
                'teacher_id' => $request->teacher_id
            ];

            $subject = ClassSubject::create($data);

            $class = SchoolClass::find($subject->class_id);

            $teacher = Teacher::find($subject->teacher_id);

            $success['subject_name'] = $subject->subject_name;
            $success['class_name'] = $class ? $class->class_name : null;
            $success['teacher_name'] = $teacher ? $teacher->fullname : null;

            return response()->json([
                'status' => true,
                'message' => 'Subject created successfully',
                'data' => $success,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);

        }
    }
}
