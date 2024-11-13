<?php

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\StudentCtrl\Auth\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('student/import', [ViewController::class,'index']);

Route::post('student/import', [StudentController::class,'importExcelData']);


// Route::post('student/import', [TeacherController::class,'importExcelData']);

// Route::post('student/import', [PrincipalController::class,'importExcelData']);

