<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\SchoolClass;
use App\Models\Superadmin\Dashboard\subjectaddTeacher;
use App\Models\Teacher\Auth\Teacher;
use Illuminate\Http\Request;
use Validator;

class SubjectTeacherController extends Controller
{
    public function getTeacherSubject($class_id, $subject_id)
    {
        $teacherList = ClassSubject::where('class_id', $class_id)
            ->where('id', $subject_id)
            ->with('teacher')
            ->get();
       
        $response = [];
        foreach ($teacherList as $teacherEntry) {
            $response[] = [
                'teacher_name' => $teacherEntry->teacher ? $teacherEntry->teacher->fullname : 'Tidak ada guru',
            ];
        }


        if ($teacherList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Teacher not found',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $response,
        ], 200);
    }

}
