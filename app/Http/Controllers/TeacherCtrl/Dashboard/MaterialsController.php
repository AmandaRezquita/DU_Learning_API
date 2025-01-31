<?php

namespace App\Http\Controllers\TeacherCtrl\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Dashboard\AddMaterials;
use Carbon\Carbon;
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
                    'file' => 'sometimes|file|mimes:pdf,doc,docx|max:2048|required_without:link',
                    'link' => 'sometimes|nullable|url|required_without:file',
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
            $link = null;

            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('file', 'public');
            } elseif ($request->filled('link')) {
                $link = $request->link;
            }

            $timezone = $request->timezone ?? 'Asia/Jakarta';

            $material = AddMaterials::create([
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'description' => $request->description,
                'date' => Carbon::now($timezone),
                'file' => $filePath,
                'link' => $link,
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
                    'date' => Carbon::parse($material->date)->translatedFormat('d F Y H:i'),
                    'file' => $material->file ? asset('storage/' . $material->file) : null,
                    'link' => $material->link,
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
            ->orderBy('date', 'asc')
            ->get();

        if ($materialList->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No materials found',
                'data' => [],
            ], 200);
        }

        $groupedMaterials = $materialList->groupBy(function ($material) {
            return Carbon::parse($material->date)->translatedFormat('d F Y');
        });

        $response = $groupedMaterials->map(function ($materials, $date) {
            return [
                'date' => $date,
                'materials' => $materials->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'title' => $material->title,
                        'time' => Carbon::parse($material->date)->translatedFormat('H:i'),
                        'class_id' => $material->class_id,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched materials',
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

        $material = AddMaterials::find($id);

        if (!$material) {
            return response()->json([
                'status' => false,
                'message' => 'Material not found',
            ], 200);
        }

        if ($request->has('title') && $request->title !== null) {
            $material->title = $request->title;
        }

        if ($request->has('description') && $request->description !== null) {
            $material->description = $request->description;
        }

        if ($request->hasFile('file')) {
            if ($material->file && Storage::disk('public')->exists($material->file)) {
                Storage::disk('public')->delete($material->file);
            }
            $path = $request->file('file')->store('file', 'public');
            $material->file = $path;
            $material->link = null;
        } elseif ($request->filled('link')) {
            if ($material->file && Storage::disk('public')->exists($material->file)) {
                Storage::disk('public')->delete($material->file);
            }
            $material->link = $request->link;
            $material->file = null;
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
                'link' => $material->link ?? null,
            ]
        ], 200);
    }

    public function getMaterialById($id)
    {
        $material = AddMaterials::where('id', $id)
            ->first();

        if (!$material) {
            return response()->json([
                'status' => false,
                'message' => 'Material not found',
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Material retrieved successfully',
            'data' => [
                'id' => $material->id,
                'class_id' => $material->class_id,
                'subject_id' => $material->subject_id,
                'title' => $material->title,
                'description' => $material->description,
                'date' => Carbon::parse($material->date)->translatedFormat('d F Y H:i'),
                'file' => $material->file ? asset(path: 'storage/' . $material->file) : null,
                'link' => $material->link ?? null,
            ]
        ], 200);
    }
}
