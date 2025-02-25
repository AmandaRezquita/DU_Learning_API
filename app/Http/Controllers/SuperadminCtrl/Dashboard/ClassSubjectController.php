<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\Subject;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class ClassSubjectController extends Controller
{
    public function getSubject($class_id)
    {
        $subjects = ClassSubject::with(['teacher', 'subject']) 
            ->where('class_id', $class_id)
            ->get();

        if ($subjects->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No subjects found for this class',
                'data' => [],
            ], 200);
        }

        $response = $subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'subject_name' => $subject->subject ? $subject->subject->subject_name : 'Tidak ada subject',
                'teacher_name' => $subject->teacher ? $subject->teacher->fullname : 'Tidak ada guru',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subjects and teachers',
            'data' => $response,
        ], 200);
    }


    public function updateSubject(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'subject_id' => 'nullable|integer',
                'teacher_id' => 'nullable|integer',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $subject = ClassSubject::find($id);

        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => 'Subject not found',
            ], 200);
        }

        if ($request->has('subject_id') && $request->subject_id !== null) {
            $subject->subject_id = $request->subject_id;
        }

        if ($request->has('teacher_id') && $request->teacher_id !== null) {
            $subject->teacher_id = $request->teacher_id;
        }

        $subject->save();

        $subjectTeacher = ClassSubject::where('id', $subject->id)->first();
        $teacherName = $subjectTeacher && $subjectTeacher->teacher ? $subjectTeacher->teacher->fullname : 'Tidak ada guru';

        return response()->json([
            'status' => true,
            'message' => 'Subject updated successfully',
            'data' => [
                'id' => $subject->id,
                'subject_name' => $subject->subject_id,
                'teacher_name' => $teacherName,
            ]
        ], 200);
    }


    public function createSubject(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' => 'required|integer',
                    'subject_id' => 'required|integer',
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
                ->where('subject_id', $request->subject_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subject already exists in this class',
                ], 422);
            }


            $data = [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id
            ];

            $subject = ClassSubject::create($data);

            $class = SchoolClass::find($subject->class_id);

            $teacher = Teacher::find($subject->teacher_id);

            $subjectName = Subject::find($subject->subject_id);



            $success['subject_name'] = $subjectName ? $subjectName->subject_name : null;
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

    public function getSubjectById($id)
    {
        $subject = ClassSubject::with('teacher', 'subject')->find($id);

        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => 'Subject not found',
            ], 200);
        }

        $response = [
            'id' => $subject->id,
            'subject_name' => $subject->subject ? $subject->subject->subject_name : "Tidak ada guru",
            'teacher_name' => $subject->teacher ? $subject->teacher->fullname : 'Tidak ada guru',
        ];

        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subject',
            'data' => $response,
        ], 200);
    }

}
