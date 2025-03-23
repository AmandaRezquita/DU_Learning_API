<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student\Auth\Student;
use App\Models\Superadmin\Dashboard\StudentClass;
use App\Models\Superadmin\Dashboard\StudentRaport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RaportController extends Controller
{
    public function CreateRaport(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'smt' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf|max:2048'
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $fileName = str_replace(' ', '_', $request->file('file')->getClientOriginalName());
            $filePath = $request->file('file')->storeAs('raport', $fileName, 'public');
            $filePath = asset('storage/' . $filePath);
        }

        $raport = StudentRaport::create([
            'student_id' => $request->student_id,
            'smt' => $request->smt,
            'file' => $filePath ? "https://docs.google.com/gview?url=$filePath&embedded=true" : null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Raport created successfully',
            'data' => $raport
        ]);
    }


    public function UpdateRaport(Request $request, $id)
    {
        $request->validate([
            'smt' => 'required|in:1,2,3,4,5,6',
            'file' => 'nullable|file|mimes:pdf|max:2048'
        ]);

        $raport = StudentRaport::findOrFail($id);

        if ($request->hasFile('file')) {
            if ($raport->file) {
                Storage::disk('public')->delete($raport->file);
            }

            $fileName = str_replace(' ', '_', $request->file('file')->getClientOriginalName());
            $filePath = $request->file('file')->storeAs('raport', $fileName, 'public');

            $raport->file = 'storage/' . $filePath;
        }

        $raport->smt = $request->smt;
        $raport->save();

        return response()->json([
            'status' => true,
            'message' => 'Raport updated successfully',
            'data' => $raport
        ]);
    }

    public function DeleteRaport($id)
    {
        $raport = StudentRaport::findOrFail($id);

        if ($raport->file) {
            Storage::disk('public')->delete($raport->file);
        }

        $raport->delete();

        return response()->json([
            'status' => true,
            'message' => 'Raport deleted successfully'
        ]);
    }

    public function getStudentRaports()
    {
        $user = auth()->user();

        if (!$user || !$user instanceof Student) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $student_id = $user->id;
        $student = Student::find($student_id);

        $semesters = ['1', '2', '3', '4', '5', '6'];
        $raports = StudentRaport::where('student_id', $student_id)->get()->keyBy('smt');

        $formattedRaports = collect($semesters)->map(function ($smt) use ($raports) {
            return [
                'raport_id' => $raports->has($smt) ? $raports[$smt]->id : null,
                'smt' => $smt,
                'file' => $raports->has($smt) ? $raports[$smt]->file : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched student raports',
            'data' => [
                'student_id' => $student->id,
                'student_name' => $student->fullname ?? 'null',
                'raports' => $formattedRaports,
            ]
        ]);
    }


    public function getStudentListRaport($class_id)
    {
        $semesters = ['1', '2', '3', '4', '5', '6'];

        $students = StudentClass::where('class_id', $class_id)->get();

        $studentRaports = $students->map(function ($student) use ($semesters) {
            $raports = StudentRaport::where('student_id', $student->id)->get()->keyBy('smt');

            $studentName = Student::find($student->student_id);

            $formattedRaports = collect($semesters)->map(function ($smt) use ($raports) {
                return [
                    'raport_id' => $raports->has($smt) ? $raports[$smt]->id : null,
                    'smt' => $smt,
                    'file' => $raports->has($smt) ? $raports[$smt]->file : null,
                ];
            });

            return [
                'student_id' => $student->id,
                'student_name' => $studentName->fullname ?? 'null',
                'raports' => $formattedRaports,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched student raports by class',
            'data' => $studentRaports,
        ]);
    }


}
