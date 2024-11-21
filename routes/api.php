<?php


use App\Http\Controllers\LoginController;
use App\Http\Controllers\PrincipalCtrl\Auth\PrincipalAvatarController;
use App\Http\Controllers\PrincipalCtrl\Auth\PrincipalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentCtrl\Auth\StudentAvatarController;
use App\Http\Controllers\StudentCtrl\Auth\StudentController;
use App\Http\Controllers\StudentCtrl\Dashboard\TimeGreetingController;
use App\Http\Controllers\StudentCtrl\Profile\StudentProfile;
use App\Http\Controllers\SuperadminCtrl\Auth\SchoolController;
use App\Http\Controllers\TeacherCtrl\Auth\TeacherAvatarController;
use App\Http\Controllers\TeacherCtrl\Auth\TeacherController;
use Illuminate\Support\Facades\Route;


Route::get('roles', [RoleController::class, 'GetRoles']);

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

Route::prefix('principal/')->group(function () {
    Route::post("regis", [PrincipalController::class, "register"]);
    Route::post("login", [PrincipalController::class, "login"]);
    Route::get('avatar', [PrincipalAvatarController::class, 'getAvatars']);
});

Route::post("loginSuperadmin", [SchoolController::class, "login"]);
Route::post("login", [LoginController::class, "login"]);


Route::group([
    "middleware" => ["auth:sanctum"]
], function () {

    Route::prefix('student/')->group(function () {
        Route::get('list', [StudentController::class, 'StudentList']);
        Route::get("profile", [StudentController::class, "profile"]);

        Route::prefix('edit/')->group(function () {
            Route::put('email', [StudentController::class,'edit_email']);
            Route::put('password', [StudentController::class,'edit_password']);
            Route::put('username', [StudentController::class,'edit_username']);
            Route::put('phone_number', [StudentController::class,'edit_phone_number']);
        });

        Route::prefix('dashboard/')->group(function () {
            Route::get('greeting', [TimeGreetingController::class,'greet']);
        });

        Route::delete('delete', [StudentController::class, 'deleteAccount']);
        Route::get("logout", [StudentController::class, "logout"]);
        Route::post('import', [StudentController::class,'importExcelData']);

    });

    Route::prefix('teacher/')->group(function () {
        Route::get('list', [TeacherController::class, 'TeacherList']);
        Route::get("profile", [TeacherController::class, "profile"]);
        Route::put('edit', [TeacherController::class, 'updateProfile']);
        Route::delete('delete', [TeacherController::class, 'deleteAccount']);
        Route::get("logout", [TeacherController::class, "logout"]);
        Route::post('import', [TeacherController::class,'importExcelData']);
    });

    Route::prefix('principal/')->group(function () {
        Route::get('list', [PrincipalController::class, 'TeacherList']);
        Route::get("profile", [PrincipalController::class, "profile"]);
        Route::put('edit', [PrincipalController::class, 'updateProfile']);
        Route::delete('delete', [PrincipalController::class, 'deleteAccount']);
        Route::get("logout", [PrincipalController::class, "logout"]);
        Route::post('import', [PrincipalController::class,'importExcelData']);
    });
});








// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
