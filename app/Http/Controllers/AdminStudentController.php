<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminStudentController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('student_code')->get();

        $rooms = Student::query()
            ->select('room')
            ->distinct()
            ->pluck('room')
            ->filter()
            ->values();

        $defaultClassrooms = collect(range(1, 6))->flatMap(function ($grade) {
            return collect(range(1, 10))->map(fn ($room) => "ป.$grade/$room");
        });

        $rooms = $defaultClassrooms
            ->merge($rooms)
            ->unique()
            ->values();

        return view('admin.add-student', compact('students', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_code' => 'required|string|max:20|unique:students,student_code',
            'title'        => 'nullable|string|max:20',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'gender'       => 'nullable|string|in:ชาย,หญิง,ไม่ระบุ',
            'room'         => 'nullable|string|max:20',
        ]);

        [$grade, $classroom] = $this->splitGradeAndClassroom($data['room'] ?? null);

        Student::create([
            'student_code' => $data['student_code'],
            'title'        => $data['title'] ?? '',
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'gender'       => $data['gender'] ?? 'ไม่ระบุ',
            'room'         => $grade,
            'classroom'    => $classroom,
        ]);

        return back()->with('status', 'บันทึกนักเรียนเรียบร้อยแล้ว');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['file' => 'ไม่สามารถเปิดไฟล์ได้']);
        }

        $header = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNumber = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;

            if ($rowNumber === 1) {
                $header = array_map(
                    fn ($value) => strtolower(trim($value)),
                    $row
                );
                continue;
            }

            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            $mapped = $this->mapRow($header, $row);

            if (! $mapped['student_code'] || ! $mapped['first_name'] || ! $mapped['last_name']) {
                $skipped++;
                continue;
            }

            $student = Student::updateOrCreate(
                ['student_code' => $mapped['student_code']],
                $mapped
            );

            $student->wasRecentlyCreated ? $created++ : $updated++;
        }

        fclose($handle);

        $message = "สรุปผล: เพิ่ม {$created} อัปเดต {$updated} ข้าม {$skipped}";

        return back()->with('status', $message);
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'student_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'student_code')->ignore($student->id),
            ],
            'title'        => 'nullable|string|max:20',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'gender'       => 'nullable|string|in:ชาย,หญิง,ไม่ระบุ',
            'room'         => 'nullable|string|max:20',
        ]);

        [$grade, $classroom] = $this->splitGradeAndClassroom($data['room'] ?? null);

        $student->update([
            'student_code' => $data['student_code'],
            'title'        => $data['title'] ?? '',
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'gender'       => $data['gender'] ?? 'ไม่ระบุ',
            'room'         => $grade,
        ]);

        return back()->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return back()->with('status', 'ลบนักเรียนเรียบร้อยแล้ว');
    }

    private function mapRow(array $header, array $row): array
    {
        $get = function (string $key) use ($header, $row) {
            $index = array_search($key, $header, true);
            if ($index === false || ! isset($row[$index])) {
                return null;
            }

            return trim($row[$index]);
        };

        $gender = trim((string) $get('gender'));

        $normalizedGender = match (mb_strtolower($gender)) {
            'ชาย', 'm', 'male', 'boy'   => 'ชาย',
            'หญิง', 'f', 'female', 'girl' => 'หญิง',
            'ไม่ระบุ', 'ไม่ระบุ.', 'not specified', 'not specified.' => 'ไม่ระบุ',
            default => 'ไม่ระบุ',
        };

        [$grade, $classroom] = $this->splitGradeAndClassroom($get('room') ?? $get('class'));

        return [
            'student_code' => $get('student_code') ?? $get('code'),
            'title'        => $get('title') ?? '',
            'first_name'   => $get('first_name') ?? $get('name') ?? $get('firstname'),
            'last_name'    => $get('last_name') ?? $get('lastname') ?? $get('surname'),
            'gender'       => $normalizedGender,
            'room'         => $grade,
            'classroom'    => $classroom,
        ];
    }

    private function splitGradeAndClassroom(?string $roomValue): array
    {
        if (! $roomValue) {
            return [null, null];
        }

        $parts = preg_split('/\s*\/\s*/', $roomValue, 2);
        $grade = $this->normalizeGrade($parts[0] ?? '');
        $classroom = $roomValue;

        return [$grade ?: null, $classroom];
    }

    private function normalizeGrade(string $grade): string
    {
        $clean = preg_replace('/\s+/u', '', $grade);
        if (! $clean) {
            return '';
        }

        $clean = preg_replace('/^(?:\x{0E21}|[MmPp])\.?/u', 'ป.', $clean);
        if (! str_contains($clean, '.')) {
            $clean = preg_replace('/^([^\d]+)(\d+)/u', '$1.$2', $clean);
        }

        return $clean;
    }
}
