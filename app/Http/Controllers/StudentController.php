<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Storage;

class StudentController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validateStudent = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:students,email',
                    'password' => 'required|string|max:255',
                    'confirm_password' => 'required|same:password',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]
            );

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateStudent->errors()
                ], 401);
            }
 
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image));
            }

            $student = Student::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'confirm_password' => $request->confirm_password,
                'image' => $imageName,
            ]);

            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            $token = $student->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $student->name;
            $success['phone_number'] = $student->phone_number;
            $success['email'] = $student->email;
            $success['image'] = $student->image;


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

    public function login(Request $request)
    {
        try {
            $validateStudent = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateStudent->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateStudent->errors()
                ], 401);
            }

            if (!Auth::guard('student')->attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau Password yang dimasukan salah',
                ], 401);
            }

            $student = Student::where('email', $request->email)->first();
            
            $token = $student->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $student->name;

            return response()->json([
                'status' => true,
                'message' => 'Student logged in successfully',
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
       $studentData = auth()->guard('')->user();
       return response()->json([
        'status' => true,
        'message' => 'Profile Information',
        'data' => $studentData,
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
