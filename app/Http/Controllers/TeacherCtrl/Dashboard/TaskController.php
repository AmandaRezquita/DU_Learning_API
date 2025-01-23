<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Teacher\Dashboard\AddTask;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;
use Validator;

class TaskController extends Controller
{
    public function addTask(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'class_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'file' => 'sometimes|file|mimes:pdf,doc,docx|max:2048|required_without:link',
                'link' => 'sometimes|nullable|url|required_without:file',
                'due_date' => 'nullable|date_format:Y-m-d',
                'hour' => 'nullable|date_format:H:i',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validate->errors(),
                ], 422);
            }

            $filePath = null;
            $link = null;

            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('file', 'public');
            } elseif ($request->filled('link')) {
                $link = $request->link;
            }

            $task = AddTask::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filePath, 
                'link' => $link,
                'date' => now(),
                'due_date' => $request->due_date,
                'hour' => $request->hour,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task created successfully',
                'data' => [
                    'id' => $task->id,
                    'class_id' => $task->class_id,
                    'subject_id' => $task->subject_id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file' => $task->file ? asset('storage/' . $task->file) : null,
                    'link' => $task->link,
                    'date' => $task->date,
                    'due_date' => $task->due_date,
                    'hour' => $task->hour,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTask($class_id, $subject_id)
    {
        $taskList = AddTask::where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->get();

        $response = [];
        foreach ($taskList as $task) {
            $response[] = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file' => $task->file ? asset('storage/' . $task->file) : null,
                'link' => $task->link ?? null,
                'date' => Carbon::parse($task->date)->translatedFormat('d F Y H:i'),
                'due_date' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('d F Y') : null,
                'hour' => $task->hour ? Carbon::parse($task->hour)->translatedFormat('H:i') : null,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched tasks',
            'data' => $response,
        ], 200);
    }

    public function editTask(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'link' => 'nullable|url',
            'due_date' => 'nullable|date_format:Y-m-d H:i',
            'hour' => 'nullable|date_format:H:i'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $task = AddTask::find($id);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $task->title = $request->title ?? $task->title;
        $task->description = $request->description ?? $task->description;
        $task->due_date = $request->due_date ?? $task->due_date;
        $task->hour = $request->hour ?? $task->hour;

        if ($request->hasFile('file')) {
            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }
            $path = $request->file('file')->store('file', 'public');
            $task->file = $path;
            $task->link = null; 
        } elseif ($request->filled('link')) {
            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }
            $task->link = $request->link;
            $task->file = null;
        }

        $task->save();

        return response()->json([
            'status' => true,
            'message' => 'Material updated successfully',
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file' => $task->file ? asset('storage/' . $task->file) : null,
                'link' => $task->link ?? null,
                'due_date' => $task->due_date,
                'hour' => $task->hour
            ]
        ], 200);
    }

    public function gradeTask(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'student_task_id' => 'required|integer',
            'score' => 'required|integer|min:0|max:100',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $studentTask = StudentTask::findOrFail($request->student_task_id);
        $studentTask->update([
            'score' => $request->score,
            'status' => 'Selesai',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Task graded successfully',
            'data' => [
                'id' => $studentTask->id,
                'task_id' => $studentTask->task_id,
                'student_id' => $studentTask->student_id,
                'file' => asset('storage/' . $studentTask->file),
                'status' => $studentTask->status,
                'score' => $studentTask->score,
                'submitted_at' => Carbon::parse($studentTask->submitted_at)->translatedFormat('d F Y H:i'),
            ]
        ], 200);
    }

    public function getTaskById($class_id, $subject_id, $id)
    {
        $task = AddTask::where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->where('id', $id)
            ->first();

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        }

        $response = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'file' => $task->file ? asset('storage/' . $task->file) : null,
            'link' => $task->link ?? null,
            'date' => Carbon::parse($task->date)->translatedFormat('d F Y H:i'),
            'due_date' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('d F Y') : null,
            'hour' => $task->hour ? Carbon::parse($task->hour)->translatedFormat('H:i') : null,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched task',
            'data' => $response,
        ], 200);
    }
}