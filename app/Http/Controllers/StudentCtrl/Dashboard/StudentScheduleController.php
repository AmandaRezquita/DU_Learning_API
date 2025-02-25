<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Teacher\Auth\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \App\Models\Superadmin\Dashboard\Schedule;

class StudentScheduleController extends Controller
{
    public function getStudentClassToday()
    {
        $student_id = auth()->id();
        $todayDayId = Carbon::now()->dayOfWeekIso;

        $studentClasses = StudentClass::where('student_id', $student_id)->pluck('class_id');

        $classList = Schedule::whereIn('class_id', $studentClasses)
            ->where('day_id', $todayDayId)
            ->with(['subject.subject', 'class'])
            ->get();

        $response = $classList->map(function ($schedule) {
            $teacher = $schedule->subject->teacher ?? null;

            return [
                'id' => $schedule->id,
                'subject_name' => $schedule->subject->subject->subject_name ?? null,
                'start_time' => $schedule->start_time ?? null,
                'end_time' => $schedule->end_time ?? null,
                'teacher_name' => $teacher?->fullname ?? null,

            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched class for today',
            'data' => $response,
        ], 200);
    }

    public function getStudentSchedule()
    {
        $student_id = auth()->id();
        $studentClasses = StudentClass::where('student_id', $student_id)->pluck('class_id');

        $schedules = Schedule::whereIn('class_id', $studentClasses)
            ->with(['class', 'subject.teacher', 'day', 'subject.subject'])
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
            $dayName = strtolower($schedule->day->day ?? 'Unknown Day');
            $groupedSchedules[$dayName][] = [
                'id' => $schedule->id,
                'subject' => $schedule->subject->subject->subject_name ?? 'Unknown Subject',
                'teacher_name' => $schedule->subject->teacher->fullname ?? 'Teacher not assigned',
                'class_name' => $schedule->class->class_name ?? 'Unknown Class',
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
        }

        foreach ($groupedSchedules as &$subjects) {
            usort($subjects, function ($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
        }

        $response = array_map(function ($dayName) use ($groupedSchedules) {
            return [
                'day' => $dayName,
                'subjects' => $groupedSchedules[$dayName] ?? [],
            ];
        }, $daysOfWeek);

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched schedule',
            'data' => $response,
        ], 200);
    }

}
