<?php

namespace App\Http\Controllers\StudentCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Student\Dashboard\StudentTask;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Superadmin\Dashboard\Subject;
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
                'file' => $task->file ? "https://docs.google.com/gview?url=" . asset('storage/' . $task->file) . "&embedded=true" : null,
                'link' => $task->link ?? null,
                'status' => $status,
                'score' => $studentTask ? $studentTask->score : 0,
            ];
        });

        return response()->json(['status' => true, 'data' => $response], 200);
    }

    public function StudentGetListTask($class_id, $subject_id)
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

    public function StudentAddTask(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'task_id' => 'required|integer',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'link' => 'nullable|url',
            ]);

            if ($validate->fails()) {
                return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
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

            $submittedAt = Carbon::now($timezone);

            $studentTask = StudentTask::create([
                'task_id' => $request->task_id,
                'student_id' => auth()->id(),
                'file' => $filePath,
                'link' => $link,
                'status' => 'Dikumpulkan',
                'submitted_at' => $submittedAt,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task submitted successfully',
                'data' => [
                    'id' => $studentTask->id,
                    'task_id' => $studentTask->task_id,
                    'student_id' => $studentTask->student_id,
                    'file' => $studentTask->file ? "https://docs.google.com/gview?url=" . asset('storage/' . $studentTask->file) . "&embedded=true" : null,
                    'link' => $studentTask->link,
                    'status' => $studentTask->status,
                    'submitted_at' => $submittedAt->translatedFormat('d F Y H:i'),
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



    public function StudentEditTask(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'link' => 'nullable|url',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $student_id = auth()->id();

        $task = StudentTask::where('id', $id)
            ->where('student_id', $student_id)
            ->first();

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Tugas tidak ditemukan atau Anda tidak memiliki akses.',
            ], 200);
        }

        if ($task->status === 'Selesai') {
            return response()->json([
                'status' => false,
                'message' => 'Tugas yang sudah selesai tidak dapat diedit.',
            ], 200);
        }

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
            'message' => 'Tugas berhasil diperbarui.',
            'data' => [
                'file' => $task->file ? "https://docs.google.com/gview?url=" . asset('storage/' . $task->file) . "&embedded=true" : null,
                'link' => $task->link ?? null,
            ]
        ], 200);
    }


    public function StudentGetTaskByStatus($subject_id = 'all', $status = 'all')
    {
        $student_id = auth()->id();

        $tasksQuery = AddTask::with([
            'studentTasks' => function ($query) use ($student_id) {
                $query->where('student_id', $student_id);
            }
        ]);

        if ($subject_id !== 'all') {
            $tasksQuery->where('subject_id', $subject_id);
        }

        $tasks = $tasksQuery->get();

        $filteredTasks = $tasks->filter(function ($task) use ($status) {
            $studentTask = $task->studentTasks->first();
            $currentDateTime = Carbon::now();

            $taskStatus = 'Belum Dikumpulkan';

            if ($task->due_date && !empty($task->hour) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $task->hour)) {
                $dueDateTime = Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour);

                if ($currentDateTime->gt($dueDateTime)) {
                    $taskStatus = 'Kadaluarsa';
                }
            }

            if ($studentTask) {
                $taskStatus = $studentTask->status;
            }

            return $status === 'all' || strtolower($taskStatus) === strtolower(str_replace('_', ' ', $status));
        });

        $statusOrder = [
            'belum dikumpulkan' => 1,
            'dikumpulkan' => 2,
            'selesai' => 3,
            'kadaluarsa' => 4,
        ];

        $filteredTasks = $filteredTasks->sortBy(function ($task) use ($statusOrder) {
            $studentTask = $task->studentTasks->first();
            $currentDateTime = Carbon::now();

            $taskStatus = 'Belum Dikumpulkan';

            if ($task->due_date && !empty($task->hour) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $task->hour)) {
                $dueDateTime = Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour);

                if ($currentDateTime->gt($dueDateTime)) {
                    $taskStatus = 'Kadaluarsa';
                }
            }

            if ($studentTask) {
                $taskStatus = $studentTask->status;
            }

            return $statusOrder[strtolower($taskStatus)];
        });

        $filteredTasks = $filteredTasks->sortByDesc(function ($task) {
            return $task->created_at;
        });

        $response = $filteredTasks->map(function ($task) {
            $studentTask = $task->studentTasks->first();
            $currentDateTime = Carbon::now();

            $taskStatus = 'Belum Dikumpulkan';
            $info = 'Tidak ada batas waktu';

            if ($task->due_date && !empty($task->hour) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $task->hour)) {
                $dueDateTime = Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour);

                if ($currentDateTime->gt($dueDateTime)) {
                    $taskStatus = 'Kadaluarsa';
                    $info = 'Batas waktu: ' . $dueDateTime->format('d F Y H:i');
                } else {
                    $info = 'Batas waktu: ' . $dueDateTime->format('d F Y H:i');
                }
            }

            if ($studentTask) {
                $taskStatus = $studentTask->status;
                if ($studentTask->status === 'Dikumpulkan') {
                    $info = 'Dikumpulkan pada: ' . Carbon::parse($studentTask->submitted_at)->format('d F Y H:i');
                } elseif ($studentTask->status === 'Selesai') {
                    $info = 'Selesai pada: ' . Carbon::parse($studentTask->submitted_at)->format('d F Y H:i');
                }
            }

            $subject = ClassSubject::find($task->subject_id);
            $subjectName = Subject::find($subject->subject_id);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'subject' => $subject ? $subjectName->subject_name : 'Tidak Diketahui',
                'status' => $taskStatus,
                'info' => $info,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched tasks',
            'data' => $response,
        ], 200);
    }


    public function StudentGetTaskById($task_id)
    {
        $student_id = auth()->id();

        $task = AddTask::with([
            'studentTasksId' => function ($query) use ($student_id) {
                $query->where('student_id', $student_id);
            }
        ])->find($task_id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found'], 200);
        }

        $studentTask = $task->studentTasks->first();
        $currentDateTime = Carbon::now();
        $dueDateTime = Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour);

        $status = 'Belum Dikerjakan';

        if ($studentTask) {
            $status = $studentTask->status;
        } elseif ($currentDateTime->gt($dueDateTime)) {
            $status = 'Kadaluarsa';
        }

        $response = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'date' => Carbon::parse($task->date)->translatedFormat('d F Y'),
            'due_date' => Carbon::parse($task->due_date)->translatedFormat('d F Y'),
            'hour' => Carbon::createFromFormat('H:i:s', $task->hour)->format('H:i'),
            'file' => $task->file ? "https://docs.google.com/gview?url=" . asset('storage/' . $task->file) . "&embedded=true" : null,
            'answer' => $studentTask && $studentTask->file
                ? "https://docs.google.com/gview?url=" . asset('storage/' . $studentTask->file) . "&embedded=true"
                : ($studentTask->link ?? null),
            'link' => $task->link ?? null,
            'status' => $status,
            'score' => $studentTask ? $studentTask->score : 0,
        ];
        return response()->json(['status' => true, 'data' => $response], 200);
    }

    public function getStudentAnswersByTaskId($task_id)
    {
        $task = AddTask::with('studentTasks.student')->find($task_id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found'], 200);
        }

        // Ambil semua siswa yang ada di kelas terkait dengan tugas ini
        $students = Student::whereHas('studentClasses', function ($query) use ($task) {
            $query->where('class_id', $task->class_id);
        })->get();

        $responses = [];

        foreach ($students as $student) {
            // Cek apakah siswa ini sudah mengumpulkan tugas
            $studentTask = $task->studentTasks->where('student_id', $student->id)->first();

            if ($studentTask) {
                if ($studentTask->score !== null) {
                    $status = 'Sudah Dinilai';
                } elseif (now()->gt(Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour))) {
                    $status = 'Kadaluarsa';
                } else {
                    $status = 'Belum Dinilai';
                }

                $answer = $studentTask->file
                    ? "https://docs.google.com/gview?url=" . asset('storage/' . $studentTask->file) . "&embedded=true"
                    : ($studentTask->link ?? null);

                $score = $studentTask->score ?? 0;
            } else {
                $status = 'Belum Mengumpulkan';
                $answer = null;
                $score = 0;
            }

            $responses[] = [
                'student_id' => $student->id,
                'studentTask_id' => $studentTask->id ?? null,
                'student_name' => $student->fullname,
                'answer' => $answer,
                'status' => $status,
                'score' => $score,
            ];
        }

        // Custom sorting: "Belum Dinilai" paling atas, "Sudah Dinilai" paling bawah
        usort($responses, function ($a, $b) {
            $order = ['Belum Dinilai' => 1, 'Belum Mengumpulkan' => 2, 'Kadaluarsa' => 3, 'Sudah Dinilai' => 4];

            return $order[$a['status']] <=> $order[$b['status']];
        });

        return response()->json(['status' => true, 'data' => $responses], 200);
    }



    public function getStudentAnswerByStudentTaskId($studentTask_id)
    {
        $studentTask = StudentTask::with('student', 'task')->find($studentTask_id);

        if (!$studentTask) {
            return response()->json(['status' => false, 'message' => 'Student Task not found'], 200);
        }

        $task = $studentTask->task;
        $student = $studentTask->student;

        $status = $studentTask->status ?? 'Belum Dikerjakan';
        if (!$studentTask->status && now()->gt(Carbon::parse($task->due_date)->setTimeFromTimeString($task->hour))) {
            $status = 'Kadaluarsa';
        }

        $answerContent = null;
        if ($studentTask->file) {
            $answerContent = "https://docs.google.com/gview?url=" . asset('storage/' . $studentTask->file) . "&embedded=true";
        } elseif ($studentTask->link) {
            $answerContent = $studentTask->link;
        }

        $gradingStatus = $studentTask->score !== null ? 'Sudah Dinilai' : 'Belum Dinilai';
        $score = $studentTask->score ?? null;

        $submittedAt = $studentTask->submitted_at
            ? Carbon::parse($studentTask->submitted_at)->locale('id')->translatedFormat('l, d F Y H:i')
            : null;

        $student = Student::find($studentTask->student_id);

        $response = [
            'student_name' => $student->fullname ?? 'Unknown',
            'submitted_at' => $submittedAt,
            'answer' => $answerContent,
            'status' => $gradingStatus,
            'score' => $score,
        ];

        return response()->json(['status' => true, 'data' => $response], 200);
    }
}
