<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validateTeacher = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:teachers,email',
                    'password' => 'required|string|max:255',
                    'confirm_password' => 'required|same:password',
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

            $teacher = Teacher::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'confirm_password' => $request->confirm_password,
                'image' => $request->image,
            ]);

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
                    'email' => 'required|email',
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

            if (!Auth::guard('teacher')->attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau Password yang dimasukan salah',
                ], 401);
            }

            $teacher = Teacher::where('email', $request->email)->first();
            
            $token = $teacher->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $teacher->name;

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'token' => $token,
                "data"=> $success
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function profile(){
       $teacherData = auth()->guard('')->user();
       return response()->json([
        'status' => true,
        'message' => 'Profile Information',
        'data' => $teacherData,
    ], 200);
    }

    public function logout(){
        auth()->guard('')->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
            'data' => [],
        ], 200);
    }
}
