<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;

class TotalController extends Controller
{
    public function getTotal(){
        try {
            $totalTeacher = Teacher::count();
            $totalStudent = Student::count();
            $principal = Principal::count();
            $class = SchoolClass::count();

            $data['guru'] = $totalTeacher;
            $data['murid'] = $totalStudent;
            $data['kelas'] = $class;
            $data['kepala_sekolah'] = $principal;

            return response()->json([
                'status' => true,
                'message' => 'Successfully',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
