<?php

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\StudentCtrl\Auth\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCtrl\Dashboard\TaskController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('student/import', [ViewController::class,'index']);

Route::post('student/import', [StudentController::class,'importExcelData']);

Route::get('/tasks/{class_id}/{subject_id}', [TaskController::class, 'show'])->name('tasks.show');


