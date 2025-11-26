<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    public function dashboard()
    {
        $studentCount = Student::count();

        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $teacherQuery = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)
            : User::query()->whereRaw('1 = 0');

        $teacherCount = $teacherQuery->count();

        $homeroomTeachers = $teacherQuery
            ? User::query()
                ->where('role_id', $teacherRoleId)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'major'])
            : collect();

        $classCount = Course::query()
            ->select('rooms')
            ->get()
            ->flatMap(fn ($course) => collect($course->rooms ?? []))
            ->filter(fn ($room) => $room !== null && $room !== '')
            ->unique()
            ->count();

        $majorPresets = [
            'คณิตศาสตร์',
            'วิทยาศาสตร์',
            'ภาษาไทย',
            'ภาษาอังกฤษ',
            'สังคมศึกษา',
            'สุขศึกษา/พลศึกษา',
            'ศิลปะ',
            'ดนตรี',
            'การงานอาชีพ',
            'คอมพิวเตอร์',
        ];

        $allMajors = collect($majorPresets)
            ->map(fn ($major) => trim($major))
            ->merge(
                $homeroomTeachers
                    ->pluck('major')
                    ->map(fn ($major) => trim((string) $major))
                    ->filter()
            )
            ->unique()
            ->values();

        $teachersByMajor = $homeroomTeachers
            ->filter(fn ($teacher) => $teacher->major)
            ->groupBy(fn ($teacher) => trim($teacher->major));

        $courses = Course::with('teacher:id,name,email')
            ->latest()
            ->get();

        // รวมครูจากรายวิชา (course) เข้ามาใน mapping วิชาเอก ด้วยชื่อวิชาเป็นกุญแจ
        foreach ($courses as $course) {
            $majorKey = trim((string) $course->name);
            if ($majorKey === '') {
                continue;
            }

            $current = collect($teachersByMajor->get($majorKey, collect()));

            if ($course->teacher) {
                $current = $current
                    ->push($course->teacher)
                    ->unique('id')
                    ->values();
            }

            // เก็บไว้แม้ยังไม่มีครู เพื่อให้วิชาเอกปรากฏครบตามชื่อรายวิชา
            $teachersByMajor->put($majorKey, $current);
        }

        // อัปเดตวิชาเอกทั้งหมดให้รวมชื่อรายวิชาที่มีอยู่จริงด้วย
        $allMajors = $allMajors
            ->merge(
                $courses
                    ->pluck('name')
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
            )
            ->unique()
            ->values();

        $teacherWithMajorCount = $teachersByMajor
            ->flatten(1)
            ->unique('id')
            ->count();

        return view('dashboards.director', [
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'classCount'   => $classCount,
            'homeroomTeachers' => $homeroomTeachers,
            'allMajors' => $allMajors,
            'teachersByMajor' => $teachersByMajor,
            'teacherWithMajorCount' => $teacherWithMajorCount,
            'courses' => $courses,
        ]);
    }

    public function teacherPlans(Request $request)
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $baseCourseQuery = Course::query()
            ->when($teacherRoleId, fn ($query) => $query->whereIn('user_id', function ($sub) use ($teacherRoleId) {
                $sub->select('id')->from('users')->where('role_id', $teacherRoleId);
            }));

        $search = trim((string) $request->input('q', ''));

        $courseOptions = (clone $baseCourseQuery)
            ->select('name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $selectedCourse = (string) $request->input('course', '');
        if ($selectedCourse && ! in_array($selectedCourse, $courseOptions, true)) {
            $selectedCourse = null;
        }

        $gradeOptions = $selectedCourse
            ? (clone $baseCourseQuery)
                ->where('name', $selectedCourse)
                ->select('grade')
                ->whereNotNull('grade')
                ->distinct()
                ->orderBy('grade')
                ->pluck('grade')
                ->all()
            : [];

        $selectedGrade = (string) $request->input('grade', '');
        if ($selectedGrade && ! in_array($selectedGrade, $gradeOptions, true)) {
            $selectedGrade = null;
        }

        $courses = (clone $baseCourseQuery)
            ->with(['teacher:id,name,email'])
            ->when($selectedCourse, fn ($query) => $query->where('name', $selectedCourse))
            ->when($selectedGrade, fn ($query) => $query->where('grade', $selectedGrade))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('grade', 'like', '%' . $search . '%')
                        ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', '%' . $search . '%'));
                });
            })
            // ต้องดึง teaching_hours และ assignments ด้วยเพื่อใช้คำนวณสถานะในหน้า view
            ->select(
                'id',
                'user_id',
                'name',
                'grade',
                'term',
                'year',
                'rooms',
                'description',
                'teaching_hours',
                'assignments'
            )
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        $teacherCount = $courses->pluck('teacher')->filter()->unique('id')->count();
        $studentCount = Student::count();
        $roomsCount = $courses->flatMap(fn ($course) => $course->rooms ?? [])->unique()->count();

        if ($request->ajax()) {
            $html = view('director.partials.teacher-plan-results', [
                'courses'       => $courses,
                'teacherCount'  => $teacherCount,
                'studentCount'  => $studentCount,
                'roomsCount'    => $roomsCount,
            ])->render();

            return response()->json([
                'html'           => $html,
                'gradeOptions'   => $gradeOptions,
                'selectedGrade'  => $selectedGrade,
                'selectedCourse' => $selectedCourse,
            ]);
        }

        return view('director.teacher-plans', [
            'courseOptions' => $courseOptions,
            'selectedCourse' => $selectedCourse,
            'gradeOptions'  => $gradeOptions,
            'selectedGrade' => $selectedGrade,
            'courses'       => $courses,
            'teacherCount'  => $teacherCount,
            'search'        => $search,
            'studentCount'  => $studentCount,
            'roomsCount'    => $roomsCount,
        ]);
    }

    public function courseDetail(Course $course)
    {
        $course->load('teacher:id,name,email');

        $studentCount = Student::count();
        $teacherCount = User::whereHas('role', fn ($q) => $q->where('name', 'teacher'))->count();
        $roomsCount = collect($course->rooms ?? [])->unique()->count();

        if (request()->ajax()) {
            $html = view('director.partials.course-detail-modal', [
                'course'       => $course,
                'studentCount' => $studentCount,
                'teacherCount' => $teacherCount,
                'roomsCount'   => $roomsCount,
                'hours'        => collect($course->teaching_hours ?? []),
                'lessons'      => collect($course->lessons ?? []),
                'assignments'  => collect($course->assignments ?? []),
            ])->render();

            return response()->json(['html' => $html]);
        }

        return view('director.course-detail', [
            'course'       => $course,
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'roomsCount'   => $roomsCount,
        ]);
    }
}
