<?php

namespace App\Http\Controllers\StudentCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use App\Mail\sendStudentEmail;
use App\Models\Student\Auth\StudentGender;
use App\Models\Student\Auth\StudentImage;
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
            $studentList = Student::all()->map(function ($student) {

                $gender = StudentGender::find($student->gender_id);

                $image = StudentImage::find($student->student_image_id);

                return [
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'nickname' => $student->nickname,
                    'birth_date' => $student->birth_date,
                    'student_number' => $student->student_number,
                    'gender' => $gender ? $gender->name : null,
                    'phone_number' => $student->phone_number,
                    'username' => $student->username,
                    'email' => $student->email,
                    'image' => $image ? $image->image : null,
                    'role_id' => $student->role_id,
                ];
            });
    
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
                    'fullname' => 'required|string|max:255',
                    'nickname' => 'required|string|max:255',
                    'birth_date' => 'required|string|max:255',
                    'student_number' => 'required|string|max:255',
                    'gender_id' => 'required|integer',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:students,email',
                ]
            );

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 422);
            }

            $studentImageId = $request->gender_id == 1 ? 1 : ($request->gender_id == 2 ? 2 : null);

            $data = [
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'birth_date' => $request->birth_date,
                'student_number' => $request->student_number,
                'gender_id' => $request->gender_id,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'student_image_id' => $studentImageId,
                'role_id' => 1,
            ];


            $student = $this->handleRecordCreation($data);

            $gender = StudentGender::find($student->gender_id);

            $image = StudentImage::find($student->student_image_id);


            $token = $student->createToken("API TOKEN")->plainTextToken;

            $success['fullname'] = $student->fullname;
            $success['nickname'] = $student->nickname;
            $success['birth_date'] = $student->birth_date;
            $success['student_number'] = $student->student_number;
            $success['gender'] = $gender ? $gender->name : null;
            $success['phone_number'] = $student->phone_number;
            $success['email'] = $student->email;
            $success['image'] = $image ? $image->image : null;
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

        $gender = StudentGender::find($studentData->gender_id);

        $image = StudentImage::find($studentData->student_image_id);

        $success['fullname'] = $studentData->fullname;
        $success['nickname'] = $studentData->nickname;
        $success['username'] = $studentData->nickname;
        $success['birth_date'] = $studentData->birth_date;
        $success['student_number'] = $studentData->student_number;
        $success['gender'] = $gender ? $gender->name : null;
        $success['phone_number'] = $studentData->phone_number;
        $success['email'] = $studentData->email;
        $success['image'] = $image ? $image->image : null;
        $success['role_id'] = $studentData->role_id;

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $success,
        ], 200);
    }


    public function edit_email(Request $request)
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
            ]);

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 422);
            }

            if ($request->has('email') && $request->email === $student->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'The new email cannot be the same as the current email.'
                ], 422);
            }

            if ($request->has('email')) {
                $student->email = $request->email;
            }

            $student->save();

            return response()->json([
                'status' => true,
                'message' => 'email updated successfully',
                'data' => $student
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
            $student = auth()->user();

            if (!$student) {
                return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
            }

            $validateStudent = Validator::make($request->all(), [
                'current_password' => 'required|string|min:8',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validateStudent->fails()) {
                return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validateStudent->errors()], 422);
            }

            if (!Hash::check($request->current_password, $student->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 401);
            }

            $student->password = Hash::make($request->password);
            $student->save();

            return response()->json(['status' => true, 'message' => 'Password updated successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => $th->getMessage()], 500);
        }
    }


    public function edit_username(Request $request)
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
                'username' => 'nullable|string|max:255',
            ]);

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 422);
            }

            if ($request->has('username')) {
                $student->username = $request->username;
            }

            $student->save();

            return response()->json([
                'status' => true,
                'message' => 'username updated successfully',
                'data' => $student
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
            $student = auth()->user();

            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateStudent = Validator::make($request->all(), [
                'phone_number' => 'nullable|string|max:255',
            ]);

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateStudent->errors()
                ], 422);
            }

            if ($request->has('phone_number')) {
                $student->phone_number = $request->phone_number;
            }

            $student->save();

            return response()->json([
                'status' => true,
                'message' => 'phone number updated successfully',
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
        ], 200);
    }

    protected function handleRecordCreation(array $data): Student
    {
        $firstName = explode(' ', $data['fullname'])[0];

        $lastTwoDigits = substr($data['student_number'], -2);

        $username = $firstName . $lastTwoDigits;

        $password = Str::random(8);

        $data['username'] = $username;
        $data['password'] = Hash::make($password);
        $data['role_id'] = 1;

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
                'fullname' => $row[0],
                'nickname' => $row[1],
                'birth_date' => $row[2] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d'),
                'student_number' => $row[3],
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
