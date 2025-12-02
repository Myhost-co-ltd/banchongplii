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

        Student::create([
            'student_code' => $data['student_code'],
            'title'        => $data['title'] ?? '',
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'gender'       => $data['gender'] ?? 'ไม่ระบุ',
            'room'         => $data['room'] ?? null,
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

        $delimiter = $this->detectDelimiter($handle);

        $header = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNumber = 0;

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $rowNumber++;

            if ($rowNumber === 1) {
                $header = array_map(
                    fn ($value) => $this->normalizeHeaderValue($value),
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

        $student->update([
            'student_code' => $data['student_code'],
            'title'        => $data['title'] ?? '',
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'gender'       => $data['gender'] ?? 'ไม่ระบุ',
            'room'         => $data['room'] ?? null,
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

        $resolve = function (array $keys) use ($get) {
            foreach ($keys as $key) {
                $value = $get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $gender = trim((string) $resolve(['gender', 'เพศ']));

        $normalizedGender = match (mb_strtolower($gender)) {
            'ชาย', 'm', 'male', 'boy'   => 'ชาย',
            'หญิง', 'f', 'female', 'girl' => 'หญิง',
            'ไม่ระบุ', 'ไม่ระบุ.', 'not specified', 'not specified.' => 'ไม่ระบุ',
            default => 'ไม่ระบุ',
        };

        [$grade, $classroom] = $this->splitGradeAndClassroom(
            $resolve(['room', 'class', 'ห้อง', 'ชั้น', 'ระดับชั้น', 'ชั้นเรียน'])
        );

        return [
            'student_code' => $resolve(['student_code', 'code', 'รหัส', 'รหัสนักเรียน']),
            'title'        => $resolve(['title', 'คำนำหน้า', 'คำนำหน้านาม', 'prefix']) ?? '',
            'first_name'   => $resolve(['first_name', 'name', 'firstname', 'ชื่อ', 'ชื่อนักเรียน']),
            'last_name'    => $resolve(['last_name', 'lastname', 'surname', 'นามสกุล']),
            'gender'       => $normalizedGender,
            'room'         => $classroom ?: $grade,
        ];
    }

    private function detectDelimiter($handle): string
    {
        $delimiters = [',', ';', "\t"];

        $startPosition = ftell($handle);
        $sampleLine = '';

        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $sampleLine = $line;
                break;
            }
        }

        fseek($handle, $startPosition);

        if ($sampleLine === '') {
            return ',';
        }

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($sampleLine, $delimiter);
        }

        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    private function normalizeHeaderValue(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value); // remove UTF-8 BOM if present
        $value = trim(str_replace(["\r", "\n"], '', $value));

        return mb_strtolower($value);
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
