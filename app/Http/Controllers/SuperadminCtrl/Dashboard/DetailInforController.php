<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Principal\Auth\Principal_Gender;
use App\Models\Principal\Auth\Principal_Image;
use App\Models\Student\Auth\Student;
use App\Models\Student\Auth\StudentGender;
use App\Models\Student\Auth\StudentImage;
use App\Models\Teacher\Auth\Teacher;
use App\Models\Teacher\Auth\TeacherGender;
use App\Models\Teacher\Auth\TeacherImage;
use Illuminate\Http\Request;

class DetailInforController extends Controller
{
    public function detailStudent($id)
    {
        $studentData = Student::find($id);

        if (!$studentData) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $gender = StudentGender::find($studentData->gender_id);
        $image = StudentImage::find($studentData->student_image_id);

        $response = [
            'fullname' => $studentData->fullname,
            'nickname' => $studentData->nickname,
            'username' => $studentData->username,
            'birth_date' => $studentData->birth_date,
            'student_number' => $studentData->student_number,
            'gender' => $gender ? $gender->name : null,
            'phone_number' => $studentData->phone_number,
            'email' => $studentData->email,
            'image' => $image ? $image->image : null,
            'role_id' => $studentData->role_id,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $response,
        ], 200);
    }

    public function detailTeacher($id)
    {
        $teacherData = Teacher::find($id);

        if (!$teacherData) {
            return response()->json([
                'status' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $gender = TeacherGender::find($teacherData->gender_id);
        $image = TeacherImage::find($teacherData->teacher_image_id);

        $response = [
            'fullname' => $teacherData->fullname,
            'nickname' => $teacherData->nickname,
            'username' => $teacherData->username,
            'birth_date' => $teacherData->birth_date,
            'teacher_number' => $teacherData->teacher_number,
            'gender' => $gender ? $gender->name : null,
            'phone_number' => $teacherData->phone_number,
            'email' => $teacherData->email,
            'image' => $image ? $image->image : null,
            'role_id' => $teacherData->role_id,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $response,
        ], 200);
    }

    public function detailPrincipal($id)
    {
        $principalData = Principal::find($id);

        if (!$principalData) {
            return response()->json([
                'status' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $gender = Principal_Gender::find($principalData->gender_id);
        $image = Principal_Image::find($principalData->principal_image_id);

        $response = [
            'fullname' => $principalData->fullname,
            'nickname' => $principalData->nickname,
            'username' => $principalData->username,
            'birth_date' => $principalData->birth_date,
            'principal_number' => $principalData->principal_number,
            'gender' => $gender ? $gender->name : null,
            'phone_number' => $principalData->phone_number,
            'email' => $principalData->email,
            'image' => $image ? $image->image : null,
            'role_id' => $principalData->role_id,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $response,
        ], 200);
    }
}
