<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Teacher\Dashboard\AddTask;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;
use Validator;

class TaskController extends Controller
{
    public function show($class_id, $subject_id)
    {
        $tasks = AddTask::where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->get();
        return view('task', compact('tasks', 'class_id', 'subject_id'));
    }
    public function addTask(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'class_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'link' => 'nullable|url',
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

            if (!$request->hasFile('file') && !$request->filled('link')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Either a file or link must be provided',
                ], 422);
            }

            $filePath = null;
            $link = null;

            if ($request->hasFile('file')) {
                $fileName = $request->file('file')->getClientOriginalName();

                $fileName = str_replace(' ', '_', $fileName);

                $filePath = $request->file('file')->storeAs('file', $fileName, 'public');

                $fileUrl = url('storage/file/' . $fileName);
            } elseif ($request->filled('link')) {
                $link = $request->link;
            }

            $timezone = $request->timezone ?? 'Asia/Jakarta';

            $task = AddTask::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filePath,
                'link' => $link,
                'date' => Carbon::now($timezone),
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
                    'date' => Carbon::parse($task->date)->translatedFormat('d F Y H:i'),
                    'due_date' => Carbon::parse($task->due_date)->translatedFormat('d F Y'),
                    'hour' => Carbon::parse($task->hour)->translatedFormat('H:i'),
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
            ->orderBy('date', 'desc')
            ->get();

        if ($taskList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No tasks found',
                'data' => [],
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
                'date' => $date,
                'tasks' => $sortedTasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'time' => Carbon::parse($task->date)->translatedFormat('H:i'),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched tasks',
            'data' => $response,
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
                'submitted_at' => Carbon::parse($studentTask->submitted_at)->translatedFormat('Y-m-d H:i'),
            ]
        ], 200);
    }

    public function editTask(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'link' => 'nullable|url',
            'due_date' => 'nullable|date_format:Y-m-d',
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
                'message' => 'Task not found',
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

            $originalFileName = $request->file('file')->getClientOriginalName();
            $storedFileName = time() . '_' . str_replace(' ', '_', $originalFileName);

            $path = $request->file('file')->storeAs('file', $storedFileName, 'public');

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
            'message' => 'Task updated successfully',
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

    public function getTaskById($id)
    {
        $task = AddTask::find($id);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched task',
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file' => $task->file ? asset('storage/' . $task->file) : null,
                'link' => $task->link ?? null,
                'date' => $task->date ? Carbon::parse($task->date)->translatedFormat('Y-m-d H:i') : null,
                'due_date' => $task->due_date ? Carbon::parse($task->due_date)->translatedFormat('Y-m-d') : null,
                'hour' => $task->hour ? Carbon::parse($task->hour)->translatedFormat('H:i') : null,
            ],
        ], 200);
    }

    public function getTaskByDate($class_id)
    {
        $tasks = AddTask::where('class_id', $class_id)->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No tasks found',
                'data' => [],
            ], 200);
        }

        $response = [
            'date' => $tasks->first()->date ? Carbon::parse($tasks->first()->date)->translatedFormat('Y-m-d') : null,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'time' => $task->date ? Carbon::parse($task->date)->translatedFormat('H:i') : null,
                ];
            }),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched tasks',
            'data' => $response,
        ], 200);
    }

}