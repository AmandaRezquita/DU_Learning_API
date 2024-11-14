<?php

namespace App\Http\Controllers\TeacherCtrl\Auth;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Auth\TeacherAvatar;
use Illuminate\Http\Request;

class TeacherAvatarController extends Controller
{
    public function getAvatars()
    {
        try {
            $avatars = TeacherAvatar::all();

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
