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
                'teacher_number' => $teacher->teacher_number,
                'image' => $image ? $image->image : null,
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
        $firstName = explode(' ', $data['fullname'])[0];

        $lastTwoDigits = substr($data['teacher_number'], -2);

        $username = $firstName . $lastTwoDigits;

        $password = Str::random(8);

        $data['username'] = $username;
        $data['password'] = Hash::make($password);
        $data['role_id'] = 2;

         if (!isset($data['teacher_image_id']) || $data['teacher_image_id'] === null) {
            $data['teacher_image_id'] = $data['gender_id'] == 1 ? 2 : ($data['gender_id'] == 2 ? 1 : null);
        }

        $teacher = Teacher::create($data);

        Mail::to($data['email'])->send(new sendTeacherEmail($username, $password));

        return $teacher;
    }

    public function importExcelData(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls'
            ],
        ]);
    
        $importedData = Excel::toArray(new TeacherImport, $request->file('import_file'));
        $rows = $importedData[0];
    
        $header = array_map('strtolower', $rows[0]);
        $dataRows = array_slice($rows, 1);
    
        foreach ($dataRows as $row) {
            try {
                $isRowValid = true;
                $data = [
                    'fullname' => $row[array_search('fullname', $header)] ?? null,
                    'nickname' => $row[array_search('nickname', $header)] ?? null,
                    'birth_date' => isset($row[array_search('birth_date', $header)])
                        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[array_search('birth_date', $header)])->format('Y-m-d')
                        : null,
                    'teacher_number' => $row[array_search('teacher_number', $header)] ?? null,
                    'gender_id' => $row[array_search('gender_id', $header)] ?? null,
                    'phone_number' => $row[array_search('phone_number', $header)] ?? null,
                    'email' => $row[array_search('email', $header)] ?? null,
                ];
    
                foreach ($data as $value) {
                    if (is_null($value)) {
                        $isRowValid = false;
                        break;
                    }
                }
    
                if ($isRowValid) {
                    $this->handleRecordCreation($data);
                }
            } catch (\Exception $th) {
                return response()->json([
                    'status' => false,
                    'errors' => $th->getMessage()
                ], 500);
            }
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Import successfully',
        ], 200);
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
