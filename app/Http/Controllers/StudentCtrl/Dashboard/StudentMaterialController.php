<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Teacher\Auth\Teacher;
use App\Models\Teacher\Dashboard\AddMaterials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Superadmin\Dashboard\Schedule;

class StudentMaterialController extends Controller
{
    public function getStudentSubjectToday()
    {
        $student_id = auth()->id();
        $todayDayId = Carbon::now()->dayOfWeekIso;

        $subjectList = Schedule::whereHas('studentClass', function ($query) use ($student_id) {
            $query->where('class_id', $student_id);
        })
            ->where('day_id', $todayDayId)
            ->with(['subject', 'class'])
            ->get();

        $response = $subjectList->map(function ($schedule) {

            $name = Teacher::find($schedule->subject->teacher_id);

            return [
                'id' => $schedule->id,
                'subject_id' => $schedule->subject->id ?? null,
                'subject_name' => $schedule->subject->subject_name ?? null,
                'teacher_name' => $name->fullname ?? null,

            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched subject for today',
            'data' => $response,
        ], 200);
    }

    public function getStudentSubject()
    {
        $student_id = auth()->id();

        $subjectList = ClassSubject::whereHas('studentClass', function ($query) use ($student_id) {
            $query->where('class_id', $student_id);
        })
            ->with([ 'class'])
            ->get();

        $response = $subjectList->map(function ($schedule) {

            $name = Teacher::find($schedule->teacher_id);

            return [
                'id' => $schedule->id,
                'subject_id' => $schedule->id ?? null,
                'subject_name' => $schedule->subject_name ?? null,
                'teacher_name' => $name->fullname ?? null,

            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched subject',
            'data' => $response,
        ], 200);
    }

    public function getMaterialList($subject_id)
    {
        $materialList = AddMaterials::where('subject_id', $subject_id)
            ->orderBy('date', 'desc')
            ->with('subject')
            ->get();

        if ($materialList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No materials found',
                'data' => [],
            ], 200);
        }

        $groupedMat = $materialList->groupBy(function ($material) {
            return Carbon::parse($material->date)->translatedFormat('d F Y');
        });

        $groupedMats = $groupedMat->sortByDesc(function ($materials, $date) {
            return Carbon::createFromFormat('d F Y', $date);
        });

        $response = $groupedMats->map(function ($materials, $date) {
            $sortedMats = $materials->sortByDesc(function ($material) {
                return Carbon::parse($material->date);
            });

            return [
                'date' => $date,
                'tasks' => $sortedMats->map(function ($material) {

                    $teacher = Teacher::find($material->subject->teacher_id);

                    return [
                        'material_id' => $material->id,
                        'title' => $material->title,
                        'time' => Carbon::parse($material->date)->translatedFormat('H:i'),
                        'teacher_name' =>  $teacher->nickname ?? null,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched tasks',
            'data' => $response,
        ], 200);
    }
}
