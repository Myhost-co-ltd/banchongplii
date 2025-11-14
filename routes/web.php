<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;

// ========================
//   AUTH
// ========================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========================
//   DASHBOARD BY ROLE
// ========================

Route::middleware(['auth'])->group(function () {

    // 🔹 Dashboard Superadmin
    Route::get('/dashboard/superadmin', function () {
        return view('dashboards.superadmin');
    })->name('dashboard.superadmin');

    // 🔹 Dashboard Teacher (ใช้หน้าเดิมของพี่)
    Route::get('/dashboard/teacher', [StudentController::class, 'index'])
        ->name('dashboard.teacher');

    // 🔹 Dashboard Director
    Route::get('/dashboard/director', function () {
        return view('dashboards.director');
    })->name('dashboard.director');
});

// ========================
//   หน้าอื่นๆ เดิมของพี่
// ========================

Route::get('/attendance', function () {
    return view('attendance');
})->middleware('auth')->name('attendance');

Route::get('/assignments', function () {
    return view('assignments');
})->middleware('auth')->name('assignments');

Route::get('/summary', function () {
    return view('summary');
})->middleware('auth')->name('summary');

Route::get('/chart-summary', function () {
    return view('chart-summary');
})->middleware('auth')->name('chart-summary');

Route::get('/course-structure', function () {
    return view('course-structure');
})->middleware('auth')->name('course-structure');

Route::get('/evaluation', function () {
    return view('evaluation');
})->middleware('auth')->name('evaluation');

