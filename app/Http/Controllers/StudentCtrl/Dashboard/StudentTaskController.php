<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Teacher\Dashboard\AddTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Storage;
use Validator;

class StudentTaskController extends Controller
{
    public function StudentGetTask($student_id, $class_id, $subject_id)
    {
        $tasks = AddTask::where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->with([
                'studentTasks' => function ($query) use ($student_id) {
                    $query->where('student_id', $student_id);
                }
            ])
            ->get();

        $response = $tasks->map(function ($task) use ($student_id) {
            $studentTask = $task->studentTasks->first();
            $currentDateTime = Carbon::now();
            $dueDateTime = Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour);

            $status = 'Belum Dikerjakan';
            if ($studentTask) {
                $status = $studentTask->status;
            } elseif ($currentDateTime->gt($dueDateTime)) {
                $status = 'Kadaluarsa';
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'date' => Carbon::parse($task->date)->translatedFormat('d F Y'),
                'due_date' => Carbon::parse($task->due_date)->translatedFormat('d F Y'),
                'hour' => Carbon::parse($task->hour)->translatedFormat('H:i'),
                'file' => $task->file ? asset('storage/' . $task->file) : null,
                'link' => $task->link ?? null, 
                'status' => $status,
                'score' => $studentTask ? $studentTask->score : 0,
            ];
        });

        return response()->json(['status' => true, 'data' => $response], 200);
    }

    public function StudentGetListTask($class_id, $subject_id){
        $taskList = AddTask::where('class_id', $class_id)
        ->where('subject_id', $subject_id)
        ->orderBy('date', 'desc')
        ->get();

    if ($taskList->isEmpty()) {
        return response()->json([
            'status'  => false,
            'message' => 'No tasks found',
            'data'    => [],
        ], 200);
    }

    $groupedTasks = $taskList->groupBy(function ($task) {
        return Carbon::parse($task->date)->translatedFormat('d F Y');
    });

    $groupedTasks = $groupedTasks->sortByDesc(function ($tasks, $date) {
        return Carbon::createFromFormat('d F Y', $date);
    });

    $response = $groupedTasks->map(function ($tasks, $date) {
        $sortedTasks = $tasks->sortByDesc(function ($task) {
            return Carbon::parse($task->date);
        });

        return [
            'date'  => $date,
            'tasks' => $sortedTasks->map(function ($task) {
                return [
                    'id'    => $task->id,
                    'title' => $task->title,
                    'time'  => Carbon::parse($task->date)->translatedFormat('H:i'),
                ];
            })->values(),
        ];
    })->values();

    return response()->json([
        'status'  => true,
        'message' => 'Successfully fetched tasks',
        'data'    => $response,
    ], 200);
    }

    public function StudentAddTask(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'task_id' => 'required|integer',
                'student_id' => 'required|integer',
                'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
            }

            $filePath = $request->file('file')->store('file', 'public');

            $studentTask = StudentTask::create([
                'task_id' => $request->task_id,
                'student_id' => $request->student_id,
                'file' => $filePath,
                'status' => 'Dikumpulkan',
                'submitted_at' => Carbon::now(), 
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task submitted successfully',
                'data' => [
                    'id' => $studentTask->id,
                    'task_id' => $studentTask->task_id,
                    'student_id' => $studentTask->student_id,
                    'file' => asset('storage/' . $studentTask->file),
                    'status' => $studentTask->status,
                    'submitted_at' => Carbon::parse($studentTask->submitted_at)->translatedFormat('d F Y H:i'),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function StudentEditTask(Request $request, $id){
        $validate = Validator::make(
            $request->all(),
            [
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            ]
        );

        
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $task = StudentTask::find($id);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'task not found',
            ], 422);
        }

        if ($request->hasFile('file') && $request->file !== null) {
            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }

            $path = $request->file('file')->store('file', options: 'public');
            $task->file = $path;
        }

        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'file' => $task->file ? asset('storage/' . $task->file) : null,
            ]
        ], 200);

    }

}
