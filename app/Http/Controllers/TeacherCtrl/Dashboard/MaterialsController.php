<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Dashboard\AddMaterials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MaterialsController extends Controller
{
    public function addMaterials(Request $request)
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

            $material = AddMaterials::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filePath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Material added successfully',
                'data' => [
                    'id' => $material->id,
                    'class_id' => $material->class_id,
                    'subject_id' => $material->subject_id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'file' => asset('storage/' . $material->file),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add material',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMaterials($class_id, $subject_id)
    {
        $materialList = AddMaterials::where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->get();

        $response = [];
        foreach ($materialList as $material) {
            $response[] = [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'file' => asset('storage/' . $material->file),
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $response,
        ], 200);
    }

    public function editMaterials(Request $request, $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validate->errors(),
            ], 422);
        }

        $material = AddMaterials::find($id);

        if (!$material) {
            return response()->json([
                'status' => false,
                'message' => 'Material not found',
            ], 422);
        }

        if ($request->has('title') && $request->title !== null) {
            $material->title = $request->title;
        }

        if ($request->has('description') && $request->description !== null) {
            $material->description = $request->description;
        }

        if ($request->hasFile('file') && $request->file !== null) {
            if ($material->file && Storage::disk('public')->exists($material->file)) {
                Storage::disk('public')->delete($material->file);
            }

            $path = $request->file('file')->store('file', 'public');
            $material->file = $path;
        }

        $material->save();

        return response()->json([
            'status' => true,
            'message' => 'Material updated successfully',
            'data' => [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'file' => $material->file ? asset('storage/' . $material->file) : null,
            ]
        ], 200);
    }
}
