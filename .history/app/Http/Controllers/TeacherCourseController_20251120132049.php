<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TeacherCourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('teacher.course-create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'grade'       => 'required|string|max:20',
            'rooms'       => 'required|array|min:1',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2',
            'year'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:5000',
        ]);

        Course::create([
            'user_id'     => Auth::id(),
            'name'        => $validated['name'],
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

    public function show(?Course $course = null)
    {
        $courses = Course::where('user_id', Auth::id())
            ->latest()
            ->get();

        if ($course) {
            $this->authorizeCourse($course);
        } else {
            $course = $courses->first();
        }

        return view('teacher.course-detail', [
            'course'  => $course,
            'courses' => $courses,
        ]);
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
            'year'        => 'nullable|string|max:10',
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

    public function storeTeachingHour(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'category' => 'required|string|max:255',
            'hours'    => 'required|numeric|min:0.1',
            'unit'     => 'required|string|max:50',
            'note'     => 'nullable|string|max:1000',
        ]);

        $hours = $course->teaching_hours ?? [];
        $hours[] = [
            'id'       => (string) Str::uuid(),
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
            'title'   => 'required|string|max:255',
            'hours'   => 'required|numeric|min:0.1',
            'period'  => 'required|string|max:100',
            'details' => 'nullable|string|max:2000',
        ]);

        $lessons = $course->lessons ?? [];
        $lessons[] = [
            'id'      => (string) Str::uuid(),
            'title'   => $data['title'],
            'hours'   => $data['hours'],
            'period'  => $data['period'],
            'details' => $data['details'] ?? null,
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
            'title'   => 'required|string|max:255',
            'hours'   => 'required|numeric|min:0.1',
            'period'  => 'required|string|max:100',
            'details' => 'nullable|string|max:2000',
        ]);

        $lessons = $course->lessons ?? [];
        $updated = false;

        foreach ($lessons as $index => $item) {
            if (($item['id'] ?? null) === $lesson) {
                $lessons[$index] = array_merge($item, [
                    'title'   => $data['title'],
                    'hours'   => $data['hours'],
                    'period'  => $data['period'],
                    'details' => $data['details'] ?? null,
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return back()->withErrors(['lesson' => '�1,�,��1^�,z�,s�,,�1%�,-�,��,1�,��,��,�,��,-�1?�,T�,��1%�,-�,��,��1?�,?�1%�1,�,,']);
        }

        $course->update(['lessons' => $lessons]);

        return back()->with('status', '�,-�,�,>�1?�,"�,�,��,�,�,,�1%�,-�1?�,T�,��1%�,-�,��,��1?�,��,�,��,s�,��1%�,-�,��1?�,��1%�,');
    }

    public function storeAssignment(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'due_date'=> 'nullable|date',
            'score'   => 'nullable|numeric|min:0',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $assignments = $course->assignments ?? [];
        $assignments[] = [
            'id'       => (string) Str::uuid(),
            'title'    => $data['title'],
            'due_date' => $data['due_date'] ?? null,
            'score'    => $data['score'] ?? null,
            'notes'    => $data['notes'] ?? null,
        ];

        $course->update(['assignments' => $assignments]);

        return back()->with('status', 'บันทึกการบ้านเรียบร้อยแล้ว');
    }

    public function updateAssignment(Request $request, Course $course, string $assignment)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'score'    => 'nullable|numeric|min:0',
            'notes'    => 'nullable|string|max:2000',
        ]);

        $assignments = $course->assignments ?? [];
        $updated = false;

        foreach ($assignments as $index => $item) {
            if (($item['id'] ?? null) === $assignment) {
                $assignments[$index] = array_merge($item, [
                    'title'    => $data['title'],
                    'due_date' => $data['due_date'] ?? null,
                    'score'    => $data['score'] ?? null,
                    'notes'    => $data['notes'] ?? null,
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
