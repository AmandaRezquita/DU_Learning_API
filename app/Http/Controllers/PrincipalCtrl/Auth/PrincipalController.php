<?php

namespace App\Http\Controllers\PrincipalCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Imports\PrincipalImport;
use App\Mail\sendPrincipalEmail;
use App\Models\Principal\Auth\Principal;
use App\Models\Principal\Auth\Principal_Gender;
use App\Models\Principal\Auth\Principal_Image;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Storage;
use Str;
use Validator;
use Auth;
use Hash;

class PrincipalController extends Controller
{
    public function PrincipalList()
    {
        try {
            $principalList = principal::all()->map(function ($principal) {

                $gender = Principal_Gender::find($principal->gender_id);

                $image = Principal_Image::find($principal->principal_image_id);

                return [
                    'id' => $principal->id,
                    'fullname' => $principal->fullname,
                    'principal_number' => $principal->principal_number,
                    'image' => $image ? $image->image : null,
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'principals retrieved successfully',
                'data' => $principalList
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
            $validatePrincipal = Validator::make(
                $request->all(),
                [
                    'fullname' => 'required|string|max:255',
                    'nickname' => 'required|string|max:255',
                    'birth_date' => 'required|string|max:255',
                    'principal_number' => 'required|string|max:255',
                    'gender_id' => 'required|integer',
                    'phone_number' => 'required|string|max:255',
                    'email' => 'required|email|unique:principals,email',
                ]
            );

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatePrincipal->errors()
                ], 422);
            }

            $principalImageId = $request->gender_id == 1 ? 2 : ($request->gender_id == 2 ? 1 : null);

            $data = [
                'fullname' => $request->fullname,
                'nickname' => $request->nickname,
                'birth_date' => $request->birth_date,
                'principal_number' => $request->principal_number,
                'gender_id' => $request->gender_id,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'principal_image_id' => $principalImageId,
                'role_id' => 3,
            ];

            $principal = $this->handleRecordCreation($data);

            $gender = Principal_Gender::find($principal->gender_id);

            $image = Principal_Image::find($principal->principal_image_id);

            $token = $principal->createToken("API TOKEN")->plainTextToken;

            $success['fullname'] = $principal->fullname;
            $success['nickname'] = $principal->nickname;
            $success['birth_date'] = $principal->birth_date;
            $success['principal_number'] = $principal->principal_number;
            $success['gender'] = $gender ? $gender->name : null;
            $success['phone_number'] = $principal->phone_number;
            $success['email'] = $principal->email;
            $success['image'] = $image ? $image->image : null;
            $success['role_id'] = $principal->role_id;

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

    public function profile()
    {
        $principalData = auth()->user();

        $gender = Principal_Gender::find($principalData->gender_id);

        $image = Principal_Image::find($principalData->principal_image_id);

        $success['fullname'] = $principalData->fullname;
        $success['nickname'] = $principalData->nickname;
        $success['username'] = $principalData->nickname;
        $success['birth_date'] = $principalData->birth_date;
        $success['principal_number'] = $principalData->principal_number;
        $success['gender'] = $gender ? $gender->name : null;
        $success['phone_number'] = $principalData->phone_number;
        $success['email'] = $principalData->email;
        $success['image'] = $image ? $image->image : null;
        $success['role_id'] = $principalData->role_id;

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $success,
        ], 200);
    }

    public function edit_email(Request $request)
    {
        try {
            $principal = auth()->user();

            if (!$principal) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatePrincipal = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:principals,email,' . $principal->id,
            ]);

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatePrincipal->errors()
                ], 422);
            }

            if ($request->has('email') && $request->email === $principal->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'The new email cannot be the same as the current email.'
                ], 422);
            }

            if ($request->has('email')) {
                $principal->email = $request->email;
            }

            $principal->save();

            return response()->json([
                'status' => true,
                'message' => 'email updated successfully',
                'data' => $principal
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
            $principal = auth()->user();

            if (!$principal) {
                return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
            }

            $validatePrincipal = Validator::make($request->all(), [
                'current_password' => 'required|string|min:8',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validatePrincipal->fails()) {
                return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validatePrincipal->errors()], 422);
            }

            if (!Hash::check($request->current_password, $principal->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 401);
            }

            $principal->password = Hash::make($request->password);
            $principal->save();

            return response()->json(['status' => true, 'message' => 'Password updated successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => $th->getMessage()], 500);
        }
    }


    public function edit_username(Request $request)
    {
        try {
            $principal = auth()->user();

            if (!$principal) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatePrincipal = Validator::make($request->all(), [
                'username' => 'nullable|string|max:255',
            ]);

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatePrincipal->errors()
                ], 422);
            }

            if ($request->has('username')) {
                $principal->username = $request->username;
            }

            $principal->save();

            return response()->json([
                'status' => true,
                'message' => 'username updated successfully',
                'data' => $principal
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
            $principal = auth()->user();

            if (!$principal) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatePrincipal = Validator::make($request->all(), [
                'phone_number' => 'nullable|string|max:255',
            ]);

            if ($validatePrincipal->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatePrincipal->errors()
                ], 422);
            }

            if ($request->has('phone_number')) {
                $principal->phone_number = $request->phone_number;
            }

            $principal->save();

            return response()->json([
                'status' => true,
                'message' => 'phone number updated successfully',
                'data' => $principal
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
        auth()->guard('')->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
            'data' => [],
        ], 200);
    }

    protected function handleRecordCreation(array $data): Principal
    {
        $username = str()->random(8);
        $password = str()->random(8);

        $data['username'] = $username;
        $data['password'] = Hash::make($password);

        $principal = Principal::create($data);

        Mail::to($data['email'])->send(new sendPrincipalEmail($username, $password));

        return $principal;
    }

    public function deleteAccount(Request $request)
    {
        try {
            $principal = auth()->user();

            if (!$principal) {
                return response()->json([
                    'status' => false,
                    'message' => 'principal not authenticated'
                ], 401);
            }

            $principal->delete();
            auth()->guard('principal')->logout();

            return response()->json([
                'status' => true,
                'message' => 'principal account deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
