<?php

namespace App\Http\Controllers;

use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Auth\School;
use App\Models\Teacher\Auth\Teacher;
use Auth;
use Illuminate\Http\Request;
use Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validatePrincipal = Validator::make(
                $request->all(),
                [
                    'username' => 'required',
                    'password' => 'required'
                ]
            );

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validatePrincipal->errors()
                ], 401);
            }

            if (Auth::guard('principal')->attempt($request->only(['username', 'password']))) {
                $principal = Principal::with('role')->where('username', $request->username)->first();

                $token = $principal->createToken("API TOKEN")->plainTextToken;

                $success = $principal;

                return response()->json([
                    'status' => true,
                    'message' => 'Principal logged in successfully',
                    'token' => $token,
                    "data" => $success
                ], 200);
            }else if(Auth::guard('teacher')->attempt($request->only(['username', 'password']))){
                $teacher = Teacher::with('role')->where('username', $request->username)->first();

                $token = $teacher->createToken("API TOKEN")->plainTextToken;

                $success= $teacher;

                return response()->json([
                    'status' => true,
                    'message' => 'Teacher logged in successfully',
                    'token' => $token,
                    "data" => $success
                ], 200);
            }else if(Auth::guard('student')->attempt($request->only(['username', 'password']))){
                $student = Student::with('role')->where('username', $request->username)->first();

                $token = $student->createToken("API TOKEN")->plainTextToken;

                $success = $student;

                return response()->json([
                    'status' => true,
                    'message' => 'Student logged in successfully',
                    'token' => $token,
                    "data" => $success
                ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Username atau Password yang dimasukan salah',
                ], 401);
            }

            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

}
