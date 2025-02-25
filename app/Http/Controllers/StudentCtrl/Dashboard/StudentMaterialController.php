<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\StudentClass;
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

        $studentClasses = StudentClass::where('student_id', $student_id)->pluck('class_id');

        $subjectList = Schedule::whereIn('class_id', $studentClasses)
            ->where('day_id', $todayDayId)
            ->with(['subject.subject', 'class'])
            ->get();

        $response = $subjectList->map(function ($schedule) {
            $teacher = $schedule->subject->teacher ?? null;

            return [
                'id' => $schedule->id,
                'subject_id' => $schedule->subject->id ?? null,
                'subject_name' => $schedule->subject->subject->subject_name ?? null,
                'teacher_name' => $teacher?->fullname ?? null,
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

        $studentClasses = StudentClass::where('student_id', $student_id)->pluck('class_id');

        if ($studentClasses->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No classes found for this student.',
                'data' => [],
            ], 200);
        }

        $subjectList = ClassSubject::whereIn('class_id', $studentClasses)
            ->with(['class', 'teacher', 'subject'])
            ->get();

        if ($subjectList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No subjects found for this student in their enrolled classes.',
                'data' => [],
            ], 200);
        }

        $response = $subjectList->map(function ($schedule) {
            $teacher = $schedule->teacher;

            return [
                'subject_id' => $schedule->id ?? null,
                'subject_name' => $schedule->subject->subject_name ?? null,
                'teacher_name' => $teacher ? $teacher->fullname : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched subjects for the student',
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
                'materials' => $sortedMats->map(function ($material) {

                    $teacher = Teacher::find($material->subject->teacher_id);

                    return [
                        'material_id' => $material->id,
                        'title' => $material->title,
                        'time' => Carbon::parse($material->date)->translatedFormat('H:i'),
                        'teacher_name' => $teacher->nickname ?? null,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched materials',
            'data' => $response,
        ], 200);
    }

    public function getMaterialById($id)
    {
        $material = AddMaterials::find($id);

        if (!$material) {
            return response()->json([
                'status' => false,
                'message' => 'Material not found',
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Material retrieved successfully',
            'data' => [
                'id' => $material->id,
                'class_id' => $material->class_id,
                'subject_id' => $material->subject_id,
                'title' => $material->title,
                'description' => $material->description,
                'date' => $material->date ? Carbon::parse($material->date)->translatedFormat('d F Y H:i') : null,
                'file' => $material->file ? "https://docs.google.com/gview?url=" . asset('storage/' . $material->file) . "&embedded=true" : null,
                'link' => $material->link,
            ]
        ], 200);
    }

}
