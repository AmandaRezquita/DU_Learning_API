<?php

namespace App\Http\Controllers\SuperadminCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Superadmin\Dashboard\Subject;
use Illuminate\Http\Request;
use Validator;

class SubjectController extends Controller
{
    public function createSubject(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'subject_name' => 'required|string|max:255|unique:subjects,subject_name',
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
                'subject_name' => $request->subject_name,
            ];

            $subject = Subject::create($data);

            $success['subject_name'] = $subject->subject_name;
            
            return response()->json([
                'status' => true,
                'message' => 'Subject created successfully',
                'data' => $success,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'errors' => $th->getMessage()
            ], 500);

        }
    }

    public function editSubject(Request $request, $id){
        
        $validate = Validator::make(
            $request->all(),
            [
                'subject_name' => 'nullable|string|max:255|unique:subjects,subject_name',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => 'Subject not found',
            ], 200);
        }

        if ($request->has('subject_name') && $request->subject_name !== null) {
            $subject->subject_name = $request->subject_name;
        }


        $subject->save();

        return response()->json([
            'status' => true,
            'message' => 'Subject updated successfully',
            'data' => [
                'id' => $subject->id,
                'subject_name' => $subject->subject_name,
            ]
        ], 200);
    }



    public function getSubject()
{
    $subjects = Subject::all();

    if ($subjects->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No subjects found',
        ], 200);
    }

    return response()->json([
        'status' => true,
        'message' => 'Successfully retrieved subjects',
        'data' => $subjects,
    ], 200);
}

public function getSubjectById(Request $request, $id)
{
    $subject = Subject::find($id); 
    if (!$subject) {
        return response()->json([
            'status' => false,
            'message' => 'Subject not found',
        ], 200);
    }

    return response()->json([
        'status' => true,
        'message' => 'Successfully retrieved subject',
        'data' => $subject, 
    ], 200);
}

}
