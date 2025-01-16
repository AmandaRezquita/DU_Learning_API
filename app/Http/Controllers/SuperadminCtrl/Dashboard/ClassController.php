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
                $totalTeachers = ClassSubject::where('class_id', $class->id)
                    ->distinct('teacher_id')
                    ->count('teacher_id');
                $totalStudents = StudentClass::where('class_id', $class->id)
                    ->distinct('student_id')
                    ->count('student_id');
                $totalSubjects = ClassSubject::where('class_id', $class->id)->count();

                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'class_description' => $class->class_description,
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

    public function updateClass(Request $request, $id){

        $validate = Validator::make(
            $request->all(),
            [
                'class_name' => 'nullable|string|max:255',
                'class_description' => 'nullable|string|max:255',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $class = SchoolClass::find($id);

        if (!$class) {
            return response()->json([
                'status' => false,
                'message' => 'Class not found',
            ], 422);
        }

        if ($request->has('class_name') && $request->class_name !== null) {
            $class->class_name = $request->class_name;
        }

        if ($request->has('class_description') && $request->class_description !== null) {
            $class->class_description = $request->class_description;
        }

        $class->save();

        return response()->json([
            'status' => true,
            'message' => 'Subject updated successfully',
            'data' => [
                'id' => $class->id,
                'class_name' => $class->class_name,
                'class_description' => $class->class_description,
            ]
        ], 200);
    }

    public function createClass(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_name' => 'required|string|max:255|unique:school_classes',
                    'class_description' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()
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

    public function ClassDetail($class_id)
    {
        try {
            $class = SchoolClass::with(['teachers', 'students', 'subjects'])
                ->findOrFail($class_id);

            $classData = [
                'id' => $class->id,
                'name' => $class->class_name,
                'description' => $class->class_description,
                'teachers' => $class->teachers->map(function ($teacher) {
                    $teacher = Student::find($teacher->teacher_id);
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->fullname,
                    ];
                }),
                'students' => $class->students->map(function ($student) {
                    $student = Student::find($student->student_id);
                    return [
                        'id' => $student->id,
                        'name' => $student->fullname,
                    ];
                }),
                'subjects' => $class->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->subject_name,
                    ];
                }),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Class details retrieved successfully',
                'data' => $classData,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Class not found',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

}
