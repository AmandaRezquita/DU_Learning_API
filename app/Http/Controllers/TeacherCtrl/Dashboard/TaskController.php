<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
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
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' => 'required|integer',
                    'subject_id' => 'required|integer',
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:255',
                    'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
                    'due_date' => 'nullable|date_format:Y-m-d H:i',
                ]
            );

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validate->errors(),
                ], 422);
            }

            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('file', 'public');
            }

            $task = AddTask::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filePath,
                'date' => now(),
                'due_date' => $request->due_date,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task added successfully',
                'data' => [
                    'id' => $task->id,
                    'class_id' => $task->class_id,
                    'subject_id' => $task->subject_id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file' => asset('storage/' . $task->file),
                    'date' => Carbon::parse($task->date)->translatedFormat('d F Y'),
                    'due_date' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('d F Y') : null,
                    'due_date_hour' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('H:i') : null,
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
                'file' => asset('storage/' . $task->file),
                'date' => Carbon::parse($task->date)->translatedFormat('d F Y H:i'),
                'due_date' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('d F Y') : null,
                'due_date_hour' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('H:i') : null,
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
        $validate = Validator::make(
            $request->all(),
            [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'due_date' => 'nullable|date_format:Y-m-d H:i',
            ]
        );

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
            ], 422);
        }

        if ($request->has('title') && $request->title !== null) {
            $task->title = $request->title;
        }

        if ($request->has('description') && $request->description !== null) {
            $task->description = $request->description;
        }

        if ($request->hasFile('file') && $request->file !== null) {
            if ($task->file && Storage::disk('public')->exists($task->file)) {
                Storage::disk('public')->delete($task->file);
            }

            $path = $request->file('file')->store('file', 'public');
            $task->file = $path;
        }

        if ($request->has('due_date') && $request->due_date !== null) {
            $task->due_date = $request->due_date;
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
                'due_date' => $task->due_date,
            ]
        ], 200);
    }
}
