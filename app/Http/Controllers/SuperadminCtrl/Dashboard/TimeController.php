<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\TimeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimeController extends Controller
{
    public function createTime(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'time' => 'required|string|max:255|unique:time_schedules,time',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $data = [
                'time' => $request->time,
            ];

            $time = TimeSchedule::create($data);

            $success['time'] = $time->time;

            return response()->json([
                'status' => true,
                'message' => 'Time created successfully',
                'data' => $success,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);

        }
    }

    public function getTime()
    {
        $times = TimeSchedule::all();

        if ($times->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No times found',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully retrieved subjects',
            'data' => $times,
        ], 200);
    }

    public function editTime(Request $request, $id)
    {

        $validate = Validator::make(
            $request->all(),
            [
                'time' => 'nullable|string|max:255|unique:time_schedules,time',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $time = TimeSchedule::find($id);

        if (!$time) {
            return response()->json([
                'status' => false,
                'message' => 'Time not found',
            ], 200);
        }

        if ($request->has('time') && $request->time !== null) {
            $time->time = $request->time;
        }


        $time->save();

        return response()->json([
            'status' => true,
            'message' => 'time updated successfully',
            'data' => [
                'id' => $time->id,
                'time' => $time->time,
            ]
        ], 200);
    }
}
