<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Auth\Teacher;
use App\Models\Teacher\Dashboard\AddMaterials;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentMaterialController extends Controller
{
    public function getStudentClassToday()
    {
        $student_id = auth()->user();
        $todayDayId = Carbon::now()->dayOfWeekIso;

        $materialList = AddMaterials::whereHas('studentClass', function ($query) use ($student_id) {
            $query->where('class_id', $student_id);
        })
            ->where('day_id', $todayDayId)
            ->with(['subject'])
            ->get();

        $response = $materialList->map(function ($material) {

            $name = Teacher::find($material->subject->teacher_id);

            return [
                'id' => $material->id,
                'subject_name' => $subject->subject->subject_name ?? null,
                'teacher_name' => $name->fullname ?? null,

            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched class for today',
            'data' => $response,
        ], 200);
    }
}
