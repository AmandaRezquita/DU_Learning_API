<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\Schedule;
use App\Models\Superadmin\Dashboard\Schedule\Day;
use App\Models\Superadmin\Dashboard\Subject;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Superadmin\Dashboard\TimeSchedule;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class ScheduleController extends Controller
{
    public function addSchedule(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'day_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'start_time' => 'required|integer',
            'end_time' => 'required|integer',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $subject = ClassSubject::find($request->subject_id);
        if (!$subject || !$subject->teacher_id) {
            return response()->json([
                'status' => false,
                'message' => 'Subject or teacher not found',
            ], 200);
        }
        $teacher_id = $subject->teacher_id;

        $existingSchedule = Schedule::where('class_id', $request->class_id)
            ->where('day_id', $request->day_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })->exists();

        if ($existingSchedule) {
            return response()->json([
                'status' => false,
                'message' => 'A schedule already exists for this class at the given time',
            ], 422);
        }

        $teacherSchedule = Schedule::whereHas('subject', function ($query) use ($teacher_id) {
            $query->where('teacher_id', $teacher_id);
        })
            ->where('day_id', $request->day_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })->exists();

        if ($teacherSchedule) {
            return response()->json([
                'status' => false,
                'message' => 'The teacher is already scheduled to teach at this time',
            ], 422);
        }

        $schedule = Schedule::create([
            'class_id' => $request->class_id,
            'day_id' => $request->day_id,
            'subject_id' => $request->subject_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $day = Day::find($schedule->day_id);
        $subjectName = Subject::find($schedule->subject_id);
        $teacherName = $subject ? $subject->teacher->fullname : 'Teacher not found';
        $start_time = TimeSchedule::find($schedule->start_time);
        $end_time = TimeSchedule::find($schedule->end_time);

        $subjectData = [
            'subject' => $subjectName ? $subjectName->subject_name : 'Subject not found',
            'teacher' => $teacherName,
            'start_time' => $start_time->time,
            'end_time' => $end_time->time,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Schedule added successfully',
            'data' => [
                'class_id' => $schedule->class_id,
                'day' => $day ? $day->day : 'Unknown Day',
                'subjects' => [$subjectData],
            ],
        ], 200);
    }



    public function getSchedule($class_id)
    {
        try {
            $schedules = Schedule::where('class_id', $class_id)
                ->with(['day', 'subject.teacher', 'subject.subject'])
                ->get();

            $daysOfWeek = [
                'senin',
                'selasa',
                'rabu',
                'kamis',
                'jumat',
                'sabtu',
                'minggu'
            ];

            $groupedSchedules = [];

            foreach ($schedules as $schedule) {
                $dayName = $schedule->day->day ?? '';

                $start_time = TimeSchedule::find($schedule->start_time);
                $end_time = TimeSchedule::find($schedule->end_time);

                $groupedSchedules[$dayName][] = [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject->subject->subject_name ?? 'Unknown Subject',
                    'teacher' => $schedule->subject->teacher->fullname ?? 'Teacher not assigned',
                    'start_time' => $start_time->time  ?? 'null',
                    'end_time' => $end_time->time  ?? 'null',
                ];
            }

            $response = [];

            foreach ($daysOfWeek as $day) {
                $response[] = [
                    'day' => $day,
                    'subjects' => isset($groupedSchedules[$day])
                        ? collect($groupedSchedules[$day])->sortBy('start_time')->values()->all()
                        : [],
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Successfully fetched schedule',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch schedule',
                'error' => $e->getMessage(),
            ], 200);
        }
    }



    public function getDays()
    {
        $days = Day::all();

        if ($days->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Days not found',
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $days,
        ], 200);
    }
    public function updateSchedule(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'subject_id' => 'nullable|integer',
            'start_time' => 'nullable|integer',
            'end_time' => 'nullable|integer',
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }
    
        $schedule = Schedule::find($id);
    
        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found',  
            ], 404);
        }
    
        // Cek apakah start_time sudah dipakai di jadwal lain (tidak boleh sama)
        if ($request->has('start_time')) {
            $existingStartTime = Schedule::where('start_time', $request->start_time)
                ->where('id', '!=', $id)
                ->exists();
    
            if ($existingStartTime) {
                return response()->json([
                    'status' => false,
                    'message' => 'Start time sudah digunakan oleh jadwal lain',
                ], 422);
            }
    
            // Start time boleh sama dengan end time sebelumnya
            $prevSchedule = Schedule::where('end_time', $request->start_time)->first();
            if (!$prevSchedule && $request->start_time !== $schedule->end_time) {
                return response()->json([
                    'status' => false,
                    'message' => 'Start time harus sama dengan end time sebelumnya atau berbeda dengan start time lain',
                ], 422);
            }
    
            $schedule->start_time = $request->start_time;
        }
    
        // Cek apakah end_time sudah dipakai di jadwal lain (tidak boleh sama)
        if ($request->has('end_time')) {
            $existingEndTime = Schedule::where('end_time', $request->end_time)
                ->where('id', '!=', $id)
                ->exists();
    
            if ($existingEndTime) {
                return response()->json([
                    'status' => false,
                    'message' => 'End time sudah digunakan oleh jadwal lain',
                ], 422);
            }
    
            // End time boleh sama dengan start time selanjutnya
            $nextSchedule = Schedule::where('start_time', $request->end_time)->first();
            if (!$nextSchedule && $request->end_time !== $schedule->start_time) {
                return response()->json([
                    'status' => false,
                    'message' => 'End time harus sama dengan start time selanjutnya atau berbeda dengan end time lain',
                ], 422);
            }
    
            $schedule->end_time = $request->end_time;
        }
    
        if ($request->has('subject_id')) {
            $schedule->subject_id = $request->subject_id;
        }
    
        $schedule->save();
    
        // Ambil data untuk response
        $start_time = TimeSchedule::find($schedule->start_time);
        $end_time = TimeSchedule::find($schedule->end_time);
        $subject = Subject::find($schedule->subject_id);
        $subjectTeacher = ClassSubject::where('subject_id', $schedule->subject_id)->first();
        $teacher = $subjectTeacher ? Teacher::find($subjectTeacher->teacher_id) : null;
    
        return response()->json([
            'status' => true,
            'message' => 'Schedule updated successfully',
            'data' => [
                'subject' => $subject ? $subject->subject_name : null,
                'teacher' => $teacher ? $teacher->fullname : null,
                'start_time' => $start_time ? $start_time->time : null,
                'end_time' => $end_time ? $end_time->time : null,
            ]
        ], 200);
    }
    
}
