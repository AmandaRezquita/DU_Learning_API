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
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:teachers,email',
                    'image' => 'nullable|string',
                ]
            );

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTeacher->errors()
                ], 401);
            }

            $data = [
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'image' => $request->image,
            ];

            $teacher = $this->handleRecordCreation($data);

            $token = $teacher->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $teacher->name;
            $success['phone_number'] = $teacher->phone_number;
            $success['email'] = $teacher->email;
            $success['image'] = $teacher->image;


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

    public function login(Request $request)
    {
        try {
            $validateTeacher = Validator::make(
                $request->all(),
                [
                    'username' => 'required',
                    'password' => 'required'
                ]
            );

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateTeacher->errors()
                ], 401);
            }

            if (!Auth::guard('teacher')->attempt($request->only(['username', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username atau Password yang dimasukan salah',
                ], 401);
            }

            $teacher = Teacher::where('username', $request->username)->first();

            $token = $teacher->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $teacher->name;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'token' => $token,
                "data" => $success
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
        $teacherData = auth()->guard('')->user();
        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $teacherData,
        ], 200);
    }

    public function updateProfile(Request $request)
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
                'email' => 'nullable|email|unique:students,email,' . $teacher->id,
                'password' => 'nullable|string|min:8',
                'phone_number' => 'nullable|string|max:255',
            ]);

            if ($validateTeacher->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateTeacher->errors()
                ], 401);
            }

            if ($request->has('email')) {
                $teacher->email = $request->email;
            }
            if ($request->has('password')) {
                $teacher->password = Hash::make($request->password);
            }
            if ($request->has('phone_number')) {
                $teacher->phone_number = $request->phone_number;
            }

            $teacher->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
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
        auth()->guard('')->user()->tokens()->delete();
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
                'name' => $row[0],
                'phone_number' => $row[1],
                'email' => $row[2],
                'teacher_avatar_id' => $row[3]
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
                    'message' => 'Student not authenticated'
                ], 401);
            }

            $teacher->delete();
            auth()->guard('teacher')->logout();

            return response()->json([
                'status' => true,
                'message' => 'Student account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
