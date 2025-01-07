<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use Illuminate\Http\Request;
use Validator;

class ClassSubjectController extends Controller
{
    public function subjectList()
    {
        try {
            $subjectList = ClassSubject::all();

            return response()->json([
                'status' => true,
                'message' => 'List retrieved successfully',
                'data' => $subjectList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function createSubject(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'subject_name' => 'required|string|max:255',
                    'class_id' => 'required|integer',
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
                'subject_name' => $request->subject_name,
                'class_id' => $request->class_id,
            ];

            $subject = ClassSubject::create($data);

            $class = SchoolClass::find($subject->class_id);


            $success['subject_name'] = $subject->subject_name;
            $success['class_id'] = $subject->class_id;
            $success['class_name'] = $class ? $class->class_name : null;

            return response()->json([
                'status' => true,
                'message' => 'Class created successfully',
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
