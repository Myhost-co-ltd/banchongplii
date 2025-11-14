<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 🔹 Import Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✔ ตรวจสอบ User ที่ login อยู่
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ─────────────────────────────────────────────
// 🔹 Auth Routes
// ─────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ต้อง login ก่อนถึงจะ logout ได้
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);



// ─────────────────────────────────────────────
// 🔹 Superadmin เท่านั้น
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'index']);

    // เปลี่ยน role ของ user
    Route::post('/users/{id}/role', [RoleController::class, 'updateRole']);

    // ดึง role ทั้งหมด (ใช้ตอนทำ Dropdown)
    Route::get('/roles', [RoleController::class, 'allRoles']);
});


// ─────────────────────────────────────────────
// 🔹 Teacher เท่านั้น
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'index']);
});


// ─────────────────────────────────────────────
// 🔹 Director เท่านั้น
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:director'])->group(function () {
    Route::get('/director/dashboard', [DirectorController::class, 'index']);
});
