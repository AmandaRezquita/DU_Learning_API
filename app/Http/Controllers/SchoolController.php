<?php

namespace App\Http\Controllers;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'username' => 'required',
                    'password' => 'required'
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            if (!Auth::guard('school')->attempt($request->only(['username', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username atau Password yang dimasukan salah',
                ], 401);
            }

            $school = School::where('username', $request->username)->first();

            $token = $school->createToken("API TOKEN")->plainTextToken;

            $success['name'] = $school->name;
            $success['logo'] = $school->logo;


            return response()->json([
                'status' => true,
                'message' => 'Logged in successfully',
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
}
