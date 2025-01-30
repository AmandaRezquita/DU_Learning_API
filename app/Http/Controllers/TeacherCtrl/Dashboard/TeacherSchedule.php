<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherSchedule extends Controller
{

    public function getTeacherClassToday()
    {
        $teacher_id = auth()->id();
        $todayDayId = Carbon::now()->dayOfWeekIso;

        $classList = Schedule::whereHas('subject', function ($query) use ($teacher_id) {
            $query->where('teacher_id', $teacher_id);
        })
            ->where('day_id', $todayDayId)
            ->with(['class', 'subject'])
            ->get();

        $response = $classList->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'class_name' => $schedule->class->class_name ?? null,
                'subject_name' => $schedule->subject->subject_name ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched class for today',
            'data' => $response,
        ], 200);
    }

    public function getTeacherClass()
    {
        $teacher_id = auth()->id();

        $classList = ClassSubject::where('teacher_id', $teacher_id)
            ->with('class')
            ->get();

        $response = $classList->map(function ($class) {
            return [
                'id' => $class->id,
                'class_name' => $class->class->class_name ?? null,
                'subject_name' => $class->subject_name,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched class',
            'data' => $response,
        ], 200);
    }

    public function getTeacherScheduleToday()
    {
        $teacher_id = auth()->id();
        $todayDayId = Carbon::now()->dayOfWeekIso;

        $classList = Schedule::whereHas('subject', function ($query) use ($teacher_id) {
            $query->where('teacher_id', $teacher_id);
        })
            ->where('day_id', $todayDayId)
            ->with(['class', 'subject'])
            ->get();

        $response = $classList->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'class_name' => $schedule->class->class_name ?? null,
                'subject_name' => $schedule->subject->subject_name ?? null,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
        })->sortBy('start_time')->values(); 

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched class for today',
            'data' => $response,
        ], 200);
    }


    public function getTeacherSchedule()
    {
        $teacher_id = auth()->id();
        $schedules = Schedule::whereHas('subject', function ($query) use ($teacher_id) {
            $query->where('teacher_id', $teacher_id);
        })
            ->with(['class', 'subject.teacher', 'day'])
            ->get();

        $daysOfWeek = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            7 => 'minggu'
        ];

        $groupedSchedules = [];
        foreach ($schedules as $schedule) {
            $dayName = $schedule->day->day ?? 'Unknown Day';
            $groupedSchedules[$dayName][] = [
                'id' => $schedule->id,
                'subject' => $schedule->subject->subject_name ?? 'Unknown Subject',
                'teacher' => $schedule->subject->teacher->fullname ?? 'Teacher not assigned',
                'class_name' => $schedule->class->class_name ?? 'Unknown Class',
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
        }

        foreach ($groupedSchedules as $day => &$subjects) {
            usort($subjects, function ($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
        }

        $response = [];
        foreach ($daysOfWeek as $dayId => $dayName) {
            $response[] = [
                'day' => $dayName,
                'subjects' => $groupedSchedules[$dayName] ?? [],
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched schedule',
            'data' => $response,
        ], 200);
    }

}
