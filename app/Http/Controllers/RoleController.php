<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function GetRoles(){
        try {
            $roleList = Role::all();

            return response()->json([
                'status' => true,
                'message' => 'List retrieved successfully',
                'data' => $roleList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
