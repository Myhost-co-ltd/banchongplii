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

        $userCount = User::count();
        $teacherCount = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->count()
            : 0;

        $classroomCount = Student::whereNotNull('classroom')
            ->distinct('classroom')
            ->count('classroom');

        return view('dashboards.admin', compact('userCount', 'teacherCount', 'classroomCount'));
    }
}
