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

        return back()->with('status', 'เพิ่มนักเรียนเรียบร้อยแล้ว');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['file' => 'ไม่สามารถอ่านไฟล์ได้']);
        }

        $header = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNumber = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;

            // read header
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

        $message = "นำเข้าเรียบร้อย: เพิ่มใหม่ {$created} รายการ, อัปเดต {$updated} รายการ, ข้าม {$skipped} รายการ";

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

        return back()->with('status', 'อัปเดตข้อมูลนักเรียนเรียบร้อยแล้ว');
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

        $gender = $get('gender');
        $normalizedGender = in_array($gender, ['ชาย', 'หญิง', 'ไม่ระบุ'], true) ? $gender : 'ไม่ระบุ';

        return [
            'student_code' => $get('student_code') ?? $get('code'),
            'title'        => $get('title') ?? '',
            'first_name'   => $get('first_name') ?? $get('name') ?? $get('firstname'),
            'last_name'    => $get('last_name') ?? $get('lastname') ?? $get('surname'),
            'gender'       => $normalizedGender,
            'room'         => $get('room') ?? $get('class'),
        ];
    }
}
