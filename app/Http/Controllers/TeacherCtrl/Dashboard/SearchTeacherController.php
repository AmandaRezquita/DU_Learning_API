<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Auth\Teacher;
use App\Models\Teacher\Auth\TeacherGender;
use App\Models\Teacher\Auth\TeacherImage;
use Illuminate\Http\Request;

class SearchTeacherController extends Controller
{
    public function SearchTeacher(Request $request)
    {
        try {
            $search = $request->query('search');
            $teacherList = Teacher::where(function ($query) use ($search) {
                $query->where('fullname', 'LIKE', '%' . $search . '%')
                    ->orWhere('nickname', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            })->get();

            $teachersData = [];
            foreach ($teacherList as $teacher) {
                $gender = TeacherGender::find($teacher->gender_id);
                $image = TeacherImage::find($teacher->teacher_image_id);

                $teachersData[] = [
                    'fullname' => $teacher->fullname,
                    'nickname' => $teacher->nickname,
                    'username' => $teacher->nickname,
                    'birth_date' => $teacher->birth_date,
                    'teacher_number' => $teacher->teacher_number,
                    'gender' => $gender ? $gender->name : null,
                    'phone_number' => $teacher->phone_number,
                    'email' => $teacher->email,
                    'image' => $image ? $image->image : null,
                    'role_id' => $teacher->role_id,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully fetched teacher data',
                'data' => $teachersData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
