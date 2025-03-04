<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Principal\Auth\Principal;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\Subject;
use App\Models\Superadmin\Dashboard\TimeSchedule;
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
            $mapel = Subject::count();
            $time = TimeSchedule::count();

            $data['guru'] = $totalTeacher;
            $data['murid'] = $totalStudent;
            $data['kelas'] = $class;
            $data['mapel'] = $mapel;
            $data['kepala_sekolah'] = $principal;
            $data['time'] = $time;

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
    