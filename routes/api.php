<?php

use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;


Route::post("studentRegis",[StudentController::class, "register"]);

Route::post("studentLogin",[StudentController::class, "login"]);

Route::post("teacherRegis",[TeacherController::class, "register"]);

Route::post("teacherLogin",[TeacherController::class, "login"]);

Route::post("institutionRegis",[TeacherController::class, "register"]);

Route::post("institutionLogin",[TeacherController::class, "login"]);

Route::group([
    "middleware" => ["auth:sanctum"]
], function(){
    Route::get("studentProfile",[StudentController::class,"profile"]);
    Route::get("studentLogout",[StudentController::class,"logout"]);
    Route::get("teacherProfile",[TeacherController::class,"profile"]);
    Route::get("teacherLogout",[TeacherController::class,"logout"]);
    Route::get("institutionProfile",[TeacherController::class,"profile"]);
    Route::get("institutionLogout",[TeacherController::class,"logout"]);
});


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
