<?php

namespace App\Http\Controllers\SuperadminCtrl\Auth;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Superadmin\Auth\School;
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
            $success['phone'] = $school->phone;
            $success['address'] = $school->address;
            $success['jenjang'] = $school->jenjang;
            $success['principal_name'] = $school->principal_name;
            $success['role_id'] = $school->role_id;
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

    public function profile(Request $request)
    {
        $school = School::where('username', $request->username)->first();

        $school = auth()->user();

        $role = Role::find($school->role_id);

        $success['name'] = $school->name;
        $success['phone'] = $school->phone;
        $success['address'] = $school->address;
        $success['jenjang'] = $school->jenjang;
        $success['principal_name'] = $school->principal_name;
        $success['role'] = $role ? $role->role_name : null;
        $success['logo'] = $school->logo;
     

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $success,
        ], 200);
    }
}
