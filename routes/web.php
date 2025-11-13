<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');

Route::get('/dashboard', [StudentController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');


Route::get('/assignments', function () {
    return view('assignments');
})->middleware('auth')->name('assignments');

<<<<<<< HEAD
Route::get('/summary', function () {
    return view('summary');
})->name('summary');

Route::get('/chart-summary', function () {
    return view('chart-summary');
})->name('chart-summary');

Route::get('/course-structure', function () {
    return view('course-structure');
})->name('course-structure');

Route::get('/evaluation', function () {
    return view('evaluation');
})->name('evaluation');


=======
Route::post('/students', [StudentController::class, 'store'])
    ->middleware('auth')
    ->name('students.store');
>>>>>>> 2d5b2eb (บันทึกข้อมูลได้)


Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
