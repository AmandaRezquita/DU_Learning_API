<?php


use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PrincipalCtrl\Auth\PrincipalAvatarController;
use App\Http\Controllers\PrincipalCtrl\Auth\PrincipalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentCtrl\Auth\StudentController;
use App\Http\Controllers\StudentCtrl\Dashboard\SearchStudentController;
use App\Http\Controllers\SuperadminCtrl\Auth\SchoolController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\ClassController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\ClassSubjectController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\DeleteController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\DetailInforController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\EditController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\ScheduleController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\StudentaddClassController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\SubjectTeacherController;
use App\Http\Controllers\SuperadminCtrl\Dashboard\TotalController;
use App\Http\Controllers\TeacherCtrl\Auth\TeacherController;
use App\Http\Controllers\TeacherCtrl\Dashboard\MaterialsController;
use App\Http\Controllers\TeacherCtrl\Dashboard\SearchTeacherController;
use App\Http\Controllers\TimeGreetingController;
use Illuminate\Support\Facades\Route;


Route::get('roles', [RoleController::class, 'GetRoles']);

Route::prefix('student/')->group(function () {
    Route::post("regis", [StudentController::class, "register"]);
    Route::post("login", [StudentController::class, "login"]);
});

Route::prefix('teacher/')->group(function () {
    Route::post("regis", [TeacherController::class, "register"]);
    Route::post("login", [TeacherController::class, "login"]);
});

Route::prefix('principal/')->group(function () {
    Route::post("regis", [PrincipalController::class, "register"]);
    Route::post("login", [PrincipalController::class, "login"]);
});

Route::post("login/superadmin", [SchoolController::class, "login"]);
Route::post("login", [LoginController::class, "login"]);


Route::group([
    "middleware" => ["auth:sanctum"]
], function () {

    Route::prefix('superadmin/')->group(function () {

        Route::prefix('dashboard/')->group(function () {
    
            Route::post('create-class', [ClassController::class, 'createClass']);
            Route::post('create-subject', [ClassSubjectController::class, 'createSubject']);
            Route::post('add-teacher', [SubjectTeacherController::class, 'addTeacher']);
            Route::post('add-schedule', [ScheduleController::class, 'addSchedule']);
            Route::get('schedule/{class_id}', [ScheduleController::class, 'getSchedule']);
            Route::get('student', [SearchStudentController::class, 'SearchStudent']);
            Route::get('teacher', [SearchTeacherController::class, 'SearchTeacher']);
            Route::get('class', [ClassController::class, 'SearchClass']);
            Route::get('total', [TotalController::class, 'getTotal']);
            Route::post('add-student', [StudentaddClassController::class, 'addStudent']);
            Route::get('class-list', [ClassController::class, 'ClassList']);
            Route::get('subject-list/{class_id}', [ClassSubjectController::class, 'getSubject']);
            Route::get('teacher-subject-list/{class_id}/{subject_id}', [SubjectTeacherController::class, 'getTeacherSubject']);
            Route::get('student-list/{class_id}', [StudentaddClassController::class, 'getStudent']);

            Route::get('get-days', [ScheduleController::class, 'getDays']);
            Route::put('edit-schedule/{id}', [ScheduleController::class, 'updateSchedule']);

            Route::post('create-material', [MaterialsController::class, 'addMaterials']);
            Route::get('material-list/{class_id}/{subject_id}', [MaterialsController::class, 'getMaterials']);
            Route::put('edit-material/{id}', [MaterialsController::class, 'editMaterials']);

        });

        Route::prefix('delete/')->group(function () {
            Route::delete('student/{id}', [DeleteController::class, 'deleteStudent']);
            Route::delete('teacher/{id}', [DeleteController::class, 'deleteTeacher']);
            Route::delete('principal/{id}', [DeleteController::class, 'deletePrincipal']);
            Route::delete('class/{id}', [DeleteController::class, 'deleteClass']);
            Route::delete('class-teacher/{id}', [DeleteController::class, 'deleteTeacherClass']);
            Route::delete('class-student/{id}', [DeleteController::class, 'deleteStudentClass']);
            Route::delete('class-subject/{id}', [DeleteController::class, 'deleteClassSubject']);
            Route::delete('teacher-subject/{teacher_id}/{subject_id}', [DeleteController::class, 'deleteTeacherSubject']);
            Route::delete('schedule/{id}', [DeleteController::class, 'deleteSchedule']);
            Route::delete('material/{id}', [DeleteController::class, 'deleteMaterial']);
        });

        Route::prefix('detail/')->group(function () {
            Route::get('student/{id}', [DetailInforController::class, 'detailStudent']);
            Route::get('teacher/{id}', [DetailInforController::class, 'detailTeacher']);
            Route::get('principal/{id}', [DetailInforController::class, 'detailPrincipal']);
        });

        Route::prefix('edit/')->group(function () {
            Route::put('/student/{id}', [EditController::class, 'editStudent']);
            Route::put('/teacher/{id}', [EditController::class, 'editTeacher']);
            Route::put('/principal/{id}', [EditController::class, 'editPrincipal']);
        });
    
        Route::get('school-profile', [SchoolController::class, 'profile']);
    });

    Route::get("logout", [LogoutController::class, "logout"]);

    Route::prefix('student/')->group(function () {
        Route::get('list', [StudentController::class, 'StudentList']);
        Route::get("profile", [StudentController::class, "profile"]);

        Route::prefix('edit/')->group(function () {
            Route::put('email', [StudentController::class, 'edit_email']);
            Route::put('password', [StudentController::class, 'edit_password']);
            Route::put('username', [StudentController::class, 'edit_username']);
            Route::put('phone_number', [StudentController::class, 'edit_phone_number']);
        });

        Route::prefix('dashboard/')->group(function () {
            Route::get('greeting', [TimeGreetingController::class, 'greet']);
        });

        Route::delete('delete', [StudentController::class, 'deleteAccount']);
        Route::post('import', [StudentController::class, 'importExcelData']);

    });

    Route::prefix('teacher/')->group(function () {
        Route::get('list', [TeacherController::class, 'TeacherList']);
        Route::get("profile", [TeacherController::class, "profile"]);

        Route::prefix('edit/')->group(function () {
            Route::put('email', [TeacherController::class, 'edit_email']);
            Route::put('password', [TeacherController::class, 'edit_password']);
            Route::put('username', [TeacherController::class, 'edit_username']);
            Route::put('phone_number', [TeacherController::class, 'edit_phone_number']);
        });

        Route::prefix('dashboard/')->group(function () {
            Route::get('greeting', [TimeGreetingController::class, 'greet']);
        });

        Route::delete('delete', [TeacherController::class, 'deleteAccount']);
        Route::post('import', [TeacherController::class, 'importExcelData']);
    });

    Route::prefix('principal/')->group(function () {
        Route::get('list', [PrincipalController::class, 'PrincipalList']);
        Route::get("profile", [PrincipalController::class, "profile"]);

        Route::prefix('edit/')->group(function () {
            Route::put('email', [PrincipalController::class, 'edit_email']);
            Route::put('password', [PrincipalController::class, 'edit_password']);
            Route::put('username', [PrincipalController::class, 'edit_username']);
            Route::put('phone_number', [PrincipalController::class, 'edit_phone_number']);
        });

        Route::prefix('dashboard/')->group(function () {
            Route::get('greeting', [TimeGreetingController::class, 'greet']);
        });

        Route::delete('delete', [PrincipalController::class, 'deleteAccount']);
    });
});

