<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
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

        $classList = Schedule::whereHas('studentClass', function ($query) use ($student_id) {
            $query->where('class_id', $student_id);
        })
            ->where('day_id', $todayDayId)
            ->with(['subject', 'class'])
            ->get();

        $response = $classList->map(function ($schedule) {

            $name = Teacher::find($schedule->subject->teacher_id);

            return [
                'id' => $schedule->id,
                'subject_name' => $schedule->subject->subject_name ?? null,
                'start_time' => $schedule->start_time,
                'teacher_name' => $name->fullname ?? null,

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
        $schedules = Schedule::whereHas('subject', function ($query) use ($student_id) {
            $query->where('class_id', $student_id);
        })
            ->with(['class', 'subject', 'day'])
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
                'teacher_name' => $schedule->subject->teacher->fullname ?? 'Teacher not assigned',
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
