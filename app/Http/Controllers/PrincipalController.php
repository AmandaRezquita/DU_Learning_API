<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Storage;
use Str;
use Validator;

class PrincipalController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatePrincipal = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:students,email',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'principal_avatar_id' => 'nullable|integer',
                ]
            );

            if (($request->hasFile('image') && $request->principal_avatar_id) || (!$request->hasFile('image') && !$request->student_avatar_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You must provide either an image or an avatar, but not both.',
                ], 401);
            }

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatePrincipal->errors()
                ], 401);
            }

            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($request->image));
            }

            $principal = Principal::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'image' => $imageName,
                'principal_avatar_id' => $request->princial_avatar_id,

            ]);

            $token = $principal->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $principal->name;
            $success['phone_number'] = $principal->phone_number;
            $success['email'] = $principal->email;
            $success['image'] = $principal->image;
            $success['student_avatar_id'] = $principal->student_avatar_id;

            return response()->json([
                'status' => true,
                'message' => 'Principal created successfully',
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

            if (!Auth::guard('principal')->attempt($request->only(['username', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username atau Password yang dimasukan salah',
                ], 401);
            }

            $principal = Principal::where('username', $request->username)->first();

            $token = $principal->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $principal->name;

            return response()->json([
                'status' => true,
                'message' => 'Principal logged in successfully',
                'token' => $token,
                "data" => $success
            ], 200);
        } 
        
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function profile()
    {
        $principalData = auth()->guard('')->user();
        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $principalData,
        ], 200);
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
}
