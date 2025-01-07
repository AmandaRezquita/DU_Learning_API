<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\StudentClass;
use Illuminate\Http\Request;
use Validator;

class StudentaddClassController extends Controller
{
    public function addStudent(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' => 'required|integer',
                    'student_id' => 'required|integer|exists:students,id',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $exists = StudentClass::where('class_id', $request->class_id)
                ->where('student_id', $request->student_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student already exists in this class',
                ], 422);
            }
            
            $data = [
                'class_id' => $request->class_id,
                'student_id' => $request->student_id,
            ];

            $subject = StudentClass::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Student added successfully',
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
