<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class ClassController extends Controller
{

    public function classList()
    {
        try {
            $classList = SchoolClass::all()->map(function ($class) {
                $totalTeachers = subjectaddTeacher::where('class_id', $class->id)->count();
                $totalStudents = StudentClass::where('class_id', $class->id)->count();
                $totalSubjects = ClassSubject::where('class_id', $class->id)->count();

                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'total_teachers' => $totalTeachers,
                    'total_students' => $totalStudents,
                    'total_subjects' => $totalSubjects,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'List retrieved successfully',
                'data' => $classList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function SearchClass(Request $request)
    {
        try {
            $search = $request->query('search');

            $classList = SchoolClass::where(function ($query) use ($search) {
                $query->where('class_name', 'LIKE', '%' . $search . '%');
            })->get();

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $classList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function createClass(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_name' => 'required|string|max:255',
                    'class_description' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = [
                'class_name' => $request->class_name,
                'class_description' => $request->class_description,
            ];

            $class = SchoolClass::create($data);

            $success['class_name'] = $class->class_name;
            $success['class_description'] = $class->class_description;

            return response()->json([
                'status' => true,
                'message' => 'Class created successfully',
                'data' => $success
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);

        }
    }
}
