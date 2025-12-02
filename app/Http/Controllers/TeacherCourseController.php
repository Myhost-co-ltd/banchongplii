<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherCourseController extends Controller
{
    public function index()
    {
        $teacherMajor = Auth::user()->major ?? null;

        $courses = Course::where('user_id', Auth::id())
            ->latest()
            ->get();

        // รวมรายชื่อหลักสูตรทั้งหมด (ไม่จำกัดวิชาเอก) ให้เลือกหรือเป็นตัวอย่างค่าอัตโนมัติ
        $adminCourseOptions = Course::query()
            ->select('id', 'name', 'grade', 'term', 'year')
            ->orderBy('name')
            ->get();

        return view('teacher.course-create', [
            'courses' => $courses,
            'adminCourseOptions' => $adminCourseOptions,
            'teacherMajor' => $teacherMajor,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'grade'       => 'required|string|max:20',
            'rooms'       => 'required|array|min:1',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2',
            'year'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:5000',
        ]);

        $courseName = $validated['name'];

        Course::create([
            'user_id'     => Auth::id(),
            'name'        => $courseName,
            'grade'       => $validated['grade'],
            'rooms'       => $validated['rooms'],
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'สร้างหลักสูตรเรียบร้อยแล้ว');
    }

    public function claim(Request $request, Course $course)
    {
        if ($course->user_id) {
            abort(403, 'หลักสูตรนี้มีเจ้าของแล้ว');
        }

        $course->update(['user_id' => Auth::id()]);

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'เพิ่มหลักสูตรเข้าบัญชีของคุณแล้ว');
    }

    public function show(?int $courseId = null)
    {
        $courses = Course::where('user_id', Auth::id())
            ->latest()
            ->get();

        $course = $courses->firstWhere('id', $courseId) ?? $courses->first();

        if (! $course) {
            return redirect()
                ->route('teacher.course-create')
                ->with('status', 'ยังไม่มีหลักสูตร กรุณาสร้างหลักสูตรก่อนเข้าหน้ารายละเอียด');
        }

        $this->authorizeCourse($course);

        $selectedTerm = $this->resolveTerm($course, request('term'));
        $payload = $this->buildCoursePayload($course, $selectedTerm);

        return view('teacher.course-detail', [
            'course'        => $course,
            'courses'       => $courses,
            'selectedTerm'  => $selectedTerm,
            'hours'         => $payload['hours'],
            'lessons'       => $payload['lessons'],
            'assignments'   => $payload['assignments'],
            'lessonCapacity'=> $payload['lessonCapacity'],
            'assignmentTotal' => $payload['assignmentTotal'],
            'assignmentRemaining' => $payload['assignmentRemaining'],
            'lessonAllowedTotal' => $payload['lessonAllowedTotal'],
            'lessonUsedTotal' => $payload['lessonUsedTotal'],
            'lessonRemainingTotal' => $payload['lessonRemainingTotal'],
        ]);
    }

    public function export(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $selectedTerm = $this->resolveTerm($course, $request->input('term'));
        $payload = $this->buildCoursePayload($course, $selectedTerm);

        $fontPath = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');

        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $leelaRegularPath = "{$fontPath}/LeelawUI.ttf";
        $leelaBoldPath = "{$fontPath}/LeelaUIb.ttf";

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
                'fontDir' => $fontPath,
                'fontCache' => $fontCache,
                'tempDir' => $fontCache,
                'defaultFont' => 'LeelawUI',
                'enable_font_subsetting' => true,
            ])
            ->loadView('teacher.course-detail-pdf', array_merge($payload, [
                'course' => $course,
                'selectedTerm' => $selectedTerm,
                'teacher' => $request->user(),
            ]));

        // Register LeelawUI font explicitly so Dompdf does not fall back to an empty font key
        $metrics = $pdf->getDomPDF()->getFontMetrics();
        $metrics->registerFont([
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'normal',
        ], $leelaRegularPath);
        $metrics->registerFont([
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'bold',
        ], $leelaBoldPath);

        return $pdf->download('course-'.$course->id.'-term-'.$selectedTerm.'.pdf');
    }


    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        $rooms = $course->rooms ?? [];

        return view('teacher.course-edit', compact('course', 'rooms'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'grade'       => 'required|string|max:20',
            'rooms'       => 'required|array|min:1',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2',
            'year'        => ['nullable', 'integer', 'min:1', $this->yearNotInPastRule()],
            'description' => 'nullable|string|max:5000',
        ]);

        $course->update([
            'name'        => $validated['name'],
            'grade'       => $validated['grade'],
            'rooms'       => $validated['rooms'],
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('course.detail', $course)
            ->with('status', 'อัปเดตหลักสูตรเรียบร้อยแล้ว');
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        $course->delete();

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'ลบหลักสูตรเรียบร้อยแล้ว');
    }

    protected function authorizeCourse(Course $course): void
    {
        abort_unless($course->user_id === Auth::id(), 403);
    }

    protected function resolveTerm(Course $course, $input): string
    {
        $selectedTerm = in_array((string) $input, ['1', '2'], true)
            ? (string) $input
            : (string) ($course->term ?? '1');

        return $selectedTerm;
    }

    protected function buildCoursePayload(Course $course, string $selectedTerm): array
    {
        $hours = collect($course->teaching_hours ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $lessons = collect($course->lessons ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $assignments = collect($course->assignments ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $assignmentTotal = $assignments->sum(fn ($item) => $item['score'] ?? 0);
        $assignmentRemaining = max(0, 70 - $assignmentTotal);

        $lessonCapacity = [];
        $hourTargets = $hours->groupBy('category')->map(fn ($group) => (int) round($group->sum('hours')));
        $lessonUsed = $lessons->groupBy('category')->map(fn ($group) => (int) round($group->sum('hours')));

        foreach ($hourTargets as $category => $allowedHours) {
            $usedHours = $lessonUsed[$category] ?? 0;
            $remaining = max(0, $allowedHours - $usedHours);
            $lessonCapacity[$category] = [
                'allowed'   => $allowedHours,
                'used'      => $usedHours,
                'remaining' => $remaining,
            ];
        }

        $lessonAllowedTotal = (int) round($hourTargets->sum());
        $lessonUsedTotal = (int) round($lessonUsed->sum());
        $lessonRemainingTotal = max(0, $lessonAllowedTotal - $lessonUsedTotal);

        return [
            'hours' => $hours,
            'lessons' => $lessons,
            'assignments' => $assignments,
            'assignmentTotal' => $assignmentTotal,
            'assignmentRemaining' => $assignmentRemaining,
            'lessonCapacity' => $lessonCapacity,
            'lessonAllowedTotal' => $lessonAllowedTotal,
            'lessonUsedTotal' => $lessonUsedTotal,
            'lessonRemainingTotal' => $lessonRemainingTotal,
        ];
    }

    private function yearNotInPastRule(): \Closure
    {
        $currentYearAd = now(config('app.timezone', 'Asia/Bangkok'))->year;
        $currentYearBe = $currentYearAd + 543;

        return function ($attribute, $value, $fail) use ($currentYearBe) {
            if ($value === null || $value === '') {
                return;
            }

            $year = (int) $value;
            if ($year < 2400) {
                $fail('กรุณากรอกปีการศึกษาเป็น พ.ศ. (เช่น '.$currentYearBe.')');
                return;
            }

            if ($year < $currentYearBe) {
                $fail("ปีการศึกษาต้องไม่ย้อนหลัง (ตั้งแต่ พ.ศ. {$currentYearBe} เป็นต้นไป)");
            }
        };
    }

    public function storeTeachingHour(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
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

        return back()->with('status', 'บันทึกชั่วโมงการสอนเรียบร้อยแล้ว');
    }

    public function updateTeachingHour(Request $request, Course $course, string $hour)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
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
        $this->authorizeCourse($course);

        $hours = collect($course->teaching_hours ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $hour)
            ->values()
            ->all();

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'ลบชั่วโมงการสอนแล้ว');
    }

    public function storeLesson(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|in:ทฤษฎี,ปฏิบัติ',
            'title'    => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'period'   => 'nullable|string|max:100',
            'details'  => 'nullable|string|max:2000',
        ]);

        $this->guardLessonHours($course, $data['term'], $data['category'], $data['hours']);

        $lessons = $course->lessons ?? [];
        $lessons[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'category' => $data['category'],
            'title'    => $data['title'],
            'hours'    => $data['hours'],
            'period'   => $data['period'] ?? null,
            'details'  => $data['details'] ?? null,
            'created_at' => now(config('app.timezone'))->toDateTimeString(),
        ];

        $course->update(['lessons' => $lessons]);

        return back()->with('status', 'บันทึกหัวข้อเนื้อหาเรียบร้อยแล้ว');
    }

    public function destroyLesson(Course $course, string $lesson)
    {
        $this->authorizeCourse($course);

        $lessons = collect($course->lessons ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $lesson)
            ->values()
            ->all();

        $course->update(['lessons' => $lessons]);

        return back()->with('status', 'ลบหัวข้อเนื้อหาแล้ว');
    }

    public function updateLesson(Request $request, Course $course, string $lesson)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'category' => 'required|string|in:ทฤษฎี,ปฏิบัติ',
            'title'    => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'period'   => 'nullable|string|max:100',
            'details'  => 'nullable|string|max:2000',
        ]);

        $lessons = $course->lessons ?? [];
        $updated = false;

        foreach ($lessons as $index => $item) {
            if (($item['id'] ?? null) === $lesson) {
                $this->guardLessonHours(
                    $course,
                    $data['term'],
                    $data['category'],
                    $data['hours'],
                    (int) ($item['hours'] ?? 0),
                    (string) ($item['category'] ?? '')
                );

                $lessons[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'category' => $data['category'],
                    'title'    => $data['title'],
                    'hours'    => $data['hours'],
                    'period'   => $data['period'] ?? null,
                    'details'  => $data['details'] ?? null,
                    'updated_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return back()->withErrors(['lesson' => 'ไม่พบหัวข้อบทเรียนที่ต้องการอัปเดต']);
        }

        $course->update(['lessons' => $lessons]);

        return back()->with('status', 'อัปเดตหัวข้อเรียบร้อยแล้ว');
    }

    private function guardLessonHours(
        Course $course,
        string $term,
        string $category,
        int $incomingHours,
        int $existingHours = 0,
        string $existingCategory = ''
    ): void {
        $targetHours = (int) round(collect($course->teaching_hours ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $term)
            ->where('category', $category)
            ->sum('hours'));

        if ($targetHours <= 0) {
            throw ValidationException::withMessages([
                'lesson' => 'ชั่วโมงสอนหมวดนี้ยังไม่ถูกกำหนดในภาคเรียนที่เลือก',
            ]);
        }

        $usedHours = (int) round(collect($course->lessons ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $term)
            ->filter(fn ($item) => ($item['category'] ?? '') === $category)
            ->sum('hours'));

        // remove existing hours when editing same category
        if ($existingCategory === $category) {
            $usedHours -= $existingHours;
        }

        if ($usedHours + $incomingHours > $targetHours) {
            throw ValidationException::withMessages([
                'lesson' => 'ชั่วโมงรวมเกินกว่าที่กำหนดในหมวดนี้',
            ]);
        }
    }

    public function storeAssignment(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date|after_or_equal:today',
            'score'    => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string|max:2000',
        ]);

        $assignments = $course->assignments ?? [];

        $currentTotal = collect($assignments)
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $data['term'])
            ->sum(fn ($item) => $item['score'] ?? 0);

        $newScore = $data['score'] ?? 0;
        if (($currentTotal + $newScore) > 70) {
            return back()->withErrors([
                'score' => 'คะแนนรวมของชิ้นงานในภาคเรียนนี้ต้องไม่เกิน 70 (ปัจจุบันรวม ' . $currentTotal . ')',
            ])->withInput();
        }

        $assignments[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'title'    => $data['title'],
            'due_date' => $data['due_date'] ?? null,
            'score'    => $data['score'] ?? null,
            'notes'    => $data['notes'] ?? null,
            'created_at' => now(config('app.timezone'))->toDateTimeString(),
        ];

        $course->update(['assignments' => $assignments]);

        return back()->with('status', 'บันทึกการบ้านเรียบร้อยแล้ว');
    }

    public function updateAssignment(Request $request, Course $course, string $assignment)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2',
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date|after_or_equal:today',
            'score'    => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string|max:2000',
        ]);

        $assignments = $course->assignments ?? [];
        $updated = false;
        $targetItem = null;

        foreach ($assignments as $index => $item) {
            if (($item['id'] ?? null) === $assignment) {
                $targetItem = $item;
                break;
            }
        }

        if (! $targetItem) {
            return back()->withErrors(['assignment' => 'ไม่พบการบ้านที่ต้องการแก้ไข']);
        }

        $currentTotal = collect($assignments)
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $data['term'])
            ->sum(fn ($item) => $item['score'] ?? 0);

        // ถ้าอยู่ภาคเรียนเดิม ให้หักคะแนนเดิมออกก่อนคำนวณ
        if (($targetItem['term'] ?? null) === $data['term']) {
            $currentTotal -= $targetItem['score'] ?? 0;
        }

        $newScore = $data['score'] ?? 0;
        if (($currentTotal + $newScore) > 70) {
            return back()->withErrors([
                'score' => 'คะแนนรวมของชิ้นงานในภาคเรียนนี้ต้องไม่เกิน 70 (ปัจจุบันรวม ' . $currentTotal . ')',
            ])->withInput();
        }

        foreach ($assignments as $index => $item) {
            if (($item['id'] ?? null) === $assignment) {
                $assignments[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'title'    => $data['title'],
                    'due_date' => $data['due_date'] ?? null,
                    'score'    => $data['score'] ?? null,
                    'notes'    => $data['notes'] ?? null,
                    'updated_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return back()->withErrors(['assignment' => 'ไม่พบการบ้านที่ต้องการแก้ไข']);
        }

        $course->update(['assignments' => $assignments]);

        return back()->with('status', 'อัปเดตการบ้านเรียบร้อยแล้ว');
    }

    public function destroyAssignment(Course $course, string $assignment)
    {
        $this->authorizeCourse($course);

        $assignments = collect($course->assignments ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $assignment)
            ->values()
            ->all();

        $course->update(['assignments' => $assignments]);

        return back()->with('status', 'ลบการบ้านแล้ว');
    }
}
