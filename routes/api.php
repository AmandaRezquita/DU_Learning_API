<?php


use App\Http\Controllers\LoginController;
use App\Http\Controllers\PrincipalCtrl\Auth\PrincipalController;
use App\Http\Controllers\StudentCtrl\Auth\StudentAvatarController;
use App\Http\Controllers\StudentCtrl\Auth\StudentController;
use App\Http\Controllers\StudentCtrl\Profile\StudentProfile;
use App\Http\Controllers\SuperadminCtrl\Auth\SchoolController;
use App\Http\Controllers\TeacherCtrl\Auth\TeacherAvatarController;
use App\Http\Controllers\TeacherCtrl\Auth\TeacherController;
use Illuminate\Support\Facades\Route;

Route::prefix('student/')->group(function () {
    Route::post("regis", [StudentController::class, "register"]);
    Route::post("login", [StudentController::class, "login"]);
    Route::get('avatar', [StudentAvatarController::class, 'getAvatars']);
});

Route::prefix('teacher/')->group(function () {
    Route::post("regis", [TeacherController::class, "register"]);
    Route::post("login", [TeacherController::class, "login"]);
    Route::get('avatar', [TeacherAvatarController::class, 'getAvatars']);
});

Route::post("Login", [SchoolController::class, "login"]);

Route::group([
    "middleware" => ["auth:sanctum"]
], function () {

    Route::prefix('student/')->group(function () {
        Route::get('list', [StudentController::class, 'StudentList']);
        Route::get("profile", [StudentController::class, "profile"]);
        Route::put('edit', [StudentController::class, 'updateProfile']);
        Route::delete('delete', [StudentController::class, 'deleteAccount']);
        Route::get("logout", [StudentController::class, "logout"]);
        Route::post('importExcel', [StudentController::class,'importExcelData']);
    });

    Route::prefix('teacher/')->group(function () {
        Route::get('list', [TeacherController::class, 'TeacherList']);
        Route::get("profile", [TeacherController::class, "profile"]);
        Route::put('edit', [TeacherController::class, 'updateProfile']);
        Route::delete('delete', [TeacherController::class, 'deleteAccount']);
        Route::get("logout", [TeacherController::class, "logout"]);
        Route::post('importExcel', [TeacherController::class,'importExcelData']);
    });
    Route::put('principalEdit', [PrincipalController::class, 'updateProfile']);
});








// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
