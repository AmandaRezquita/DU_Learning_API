<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Teacher\Dashboard\AddTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            $dueDateTime = Carbon::parse($task->due_date);

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
                'due_date' => Carbon::parse($task->due_date)->translatedFormat('d F Y H:i'),
                'file' => asset('storage/' . $task->file),
                'status' => $status,
                'score' => $studentTask ? $studentTask->score : 0,
            ];
        });

        return response()->json(['status' => true, 'data' => $response], 200);
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

            $filePath = $request->file('file')->store('assignments', 'public');

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

}
