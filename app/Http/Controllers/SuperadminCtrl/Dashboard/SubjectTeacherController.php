<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher; // Pastikan namespace model Teacher benar
use Illuminate\Http\Request;
use Validator;

class SubjectTeacherController extends Controller
{
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

            $exists = subjectaddTeacher::where('class_id', $request->class_id)
                ->where('teacher_id', $request->teacher_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher already exists in this class',
                ], 409);
            }

            $teacher = Teacher::find($request->teacher_id);
            
            $data = [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
            ];

            $subject = subjectaddTeacher::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Teacher added successfully',
                'data' => $subject,
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
