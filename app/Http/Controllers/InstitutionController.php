<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class InstitutionController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validateInstitution = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:institutions,email',
                    'password' => 'required|string|max:255',
                    'confirm_password' => 'required|same:password',
                    'image' => 'nullable|string',
                ]
            );

            if ($validateInstitution->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateInstitution->errors()
                ], 401);
            }

            $institution = Institution::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'confirm_password' => $request->confirm_password,
                'image' => $request->image,
            ]);

            $token = $institution->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $institution->name;
            $success['phone_number'] = $institution->phone_number;
            $success['email'] = $institution->email;
            $success['image'] = $institution->image;


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
            $validateInstitution = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateInstitution->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateInstitution->errors()
                ], 401);
            }

            if (!Auth::guard('institution')->attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau Password yang dimasukan salah',
                ], 401);
            }

            $institution = Institution::where('email', $request->email)->first();
            
            $token = $institution->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $institution->name;

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
       $institutionData = auth()->guard('')->user();
       return response()->json([
        'status' => true,
        'message' => 'Profile Information',
        'data' => $institutionData,
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
