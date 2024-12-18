<?php

namespace App\Http\Controllers\TeacherCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Imports\TeacherImport;
use App\Mail\sendTeacherEmail;
use App\Models\Teacher\Auth\TeacherGender;
use App\Models\Teacher\Auth\TeacherImage;
use Illuminate\Http\Request;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use Maatwebsite\Excel\Facades\Excel;
use Storage;
use Str;

class TeacherController extends Controller
{
    public function TeacherList()
{
    try {
        $teacherList = Teacher::all()->map(function ($teacher) {

            $gender = TeacherGender::find($teacher->gender_id);

            $image = TeacherImage::find($teacher->teacher_image_id);

            return [
                'id' => $teacher->id,
                'fullname' => $teacher->fullname,
                'nickname' => $teacher->nickname,
                'birth_date' => $teacher->birth_date,
                'teacher_number' => $teacher->teacher_number,
                'gender' =>  $gender ? $gender->name : null,
                'phone_number' => $teacher->phone_number,
                'email' => $teacher->email,
                'image' => $image ? $image->image : null,
                'role_id' => $teacher->role_id,
            ];
        });
        return response()->json([
            'status' => true,
            'message' => 'Teachers retrieved successfully',
            'data' => $teacherList
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}
    public function register(Request $request)
    {
        try {
            $validateTeacher = Validator::make(
                $request->all(),
                [
                    'fullname' => 'required|string|max:255',
                    'nickname' => 'required|string|max:255',
                    'birth_date' => 'required|string|max:255',
                    'teacher_number' => 'required|string|max:255',
                    'gender_id' => 'required|integer',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:teachers,email',
                ]
            );


            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTeacher->errors()
                ], 422);
            }

            $teacherImageId = $request->gender_id == 1 ? 2 : ($request->gender_id == 2 ? 1 : null);

            $data = [
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'birth_date' => $request->birth_date,
                'teacher_number' => $request->teacher_number,
                'gender_id' => $request->gender_id,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'teacher_image_id' => $teacherImageId,
                'role_id' => 2,
            ];

            $teacher = $this->handleRecordCreation($data);

            $gender = TeacherGender::find($teacher->gender_id);

            $image = TeacherImage::find($teacher->teacher_image_id);

            $token = $teacher->createToken("API TOKEN")->plainTextToken;

            $success['fullname'] = $teacher->fullname;
            $success['nickname'] = $teacher->nickname;
            $success['birth_date'] = $teacher->birth_date;
            $success['teacher_number'] = $teacher->teacher_number;
            $success['gender'] = $gender ? $gender->name : null;
            $success['phone_number'] = $teacher->phone_number;
            $success['email'] = $teacher->email;
            $success['image'] = $image ? $image->image : null;
            $success['role_id'] = $teacher->role_id;


            return response()->json([
                'status' => true,
                'message' => 'Account created successfully',
                'token' => $token,
                'data' => $success
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function profile()
    {
        $teacherData = auth()->user();

        $gender = TeacherGender::find($teacherData->gender_id);

        $image = TeacherImage::find($teacherData->teacher_image_id);

        $success['fullname'] = $teacherData->fullname;
        $success['nickname'] = $teacherData->nickname;
        $success['username'] = $teacherData->nickname;
        $success['birth_date'] = $teacherData->birth_date;
        $success['teacher_number'] = $teacherData->teacher_number;
        $success['gender'] = $gender ? $gender->name : null;
        $success['phone_number'] = $teacherData->phone_number;
        $success['email'] = $teacherData->email;
        $success['image'] = $image ? $image->image : null;
        $success['role_id'] = $teacherData->role_id;

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $success,
        ], 200);
    }

    public function edit_email(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateTeacher = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:teachers,email,' . $teacher->id,
            ]);

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateTeacher->errors()
                ], 422);
            }

            if ($request->has('email') && $request->email === $teacher->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'The new email cannot be the same as the current email.'
                ], 422);
            }

            if ($request->has('email')) {
                $teacher->email = $request->email;
            }

            $teacher->save();

            return response()->json([
                'status' => true,
                'message' => 'email updated successfully',
                'data' => $teacher
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function edit_password(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
            }

            $validateTeacher = Validator::make($request->all(), [
                'current_password' => 'required|string|min:8',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validateTeacher->fails()) {
                return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validateTeacher->errors()], 422);
            }

            if (!Hash::check($request->current_password, $teacher->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 401);
            }

            $teacher->password = Hash::make($request->password);
            $teacher->save();

            return response()->json(['status' => true, 'message' => 'Password updated successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => $th->getMessage()], 500);
        }
    }


    public function edit_username(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateTeacher = Validator::make($request->all(), [
                'username' => 'nullable|string|max:255',
            ]);

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateTeacher->errors()
                ], 422);
            }

            if ($request->has('username')) {
                $teacher->username = $request->username;
            }

            $teacher->save();

            return response()->json([
                'status' => true,
                'message' => 'username updated successfully',
                'data' => $teacher
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function edit_phone_number(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateTeacher = Validator::make($request->all(), [
                'phone_number' => 'nullable|string|max:255',
            ]);

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateTeacher->errors()
                ], 422);
            }

            if ($request->has('phone_number')) {
                $teacher->phone_number = $request->phone_number;
            }

            $teacher->save();

            return response()->json([
                'status' => true,
                'message' => 'phone number updated successfully',
                'data' => $teacher
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
            'data' => [],
        ], 200);
    }

    protected function handleRecordCreation(array $data): Teacher
    {
        $username = str()->random(8);
        $password = str()->random(8);

        $data['username'] = $username;
        $data['password'] = Hash::make($password);
        $data['role_id'] = 2;

        $teacher = Teacher::create($data);

        Mail::to($data['email'])->send(new sendTeacherEmail($username, $password));

        return $teacher;
    }

    public function importExcelData(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file'
            ],
        ]);

        $importedData = Excel::toArray(new TeacherImport, $request->file('import_file'));

        foreach ($importedData[0] as $row) {
            $data = [
                'fullname' => $row[0],
                'nickname' => $row[1],
                'birth_date' => $row[2] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d'),
                'teacher_number' => $row[3],
                'gender_id' => $row[4],
                'phone_number' => $row[5],
                'email' => $row[6],
            ];
            $this->handleRecordCreation($data);
        }
        return redirect()->back()->with('Success', 'Import Success');
    }

    public function deleteAccount(Request $request)
    {
        try {
            $teacher = auth()->user();

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Teacher not authenticated'
                ], 401);
            }

            $teacher->delete();
            auth()->guard('teacher')->logout();

            return response()->json([
                'status' => true,
                'message' => 'Teacher account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
