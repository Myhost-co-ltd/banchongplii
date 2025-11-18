<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;


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

    Route::get('/teacher/course/create', function () {
        return view('teacher.course-create');
    })->name('teacher.course-create');
    Route::get('/teacher/course/{id}', function ($id) {
    return view('teacher.course-detail');  // หน้าแสดงรายละเอียดหลักสูตร
})->name('course.detail');
    Route::get('/teacher/courses', function () {
    return view('teacher.course-create');
})->name('teacher.courses');



    // DIRECTOR
    Route::get('/dashboard/director', function () {
        return view('dashboards.director');
    })->name('dashboard.director');
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
