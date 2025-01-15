<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Dashboard\AddTask;
use Illuminate\Http\Request;
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
                    'due_date' => 'nullable|string|max:255'
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
                'due_date' => $request->due_date
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
                    'file' => asset(path: 'storage/' . $task->file),
                    'due_date' => $task->due_date,
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
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $response,
        ], 200);
    }
}
