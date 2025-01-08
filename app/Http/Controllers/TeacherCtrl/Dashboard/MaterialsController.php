<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Dashboard\AddMaterials;
use Illuminate\Http\Request;
use Validator;

class MaterialsController extends Controller
{
    public function addMaterials(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'class_id' =>  'required|integer',
                    'subject_id' => 'required|integer',
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:255',
                    'file' => 'required|image',
                ]
            );
        }

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $material = AddMaterials::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'file' => $request->file,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Material added successfully',
            'data' => $material,
        ], 200);
    }
}
