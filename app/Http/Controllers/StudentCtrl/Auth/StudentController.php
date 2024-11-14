<?php

namespace App\Http\Controllers\StudentCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use App\Mail\sendStudentEmail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\Student\Auth\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;
use Storage;

class StudentController extends Controller
{
    public function StudentList()
    {
        try {
            $studentList = Student::all();

            return response()->json([
                'status' => true,
                'message' => 'List retrieved successfully',
                'data' => $studentList
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
            $validateStudent = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:students,email',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'student_avatar_id' => 'nullable|integer',
                    'role_id' => 'required|integer'
                ]
            );

            if (($request->hasFile('image') && $request->student_avatar_id) || (!$request->hasFile('image') && !$request->student_avatar_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You must provide either an image or an avatar, but not both.',
                ], 401);
            }

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 401);
            }

            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image));
            }

            $data = [
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'image' => $imageName,
                'student_avatar_id' => $request->student_avatar_id,
                'role_id' => $request->role_id,
            ];

            $student = $this->handleRecordCreation($data);

            $token = $student->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $student->name;
            $success['phone_number'] = $student->phone_number;
            $success['email'] = $student->email;
            $success['image'] = $student->image;
            $success['student_avatar_id'] = $student->student_avatar_id;
            $success['role_id'] = $student->role_id;

            return response()->json([
                'status' => true,
                'message' => 'Student created successfully',
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
        $studentData = auth()->user();
        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $studentData,
        ], 200);
    }

    
    public function updateProfile(Request $request)
    {
        try {
            $student = auth()->user();

            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateStudent = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:students,email,' . $student->id,
                'password' => 'nullable|string|min:8',
                'phone_number' => 'nullable|string|max:255',
            ]);

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 401);
            }

            if ($request->has('email')) {
                $student->email = $request->email;
            }
            if ($request->has('password')) {
                $student->password = Hash::make($request->password);
            }
            if ($request->has('phone_number')) {
                $student->phone_number = $request->phone_number;
            }

            $student->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => $student
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

    protected function handleRecordCreation(array $data): Student
    {
        $username = str()->random(8);
        $password = str()->random(8);

        $data['username'] = $username;
        $data['password'] = Hash::make($password);

        $student = Student::create($data);

        Mail::to($data['email'])->send(new sendStudentEmail($username, $password));

        return $student;
    }
    

    public function importExcelData(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file'
            ],
        ]);

        $importedData = Excel::toArray(new StudentImport, $request->file('import_file'));

        foreach ($importedData[0] as $row) {
            $data = [
                'name' => $row[0],
                'phone_number' => $row[1],
                'email' => $row[2],
                'student_avatar_id' => $row[3],
            ];
            $this->handleRecordCreation($data);
        }

        return redirect()->back()->with('Success', 'Import Success');
    }

    public function deleteAccount(Request $request)
    {
        try {
            $student = auth()->user();

            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student not authenticated'
                ], 401);
            }

            $student->delete();
            auth()->guard('student')->logout();

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
