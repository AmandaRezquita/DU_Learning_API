<?php

namespace App\Http\Controllers\TeacherCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Imports\TeacherImport;
use App\Mail\sendTeacherEmail;
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
            $teacherList = Teacher::all();

            return response()->json([
                'status' => true,
                'message' => 'Avatars retrieved successfully',
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
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:teachers,email',
                    'image' => 'nullable|string',
                    'teacher_avatar_id' => 'nullable|integer',
                    'role_id' => 'required|integer',
                ]
            );

            if (($request->hasFile('image') && $request->teacher_avatar_id) || (!$request->hasFile('image') && !$request->teacher_avatar_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You must provide either an image or an avatar, but not both.',
                ], 422);
            }

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTeacher->errors()
                ], 422);
            }

            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image));
            }

            $data = [
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'birth_date' => $request->birth_date,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'image' => $request->image,
                'teacher_avatar_id' => $request->teacher_avatar_id,
                'role_id' => $request->role_id,
            ];

            $teacher = $this->handleRecordCreation($data);

            $token = $teacher->createToken("API TOKEN")->plainTextToken;

            $success['fullname'] = $teacher->fullname;
            $success['nickname'] = $teacher->nickname;
            $success['birth_date'] = $teacher->birth_date;
            $success['phone_number'] = $teacher->phone_number;
            $success['email'] = $teacher->email;
            $success['image'] = $teacher->image;
            $success['teacher_avatar_id'] = $teacher->teacher_avatar_id;
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
        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $teacherData,
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
                'birth_date' => $row[2]= \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d'),
                'phone_number' => $row[3],
                'email' => $row[4],
                'teacher_avatar_id' => $row[5],
                'role_id' => $row[6],
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
