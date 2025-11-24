<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\DirectorController;


/*
|--------------------------------------------------------------------------
| HOME REDIRECT BY ROLE
|--------------------------------------------------------------------------
*/
Route::get('/', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $roleName = optional(Auth::user()->role)->name;

    switch ($roleName) {

        case 'superadmin': // superadmin = admin ด้วย
            return redirect()->route('dashboard.admin');

        case 'teacher':
            return redirect()->route('dashboard.teacher');

        case 'director':
            return redirect()->route('dashboard.director');

        default:
            return redirect()->route('login');
    }
});



/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



/*
|--------------------------------------------------------------------------
| DASHBOARDS (ALL AUTH USERS)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // SUPERADMIN & ADMIN
    Route::get('/dashboard/admin', function () {
        return view('dashboards.admin');
    })->name('dashboard.admin');

    // TEACHER
    Route::get('/dashboard/teacher', [StudentController::class, 'index'])
        ->name('dashboard.teacher');

    Route::get('/teacher/course/create', [TeacherCourseController::class, 'index'])
        ->name('teacher.course-create');
    Route::post('/teacher/course', [TeacherCourseController::class, 'store'])
        ->name('teacher.courses.store');
    Route::get('/teacher/course/{course?}', [TeacherCourseController::class, 'show'])
        ->name('course.detail');
    Route::get('/teacher/course/{course}/edit', [TeacherCourseController::class, 'edit'])
        ->name('teacher.courses.edit');
    Route::put('/teacher/course/{course}', [TeacherCourseController::class, 'update'])
        ->name('teacher.courses.update');
    Route::delete('/teacher/course/{course}', [TeacherCourseController::class, 'destroy'])
        ->name('teacher.courses.destroy');
    Route::post('/teacher/course/{course}/hours', [TeacherCourseController::class, 'storeTeachingHour'])
        ->name('teacher.courses.hours.store');
    Route::put('/teacher/course/{course}/hours/{hour}', [TeacherCourseController::class, 'updateTeachingHour'])
        ->name('teacher.courses.hours.update');
    Route::delete('/teacher/course/{course}/hours/{hour}', [TeacherCourseController::class, 'destroyTeachingHour'])
        ->name('teacher.courses.hours.destroy');
    Route::post('/teacher/course/{course}/lessons', [TeacherCourseController::class, 'storeLesson'])
        ->name('teacher.courses.lessons.store');
    Route::put('/teacher/course/{course}/lessons/{lesson}', [TeacherCourseController::class, 'updateLesson'])
        ->name('teacher.courses.lessons.update');
    Route::delete('/teacher/course/{course}/lessons/{lesson}', [TeacherCourseController::class, 'destroyLesson'])
        ->name('teacher.courses.lessons.destroy');
    Route::post('/teacher/course/{course}/assignments', [TeacherCourseController::class, 'storeAssignment'])
        ->name('teacher.courses.assignments.store');
    Route::put('/teacher/course/{course}/assignments/{assignment}', [TeacherCourseController::class, 'updateAssignment'])
        ->name('teacher.courses.assignments.update');
    Route::delete('/teacher/course/{course}/assignments/{assignment}', [TeacherCourseController::class, 'destroyAssignment'])
        ->name('teacher.courses.assignments.destroy');



    // DIRECTOR
    Route::get('/dashboard/director', [DirectorController::class, 'dashboard'])
        ->name('dashboard.director');

    Route::get('/director/teacher-plans', [DirectorController::class, 'teacherPlans'])
        ->name('director.teacher-plans');

    Route::get('/director/courses/{course}', [DirectorController::class, 'courseDetail'])
        ->name('director.course-detail');
});



/*
|--------------------------------------------------------------------------
| SUPERADMIN PAGES (จัดการนักเรียน / ครู / ผู้ใช้)
|--------------------------------------------------------------------------
|
| *** แก้ให้เหลือ middleware ชั้นเดียว -> เสถียรที่สุด ***
|
*/
Route::middleware(['auth', 'role:superadmin'])->group(function () {

    Route::view('/admin/manage-users', 'admin.manage-users')
        ->name('admin.manage-users');

    Route::view('/admin/add-student', 'admin.add-student')
        ->name('admin.add-student');

    Route::view('/admin/add-teacher', 'admin.add-teacher')
        ->name('admin.add-teacher');
});



/*
|--------------------------------------------------------------------------
| OTHER GENERAL PAGES (สำหรับครู/ผู้บริหาร)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::view('/attendance', 'attendance')->name('attendance');
    Route::view('/assignments', 'assignments')->name('assignments');
    Route::view('/summary', 'summary')->name('summary');
    Route::view('/chart-summary', 'chart-summary')->name('chart-summary');
    Route::view('/course-structure', 'course-structure')->name('course-structure');
    Route::view('/evaluation', 'evaluation')->name('evaluation');
});
