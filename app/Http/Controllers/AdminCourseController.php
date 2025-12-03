<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('teacher')
            ->latest()
            ->get();

        return view('admin.manage-courses', [
            'courses'  => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'  => ['nullable', 'exists:users,id'],
            'name'        => 'required|string|max:255',
            'grade'       => 'nullable|string|max:20',
            'rooms'       => 'nullable|array',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2',
            'year'        => ['nullable', 'integer', $this->yearBeNotPastRule()],
            'description' => 'nullable|string|max:5000',
        ]);

        $grade = $validated['grade'] ?? 'รอครูระบุ';
        $rooms = $validated['rooms'] ?? [];

        Course::create([
            'user_id'     => $validated['teacher_id'] ?? null,
            'name'        => $validated['name'],
            'grade'       => $grade,
            'rooms'       => $rooms,
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'สร้างหลักสูตรและมอบหมายครูเรียบร้อยแล้ว');
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'teacher_id'  => ['nullable', 'exists:users,id'],
            'name'        => 'required|string|max:255',
            'grade'       => 'nullable|string|max:20',
            'rooms'       => 'nullable|array',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2',
            'year'        => ['nullable', 'integer', $this->yearBeNotPastRule()],
            'description' => 'nullable|string|max:5000',
        ]);

        $grade = array_key_exists('grade', $validated) ? $validated['grade'] : $course->grade;
        $rooms = array_key_exists('rooms', $validated) ? ($validated['rooms'] ?? []) : ($course->rooms ?? []);

        $course->update([
            'user_id'     => $validated['teacher_id'] ?? $course->user_id,
            'name'        => $validated['name'],
            'grade'       => $grade,
            'rooms'       => $rooms,
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'อัปเดตหลักสูตรเรียบร้อยแล้ว');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'ลบหลักสูตรแล้ว');
    }

    public function updateAssignmentCap(Request $request, Course $course)
    {
        $data = $request->validate([
            'assignment_cap' => 'required|numeric|min:1|max:100',
        ]);

        $assignments = collect($course->assignments ?? []);
        $termTotals = $assignments
            ->groupBy(fn ($item) => $item['term'] ?? ($course->term ?? '1'))
            ->map(fn ($group) => $group->sum(fn ($item) => $item['score'] ?? 0));

        $currentMax = $termTotals->max() ?? 0;
        if ($data['assignment_cap'] < $currentMax) {
            return back()->withErrors([
                'assignment_cap' => 'เพดานที่ตั้งไว้ต่ำกว่าคะแนนรวมงานที่มีอยู่ (สูงสุดปัจจุบัน ' . $currentMax . ' คะแนน)',
            ]);
        }

        $course->update(['assignment_cap' => $data['assignment_cap']]);

        return back()->with('status', 'บันทึกเพดานคะแนนเก็บเรียบร้อยแล้ว');
    }

    public function storeTeachingHour(Request $request, Course $course)
    {
        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|max:255',
            'hours'    => 'required|numeric|min:0.1',
            'unit'     => 'required|string|max:50',
            'note'     => 'nullable|string|max:1000',
        ]);

        $hours = $course->teaching_hours ?? [];
        $hours[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'category' => $data['category'],
            'hours'    => $data['hours'],
            'unit'     => $data['unit'],
            'note'     => $data['note'] ?? null,
        ];

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'บันทึกชั่วโมงสอนเรียบร้อยแล้ว');
    }

    public function updateTeachingHour(Request $request, Course $course, string $hour)
    {
        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|max:255',
            'hours'    => 'required|numeric|min:0.1',
            'unit'     => 'required|string|max:50',
            'note'     => 'nullable|string|max:1000',
        ]);

        $hours = $course->teaching_hours ?? [];
        $updated = false;

        foreach ($hours as $index => $item) {
            if (($item['id'] ?? null) === $hour) {
                $hours[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'category' => $data['category'],
                    'hours'    => $data['hours'],
                    'unit'     => $data['unit'],
                    'note'     => $data['note'] ?? null,
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return back()->withErrors(['hour' => 'ไม่พบข้อมูลชั่วโมงที่ต้องการแก้ไข']);
        }

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'อัปเดตชั่วโมงสอนเรียบร้อยแล้ว');
    }

    public function destroyTeachingHour(Course $course, string $hour)
    {
        $hours = collect($course->teaching_hours ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $hour)
            ->values()
            ->all();

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'ลบชั่วโมงสอนแล้ว');
    }

    private function yearBeNotPastRule(): \Closure
    {
        $currentBe = now()->year + 543;

        return function ($attribute, $value, $fail) use ($currentBe) {
            if ($value === null || $value === '') {
                return;
            }

            if ($value < 2400) {
                $fail('ปีการศึกษาต้องระบุเป็น พ.ศ. เท่านั้น');
                return;
            }

            if ($value < $currentBe) {
                $fail("ปีการศึกษาต้องไม่ย้อนหลัง (ตั้งแต่ พ.ศ. {$currentBe} ขึ้นไป)");
            }
        };
    }
}
