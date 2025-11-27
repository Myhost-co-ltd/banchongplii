<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\AdminStudentController;

use App\Http\Controllers\DirectorController;

use App\Http\Controllers\ProfileController;


/*
|--------------------------------------------------------------------------
| HOME REDIRECT BY ROLE
|--------------------------------------------------------------------------
*/
Route::get('/', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $roleName = Auth::user()->role_name;

    switch ($roleName) {

        case 'superadmin':
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
| DASHBOARDS (AUTH USERS)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | TEACHER ROUTES
    |--------------------------------------------------------------------------
    */

    // Dashboard teacher
    Route::get('/dashboard/teacher', [StudentController::class, 'index'])
        ->name('dashboard.teacher');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Course create + list
    Route::get('/teacher/course/create', [TeacherCourseController::class, 'index'])
        ->name('teacher.course-create');
    Route::get('/teacher/courses', [TeacherCourseController::class, 'index'])
        ->name('teacher.courses');
    Route::post('/teacher/courses', [TeacherCourseController::class, 'store'])
        ->name('teacher.courses.store');
    Route::post('/teacher/courses/{course}/claim', [TeacherCourseController::class, 'claim'])
        ->name('teacher.courses.claim');
    Route::get('/teacher/courses/{course}/edit', [TeacherCourseController::class, 'edit'])
        ->name('teacher.courses.edit');
    Route::put('/teacher/courses/{course}', [TeacherCourseController::class, 'update'])
        ->name('teacher.courses.update');
    Route::delete('/teacher/courses/{course}', [TeacherCourseController::class, 'destroy'])
        ->name('teacher.courses.destroy');

    // Course nested resources
    Route::post('/teacher/courses/{course}/hours', [TeacherCourseController::class, 'storeTeachingHour'])
        ->name('teacher.courses.hours.store');
    Route::put('/teacher/courses/{course}/hours/{hour}', [TeacherCourseController::class, 'updateTeachingHour'])
        ->name('teacher.courses.hours.update');
    Route::delete('/teacher/courses/{course}/hours/{hour}', [TeacherCourseController::class, 'destroyTeachingHour'])
        ->name('teacher.courses.hours.destroy');

    Route::post('/teacher/courses/{course}/lessons', [TeacherCourseController::class, 'storeLesson'])
        ->name('teacher.courses.lessons.store');
    Route::put('/teacher/courses/{course}/lessons/{lesson}', [TeacherCourseController::class, 'updateLesson'])
        ->name('teacher.courses.lessons.update');
    Route::delete('/teacher/courses/{course}/lessons/{lesson}', [TeacherCourseController::class, 'destroyLesson'])
        ->name('teacher.courses.lessons.destroy');

    Route::post('/teacher/courses/{course}/assignments', [TeacherCourseController::class, 'storeAssignment'])
        ->name('teacher.courses.assignments.store');
    Route::put('/teacher/courses/{course}/assignments/{assignment}', [TeacherCourseController::class, 'updateAssignment'])
        ->name('teacher.courses.assignments.update');
    Route::delete('/teacher/courses/{course}/assignments/{assignment}', [TeacherCourseController::class, 'destroyAssignment'])
        ->name('teacher.courses.assignments.destroy');
    Route::get('/teacher/courses/{course}/export', [TeacherCourseController::class, 'export'])
        ->name('teacher.courses.export');

    // Select course page
    Route::get('/teacher/course/select', function () {
        $courses = [
            ['id' => 0, 'name' => 'คณิตศาสตร์พื้นฐาน ป.1'],
            ['id' => 1, 'name' => 'ภาษาไทยเพื่อการสื่อสาร ป.1'],
        ];
        return view('teacher.course-select', compact('courses'));
    })->name('course.select');

    // ⭐⭐ Correct: Show course detail (via Controller)
    Route::get('/teacher/course/{course?}', [TeacherCourseController::class, 'show'])
        ->name('course.detail');

    /*
    |--------------------------------------------------------------------------
    | DIRECTOR ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/director', function () {
        return view('dashboards.director');
    })->name('dashboard.director');


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/admin', AdminDashboardController::class)->name('dashboard.admin');


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
    Route::get('/teacher/students/export', [StudentController::class, 'export'])
        ->name('teacher.students.export');



    // DIRECTOR
    Route::get('/dashboard/director', [DirectorController::class, 'dashboard'])
        ->name('dashboard.director');

    Route::get('/director/teacher-plans', [DirectorController::class, 'teacherPlans'])
        ->name('director.teacher-plans');

    Route::get('/director/courses/{course}', [DirectorController::class, 'courseDetail'])
        ->name('director.course-detail');

    // Admin manage courses for teachers
    Route::middleware('role:superadmin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/courses', [AdminCourseController::class, 'index'])
                ->name('courses.index');
            Route::post('/courses', [AdminCourseController::class, 'store'])
                ->name('courses.store');
            Route::put('/courses/{course}', [AdminCourseController::class, 'update'])
                ->name('courses.update');
            Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])
                ->name('courses.destroy');
            Route::post('/courses/{course}/hours', [AdminCourseController::class, 'storeTeachingHour'])
                ->name('courses.hours.store');
            Route::put('/courses/{course}/hours/{hour}', [AdminCourseController::class, 'updateTeachingHour'])
                ->name('courses.hours.update');
            Route::delete('/courses/{course}/hours/{hour}', [AdminCourseController::class, 'destroyTeachingHour'])
                ->name('courses.hours.destroy');
        });

});


/*
|--------------------------------------------------------------------------
| SUPERADMIN PAGES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:superadmin'])->group(function () {

    Route::view('/admin/manage-users', 'admin.manage-users')->name('admin.manage-users');
    Route::get('/admin/add-student', [AdminStudentController::class, 'index'])->name('admin.add-student');
    Route::post('/admin/students', [AdminStudentController::class, 'store'])->name('admin.students.store');
    Route::post('/admin/students/import', [AdminStudentController::class, 'import'])->name('admin.students.import');
    Route::put('/admin/students/{student}', [AdminStudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{student}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::get('/admin/add-teacher', [AdminTeacherController::class, 'index'])->name('admin.add-teacher');
    Route::post('/admin/teachers', [AdminTeacherController::class, 'store'])->name('admin.teachers.store');
    Route::put('/admin/teachers/{teacher}', [AdminTeacherController::class, 'update'])->name('admin.teachers.update');
    Route::delete('/admin/teachers/{teacher}', [AdminTeacherController::class, 'destroy'])->name('admin.teachers.destroy');
});


/*
|--------------------------------------------------------------------------
| GENERAL PAGES (Teacher + Director)
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
