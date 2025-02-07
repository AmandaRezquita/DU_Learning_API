<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Student\Auth\StudentImage;
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

    public function getStudent($class_id){

        
        $students = StudentClass::where('class_id', $class_id)
        ->with('student')
        ->get();
   
    $response = [];
    foreach ($students as $s) {

        $image = StudentImage::find($s->student->student_image_id);

        $response[] = [
            'id' => $s->id,
            'student_id' => $s->student_id,
            'name' => $s->student ? $s->student->fullname : Null,
            'nis' => $s->student ? $s->student->student_number : Null,
            'image' => $image ? $image->image : null,
        ];
    }
        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subjects and teachers',
            'data' => $response,
        ], 200);
    }

    public function getStudentBySubject($class_id){

        
        $students = StudentClass::where('class_id', $class_id)
        ->with('student')
        ->get();
   
    $response = [];
    foreach ($students as $s) {

        $image = StudentImage::find($s->student->student_image_id);

        $response[] = [
            'id' => $s->id,
            'student_id' => $s->student_id,
            'name' => $s->student ? $s->student->fullname : Null,
            'phone_number' => $s->student ? $s->student->phone_number : Null,
            'nis' => $s->student ? $s->student->student_number : Null,
            'image' => $image ? $image->image : null,
        ];
    }
        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subjects and teachers',
            'data' => $response,
        ], 200);
    }

}
