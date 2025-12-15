<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $teacherCount = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->count()
            : 0;

        $studentCount = Student::count();

        $classroomCount = Student::whereNotNull('room')
            ->distinct('room')
            ->count('room');

        // นับครูที่มี/ไม่มีชั่วโมงสอนครบจากข้อมูลหลักสูตร พร้อมรายการชื่อครู
        $courses = Course::select('id', 'user_id', 'name', 'grade', 'teaching_hours', 'assignments')
            ->with('teacher:id,name,email')
            ->get();

        $teacherStatus = $courses
            ->filter(fn ($course) => $course->teacher)
            ->groupBy(fn ($course) => $course->teacher->id)
            ->map(function ($teacherCourses) {
                $hasIncompleteCourse = $teacherCourses->contains(function ($course) {
                    $hasHours = ! empty($course->teaching_hours);
                    $hasAssignments = ! empty($course->assignments);
                    return ! ($hasHours && $hasAssignments);
                });

                return [
                    'teacher' => $teacherCourses->first()->teacher,
                    'complete' => ! $hasIncompleteCourse,
                ];
            });

        $completeTeacherCount = $teacherStatus->filter(fn ($status) => $status['complete'])->count();
        $incompleteTeacherCount = $teacherStatus->filter(fn ($status) => ! $status['complete'])->count();

        $completeTeachers = $teacherStatus
            ->filter(fn ($status) => $status['complete'])
            ->pluck('teacher')
            ->filter();

        $teachersWithoutCourses = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)
                ->whereNotIn('id', $teacherStatus->keys())
                ->get(['id', 'name', 'email'])
            : collect();

        $incompleteTeachers = $teacherStatus
            ->filter(fn ($status) => ! $status['complete'])
            ->pluck('teacher')
            ->filter()
            ->merge($teachersWithoutCourses)
            ->values();

        $incompleteTeacherCount = $incompleteTeachers->count();

        return view('dashboards.admin', compact(
            'teacherCount',
            'studentCount',
            'classroomCount',
            'completeTeacherCount',
            'incompleteTeacherCount',
            'completeTeachers',
            'incompleteTeachers'
        ));
    }
}
