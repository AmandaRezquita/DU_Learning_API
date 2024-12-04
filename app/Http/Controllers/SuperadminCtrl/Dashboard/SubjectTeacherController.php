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
                    'subject_id' => 'required|integer',
                    'teacher_id' => 'required|integer|exists:teachers,id',
                    'teacher_fullname' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $teacher = Teacher::find($request->teacher_id);

            if (!$teacher || $teacher->fullname !== $request->teacher_fullname) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher fullname does not match with the teacher ID',
                ], 422);
            }

            $data = [
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
                'teacher_fullname' => $request->teacher_fullname, 
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
