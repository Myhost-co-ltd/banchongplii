<?php

namespace App\Http\Controllers;

use App\Models\Role;
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

        return view('dashboards.admin', compact('teacherCount', 'studentCount', 'classroomCount'));
    }
}
