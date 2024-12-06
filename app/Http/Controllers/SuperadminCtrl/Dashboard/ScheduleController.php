<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\Schedule;
use Illuminate\Http\Request;
use Validator;

class ScheduleController extends Controller
{
    public function addSchedule(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'class_id' => 'required|integer',
            'day' => 'required|string',
            'subjects' => 'required|array',
            'subjects.*.subject_id' => 'required|integer|exists:class_subjects,id',
            'subjects.*.start_time' => 'required|date_format:H:i',
            'subjects.*.end_time' => 'required|date_format:H:i|after:class_subjects.*.start_time',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $schedule = Schedule::create([
            'class_id' => $request->class_id,
            'day' => $request->day,
            'subjects' => $request->subjects,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Schedule added successfully',
            'data' => $schedule,
        ], 200);
    }

    public function getSchedule($class_id)
    {
        $schedule = Schedule::where('class_id', $class_id)->get();

        if ($schedule->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $schedule,
        ], 200);
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'day' => 'sometimes|string',
            'subjects' => 'sometimes|array',
            'subjects.*.subject_id' => 'required_with:subjects|integer|exists:subjects,id',
            'subjects.*.start_time' => 'required_with:subjects|date_format:H:i',
            'subjects.*.end_time' => 'required_with:subjects|date_format:H:i|after:subjects.*.start_time',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $schedule->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Schedule updated successfully',
            'data' => $schedule,
        ], 200);
    }

    public function deleteSchedule($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Schedule not found',
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Schedule deleted successfully',
        ], 200);
    }
}
