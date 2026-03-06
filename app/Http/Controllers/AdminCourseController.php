<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AdminCourseController extends Controller
{
    public function __construct()
    {
        Course::clearExpiredTemporaryAssignments();
    }

    public function index()
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $teachers = User::query()
            ->when($teacherRoleId, fn ($q) => $q->where('role_id', $teacherRoleId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $courses = Course::with(['teacher:id,name', 'temporaryTeacher:id,name'])
            ->latest()
            ->get();

        return view('admin.manage-courses', [
            'courses'  => $courses,
            'teachers' => $teachers,
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
            'term'        => 'nullable|in:1,2,summer',
            'year'        => ['nullable', 'integer', $this->yearBeNotPastRule()],
            'description' => 'nullable|string|max:5000',
        ]);

        $grade = $validated['grade'] ?? 'Ã Â¸Â£Ã Â¸Â­Ã Â¸â€žÃ Â¸Â£Ã Â¸Â¹Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Â¸';
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
            ->with('status', 'Ã Â¸ÂªÃ Â¸Â£Ã Â¹â€°Ã Â¸Â²Ã Â¸â€¡Ã Â¸Â«Ã Â¸Â¥Ã Â¸Â±Ã Â¸ÂÃ Â¸ÂªÃ Â¸Â¹Ã Â¸â€¢Ã Â¸Â£Ã Â¹ÂÃ Â¸Â¥Ã Â¸Â°Ã Â¸Â¡Ã Â¸Â­Ã Â¸Å¡Ã Â¸Â«Ã Â¸Â¡Ã Â¸Â²Ã Â¸Â¢Ã Â¸â€žÃ Â¸Â£Ã Â¸Â¹Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
    }

    public function update(Request $request, Course $course)
    {
        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $cancelTemporaryAssignment = $request->boolean('cancel_temporary_assignment');

        if ($cancelTemporaryAssignment) {
            $request->merge([
                'temporary_teacher_id' => null,
                'temporary_until' => null,
            ]);
        }

        $validated = $request->validate([
            'teacher_id'  => ['nullable', 'exists:users,id'],
            'temporary_teacher_id' => ['nullable', 'exists:users,id', 'required_with:temporary_until'],
            'temporary_until' => ['nullable', 'date', 'required_with:temporary_teacher_id', 'after_or_equal:' . $todayDate],
            'name'        => 'required|string|max:255',
            'grade'       => 'nullable|string|max:20',
            'rooms'       => 'nullable|array',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2,summer',
            'year'        => ['nullable', 'integer', $this->yearBeNotPastRule()],
            'description' => 'nullable|string|max:5000',
        ]);

        $grade = array_key_exists('grade', $validated) ? $validated['grade'] : $course->grade;
        $rooms = array_key_exists('rooms', $validated) ? ($validated['rooms'] ?? []) : ($course->rooms ?? []);
        $hadTemporaryAssignment = (bool) ($course->temporary_teacher_id && $course->temporary_until);
        $temporaryTeacherId = $validated['temporary_teacher_id'] ?? null;
        $temporaryUntil = $validated['temporary_until'] ?? null;

        if (! $temporaryTeacherId || ! $temporaryUntil) {
            $temporaryTeacherId = null;
            $temporaryUntil = null;
        }

        $course->update([
            'user_id'     => $validated['teacher_id'] ?? $course->user_id,
            'temporary_teacher_id' => $temporaryTeacherId,
            'temporary_until' => $temporaryUntil,
            'temporary_assigned_by' => $temporaryTeacherId ? Auth::id() : null,
            'name'        => $validated['name'],
            'grade'       => $grade,
            'rooms'       => $rooms,
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $statusMessage = 'à¸­à¸±à¸›à¹€à¸”à¸•à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§';
        if ($cancelTemporaryAssignment && $hadTemporaryAssignment) {
            $statusMessage = 'à¸¢à¸à¹€à¸¥à¸´à¸à¸à¸²à¸£à¸¡à¸­à¸šà¸«à¸¡à¸²à¸¢à¸„à¸£à¸¹à¹à¸—à¸™à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§';
        }

        return redirect()
            ->route('admin.courses.index')
            ->with('status', $statusMessage);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Ã Â¸Â¥Ã Â¸Å¡Ã Â¸Â«Ã Â¸Â¥Ã Â¸Â±Ã Â¸ÂÃ Â¸ÂªÃ Â¸Â¹Ã Â¸â€¢Ã Â¸Â£Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
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
                'assignment_cap' => 'Ã Â¹â‚¬Ã Â¸Å¾Ã Â¸â€Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸â€¢Ã Â¸Â±Ã Â¹â€°Ã Â¸â€¡Ã Â¹â€žÃ Â¸Â§Ã Â¹â€°Ã Â¸â€¢Ã Â¹Ë†Ã Â¸Â³Ã Â¸ÂÃ Â¸Â§Ã Â¹Ë†Ã Â¸Â²Ã Â¸â€žÃ Â¸Â°Ã Â¹ÂÃ Â¸â„¢Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â§Ã Â¸Â¡Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â¡Ã Â¸ÂµÃ Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë† (Ã Â¸ÂªÃ Â¸Â¹Ã Â¸â€¡Ã Â¸ÂªÃ Â¸Â¸Ã Â¸â€Ã Â¸â€ºÃ Â¸Â±Ã Â¸Ë†Ã Â¸Ë†Ã Â¸Â¸Ã Â¸Å¡Ã Â¸Â±Ã Â¸â„¢ ' . $currentMax . ' Ã Â¸â€žÃ Â¸Â°Ã Â¹ÂÃ Â¸â„¢Ã Â¸â„¢)',
            ]);
        }

        $course->update(['assignment_cap' => $data['assignment_cap']]);

        return back()->with('status', 'Ã Â¸Å¡Ã Â¸Â±Ã Â¸â„¢Ã Â¸â€”Ã Â¸Â¶Ã Â¸ÂÃ Â¹â‚¬Ã Â¸Å¾Ã Â¸â€Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€žÃ Â¸Â°Ã Â¹ÂÃ Â¸â„¢Ã Â¸â„¢Ã Â¹â‚¬Ã Â¸ÂÃ Â¹â€¡Ã Â¸Å¡Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
    }

    public function storeTeachingHour(Request $request, Course $course)
    {
        $data = $request->validate([
            'term'     => 'required|in:1,2,summer',
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

        return back()->with('status', 'Ã Â¸Å¡Ã Â¸Â±Ã Â¸â„¢Ã Â¸â€”Ã Â¸Â¶Ã Â¸ÂÃ Â¸Å Ã Â¸Â±Ã Â¹Ë†Ã Â¸Â§Ã Â¹â€šÃ Â¸Â¡Ã Â¸â€¡Ã Â¸ÂªÃ Â¸Â­Ã Â¸â„¢Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
    }

    public function updateTeachingHour(Request $request, Course $course, string $hour)
    {
        $data = $request->validate([
            'term'     => 'required|in:1,2,summer',
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
            return back()->withErrors(['hour' => 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸Å¾Ã Â¸Å¡Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸Å Ã Â¸Â±Ã Â¹Ë†Ã Â¸Â§Ã Â¹â€šÃ Â¸Â¡Ã Â¸â€¡Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸â€¢Ã Â¹â€°Ã Â¸Â­Ã Â¸â€¡Ã Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¹ÂÃ Â¸ÂÃ Â¹â€°Ã Â¹â€žÃ Â¸â€š']);
        }

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'Ã Â¸Â­Ã Â¸Â±Ã Â¸â€ºÃ Â¹â‚¬Ã Â¸â€Ã Â¸â€¢Ã Â¸Å Ã Â¸Â±Ã Â¹Ë†Ã Â¸Â§Ã Â¹â€šÃ Â¸Â¡Ã Â¸â€¡Ã Â¸ÂªÃ Â¸Â­Ã Â¸â„¢Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
    }

    public function destroyTeachingHour(Course $course, string $hour)
    {
        $hours = collect($course->teaching_hours ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $hour)
            ->values()
            ->all();

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'Ã Â¸Â¥Ã Â¸Å¡Ã Â¸Å Ã Â¸Â±Ã Â¹Ë†Ã Â¸Â§Ã Â¹â€šÃ Â¸Â¡Ã Â¸â€¡Ã Â¸ÂªÃ Â¸Â­Ã Â¸â„¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§');
    }

    private function yearBeNotPastRule(): \Closure
    {
        $currentBe = now(config('app.timezone', 'Asia/Bangkok'))->year + 543;

        return function ($attribute, $value, $fail) use ($currentBe) {
            if ($value === null || $value === '') {
                return;
            }

            $year = (int) $value;
            if ($year < 2400) {
                $fail('Ã Â¸ÂÃ Â¸Â£Ã Â¸Â¸Ã Â¸â€œÃ Â¸Â²Ã Â¸ÂÃ Â¸Â£Ã Â¸Â­Ã Â¸ÂÃ Â¸â€ºÃ Â¸ÂµÃ Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¸Â¨Ã Â¸Â¶Ã Â¸ÂÃ Â¸Â©Ã Â¸Â²Ã Â¹â‚¬Ã Â¸â€ºÃ Â¹â€¡Ã Â¸â„¢ Ã Â¸Å¾.Ã Â¸Â¨. (Ã Â¹â‚¬Ã Â¸Å Ã Â¹Ë†Ã Â¸â„¢ '.$currentBe.')');
                return;
            }

            if ($year !== $currentBe) {
                $fail("Ã Â¸â€ºÃ Â¸ÂµÃ Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¸Â¨Ã Â¸Â¶Ã Â¸ÂÃ Â¸Â©Ã Â¸Â²Ã Â¸â€¢Ã Â¹â€°Ã Â¸Â­Ã Â¸â€¡Ã Â¹â‚¬Ã Â¸â€ºÃ Â¹â€¡Ã Â¸â„¢Ã Â¸â€ºÃ Â¸ÂµÃ Â¸â€ºÃ Â¸Â±Ã Â¸Ë†Ã Â¸Ë†Ã Â¸Â¸Ã Â¸Å¡Ã Â¸Â±Ã Â¸â„¢Ã Â¹â‚¬Ã Â¸â€”Ã Â¹Ë†Ã Â¸Â²Ã Â¸â„¢Ã Â¸Â±Ã Â¹â€°Ã Â¸â„¢ (Ã Â¸Å¾.Ã Â¸Â¨. {$currentBe})");
            }
        };
    }
}

