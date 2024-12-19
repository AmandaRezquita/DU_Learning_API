<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\sendPrincipalEmail;
use App\Mail\sendStudentEmail;
use App\Mail\sendTeacherEmail;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Teacher\Auth\Teacher;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Str;
use Validator;

class EditController extends Controller
{
    public function editStudent(Request $request, $id)
    {
        try {
            $user = Student::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validateUser = Validator::make($request->all(), [
                'fullname' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'birth_date' => 'nullable|string|max:255',
                'student_number' => 'nullable|string|max:255',
                'gender_id' => 'nullable|integer',
                'phone_number' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:students,email,' . $user->id,
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            $isEmailChanged = false;
            if ($request->has('email') && $request->email !== $user->email && $request->email !== null) {
                $user->email = $request->email;
                $isEmailChanged = true;
            }

            if ($request->has('fullname') && $request->fullname !== null) {
                $user->fullname = $request->fullname;
            }
            if ($request->has('nickname') && $request->nickname !== null) {
                $user->nickname = $request->nickname;
            }
            if ($request->has('birth_date') && $request->birht_date !== null) {
                $user->birth_date = $request->birth_date;
            }
            if ($request->has('student_number') && $request->student_number !== null) {
                $user->student_number = $request->student_number;
            }
            if ($request->has('gender_id') && $request->gender_id !== null) {
                $user->gender_id = $request->gender_id;
            }
            if ($request->has('phone_number') && $request->phone_number !== null) {
                $user->phone_number = $request->phone_number;
            }

            $user->save();

            if ($isEmailChanged) {
                $firstName = explode(' ', $user->fullname)[0];
                $lastTwoDigits = substr($user->student_number, -2);
                $username = $firstName . $lastTwoDigits;

                $password = Str::random(8);

                $user->username = $username;
                $user->password = Hash::make($password);
                $user->save();

                Mail::to($user->email)->send(new sendStudentEmail($username, $password));
            }

            return response()->json([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function editTeacher(Request $request, $id)
    {
        try {
            $user = Teacher::find($id);
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
    
            $validateUser = Validator::make($request->all(), [
                'fullname' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'birth_date' => 'nullable|string|max:255',
                'teacher_number' => 'nullable|string|max:255',
                'gender_id' => 'nullable|integer',
                'phone_number' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:teachers,email,' . $user->id,
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
    
            $isEmailChanged = false;
            if ($request->has('email') && $request->email !== $user->email  && $request->email !== null ) {
                $user->email = $request->email;
                $isEmailChanged = true;
            }
    
            if ($request->has('fullname') && $request->fullname !== null ) {
                $user->fullname = $request->fullname;
            }
            if ($request->has('nickname') && $request->nickname !== null) {
                $user->nickname = $request->nickname;
            }
            if ($request->has('birth_date') && $request->birht_date !== null) {
                $user->birth_date = $request->birth_date;
            }
            if ($request->has('teacher_number') && $request->teacher_number !== null) {
                $user->teacher_number = $request->teacher_number;
            }
            if ($request->has('gender_id') && $request->gender_id !== null) {
                $user->gender_id = $request->gender_id;
            }
            if ($request->has('phone_number') && $request->phone_number !== null) {
                $user->phone_number = $request->phone_number;
            }
    
            $user->save();
    
            if ($isEmailChanged) {
                $firstName = explode(' ', $user->fullname)[0]; 
                $lastTwoDigits = substr($user->teacher_number, -2); 
                $username = $firstName . $lastTwoDigits;
            
                $password = Str::random(8);
            
                $user->username = $username;
                $user->password = Hash::make($password);
                $user->save();
            
                Mail::to($user->email)->send(new sendTeacherEmail($username, $password));
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function editPrincipal(Request $request, $id)
    {
        try {
            $user = Principal::find($id);
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
    
            $validateUser = Validator::make($request->all(), [
                'fullname' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'birth_date' => 'nullable|string|max:255',
                'principal_number' => 'nullable|string|max:255',
                'gender_id' => 'nullable|integer',
                'phone_number' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:principals,email,' . $user->id,
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
    
            $isEmailChanged = false;
            if ($request->has('email') && $request->email !== $user->email  && $request->email !== null ) {
                $user->email = $request->email;
                $isEmailChanged = true;
            }
    
            if ($request->has('fullname') && $request->fullname !== null ) {
                $user->fullname = $request->fullname;
            }
            if ($request->has('nickname') && $request->nickname !== null) {
                $user->nickname = $request->nickname;
            }
            if ($request->has('birth_date') && $request->birht_date !== null) {
                $user->birth_date = $request->birth_date;
            }
            if ($request->has('principal_number') && $request->principal_number !== null) {
                $user->principal_number = $request->principal_number;
            }
            if ($request->has('gender_id') && $request->gender_id !== null) {
                $user->gender_id = $request->gender_id;
            }
            if ($request->has('phone_number') && $request->phone_number !== null) {
                $user->phone_number = $request->phone_number;
            }
    
            $user->save();
    
            if ($isEmailChanged) {
                $firstName = explode(' ', $user->fullname)[0]; 
                $lastTwoDigits = substr($user->principal_number, -2); 
                $username = $firstName . $lastTwoDigits;
            
                $password = Str::random(8);
            
                $user->username = $username;
                $user->password = Hash::make($password);
                $user->save();
            
                Mail::to($user->email)->send(new sendPrincipalEmail($username, $password));
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Updated successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

}

