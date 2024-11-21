<?php

namespace App\Http\Controllers\StudentCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\StudentAvatar;
use Illuminate\Http\Request;

class StudentAvatarController extends Controller
{
    public function getAvatars()
    {
        try {
            $avatars = StudentAvatar::all();

            return response()->json([
                'status' => true,
                'message' => 'Avatars retrieved successfully',
                'data' => $avatars
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
