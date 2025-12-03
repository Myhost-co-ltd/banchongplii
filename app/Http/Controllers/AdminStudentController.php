<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

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
            'student_code' => 'nullable|string|max:20|unique:students,student_code',
            'title'        => 'nullable|string|max:20',
            'first_name'   => 'required|string|max:100|regex:/^(?!.*\d)[\p{L}\p{M}\s]+$/u',
            'last_name'    => [
                'required',
                'string',
                'max:100',
                'regex:/^(?!.*\d)[\p{L}\p{M}\s]+$/u',
                Rule::unique('students', 'last_name')->where(function ($q) use ($request) {
                    return $q->where('first_name', $request->input('first_name'))
                        ->where('title', $request->input('title'));
                }),
            ],
            'gender'       => 'nullable|string|in:ชาย,หญิง,ไม่ระบุ',
            'room'         => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->isValidRoomFormat($value)) {
                        $fail('ห้องต้องอยู่ในช่วง ป.1-ป.6 และห้อง 1-10 เช่น ป.1/1');
                    }
                },
            ],
        ], [
            'student_code.unique' => 'รหัสนักเรียนนี้มีอยู่ในระบบแล้ว',
            'student_code.max'    => 'รหัสนักเรียนต้องไม่เกิน 20 ตัวอักษร',
            'last_name.unique'    => 'ชื่อนักเรียนนี้มีอยู่ในระบบแล้ว',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required'  => 'กรุณากรอกนามสกุล',
            'first_name.regex'    => 'ชื่อต้องเป็นตัวอักษรเท่านั้น',
            'last_name.regex'     => 'นามสกุลต้องเป็นตัวอักษรเท่านั้น',
        ]);

        $this->validateTitleGenderCombo($data);
        $this->assertAlphabeticNames($data['first_name'], $data['last_name']);

        $studentCode = $data['student_code'] ?? '';
        if ($studentCode === '') {
            $studentCode = $this->nextStudentCodeForRoom($data['room'] ?? null);
        }

        Student::create([
            'student_code' => $studentCode,
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
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์นำเข้า',
            'file.file'     => 'ไฟล์ที่อัปโหลดไม่ถูกต้อง',
            'file.mimes'    => 'ไฟล์ต้องเป็น CSV หรือ Excel (.csv, .xlsx)',
        ]);

        $path = $request->file('file')->getRealPath();
        $extension = strtolower($request->file('file')->getClientOriginalExtension());

        $header = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNumber = 0;
        $errors = [];

        $headerDetected = false;
        $headerHasNameColumns = false;

        // cache next code per room during this import session to avoid duplicates
        $nextCodeCache = [];

        DB::beginTransaction();
        try {
            $rows = $extension === 'xlsx'
                ? $this->readXlsxRows($path)
                : $this->readCsvRows($path);

            foreach ($rows as $row) {
                $rowNumber++;

                // First non-empty row: detect header. If it doesn't look like a header,
                // fall back to positional mapping and treat this row as data.
                if (! $headerDetected) {
                    $normalizedHeader = array_map(
                        fn ($value) => $this->normalizeHeaderValue($value),
                        $row
                    );

                    if ($this->hasNameColumns($normalizedHeader)) {
                        $header = $normalizedHeader;
                        $headerDetected = true;
                        $headerHasNameColumns = true;
                        continue;
                    }

                    $header = $this->buildSyntheticHeader(count($row));
                    $headerDetected = true;
                    $headerHasNameColumns = $this->hasNameColumns($header);
                    // do not continue; use this row as data
                }

                // Skip completely empty rows without counting as error
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $mapped = $this->mapRow($header, $row);

                // บางไฟล์อาจลบแถวหัวคอลัมน์ออกหรือใส่ช่องว่างพิเศษจนตรวจจับ header ไม่ได้
                // หากแถวนี้ดูคล้าย header ให้ข้ามไป ไม่ต้องรายงานเป็น error
                if ($this->looksLikeHeaderRow($row, $header)) {
                    continue;
                }

                // เผื่อกรณี mapping ไม่ตรง index (เช่น header ไม่ถูก normalize)
                // เติมข้อมูลจากตำแหน่งมาตรฐานถ้ายังว่าง
                $mapped = $this->fillMissingNamesFromDefaultColumns($mapped, $row);
                $mapped = $this->fillByPositionFallback($mapped, $row);

                if (! $mapped['first_name'] || ! $mapped['last_name']) {
                    $skipped++;
                    if (! $headerHasNameColumns) {
                        $errors[] = "แถว {$rowNumber}: ไม่พบคอลัมน์ชื่อ/นามสกุลหรือแถวหัวตารางไม่ถูกต้อง (แถว 1 ต้องเป็นหัวคอลัมน์)";
                    } else {
                        $first = $mapped['first_name'] ?? '';
                        $last  = $mapped['last_name'] ?? '';
                        $rowPreview = implode(' | ', array_filter(array_map(
                            fn ($v) => trim((string) $v),
                            $row
                        ), fn ($v) => $v !== ''));
                        $errors[] = "แถว {$rowNumber}: ชื่อหรือนามสกุลว่าง (อ่านได้: ชื่อ=\"{$first}\", นามสกุล=\"{$last}\"; แถวนี้: {$rowPreview})";
                    }
                    continue;
                }

                if (! $this->isValidName($mapped['first_name']) || ! $this->isValidName($mapped['last_name'])) {
                    $skipped++;
                    $errors[] = "แถว {$rowNumber}: ชื่อ-นามสกุลต้องเป็นตัวอักษรเท่านั้น";
                    continue;
                }

                if (empty($mapped['room'])) {
                    $skipped++;
                    $errors[] = "แถว {$rowNumber}: ต้องระบุห้อง (ป.1-ป.6 / ห้อง 1-10 เช่น ป.1/1)";
                    continue;
                }

                if (! $this->isValidRoomFormat($mapped['room'])) {
                    $skipped++;
                    $errors[] = "แถว {$rowNumber}: ห้องต้องอยู่ระหว่าง ป.1-ป.6 และห้อง 1-10 (เช่น ป.1/1)";
                    continue;
                }

                if (empty($mapped['student_code'])) {
                    $mapped['student_code'] = $this->nextStudentCodeForRoom($mapped['room'] ?? null, $nextCodeCache);
                }

                $student = Student::updateOrCreate(
                    ['student_code' => $mapped['student_code']],
                    $mapped
                );

                $student->wasRecentlyCreated ? $created++ : $updated++;
            }

            if (! empty($errors)) {
                // commit ที่เพิ่ม/อัปเดตได้ แล้วแจ้งเตือนแถวที่ข้าม
                DB::commit();
                $message = "สรุปผล: เพิ่ม {$created} อัปเดต {$updated} ข้าม {$skipped}";
                return back()
                    ->withErrors(['file' => $errors])
                    ->with('status', $message);
            }

            DB::commit();

            $message = "สรุปผล: เพิ่ม {$created} อัปเดต {$updated} ข้าม {$skipped}";
            return back()->with('status', $message);
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors());
        }
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
            'first_name'   => 'required|string|max:100|regex:/^(?!.*\d)[\p{L}\p{M}\s]+$/u',
            'last_name'    => [
                'required',
                'string',
                'max:100',
                'regex:/^(?!.*\d)[\p{L}\s]+$/u',
                Rule::unique('students', 'last_name')
                    ->where(function ($q) use ($request, $student) {
                        return $q->where('first_name', $request->input('first_name'))
                            ->where('title', $request->input('title'))
                            ->where('id', '!=', $student->id);
                    }),
            ],
            'gender'       => 'nullable|string|in:ชาย,หญิง,ไม่ระบุ',
            'room'         => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->isValidRoomFormat($value)) {
                        $fail('ห้องต้องอยู่ในช่วง ป.1-ป.6 และห้อง 1-10 เช่น ป.1/1');
                    }
                },
            ],
        ], [
            'student_code.unique' => 'รหัสนักเรียนนี้มีอยู่ในระบบแล้ว',
            'student_code.max'    => 'รหัสนักเรียนต้องไม่เกิน 20 ตัวอักษร',
            'last_name.unique'    => 'ชื่อนักเรียนนี้มีอยู่ในระบบแล้ว',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required'  => 'กรุณากรอกนามสกุล',
            'first_name.regex'    => 'ชื่อต้องเป็นตัวอักษรเท่านั้น',
            'last_name.regex'     => 'นามสกุลต้องเป็นตัวอักษรเท่านั้น',
        ]);

        $this->validateTitleGenderCombo($data);
        $this->assertAlphabeticNames($data['first_name'], $data['last_name']);

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

    /**
     * ตรวจสอบว่ามี column header เกี่ยวกับชื่อ-นามสกุลหรือไม่
     */
    private function hasNameColumns(array $header): bool
    {
        $firstKeys = ['first_name', 'name', 'firstname', 'ชื่อ', 'ชื่อนักเรียน'];
        $lastKeys  = ['last_name', 'lastname', 'surname', 'นามสกุล'];

        return (bool) (array_intersect($header, $firstKeys))
            && (bool) (array_intersect($header, $lastKeys));
    }

    /**
     * ใช้ header แบบ default เมื่อตรวจไม่พบ header ในไฟล์ (เช่น ผู้นำเข้าเอาแถวหัวออก)
     */
    private function buildSyntheticHeader(int $columnCount): array
    {
        if ($columnCount >= 6) {
            return ['student_code', 'title', 'first_name', 'last_name', 'gender', 'room'];
        }
        if ($columnCount === 5) {
            return ['student_code', 'first_name', 'last_name', 'gender', 'room'];
        }
        if ($columnCount === 4) {
            return ['student_code', 'first_name', 'last_name', 'room'];
        }
        if ($columnCount === 3) {
            return ['first_name', 'last_name', 'room'];
        }

        return array_slice(['first_name', 'last_name'], 0, max(1, $columnCount));
    }

    /**
     * ถ้าแถวข้อมูลดูเหมือนแถว header (เช่น มีคำว่า first_name, last_name, ชื่อ, นามสกุล ฯลฯ)
     * ให้ข้ามแถวนี้ไปเพื่อไม่ให้เกิด error "ชื่อหรือนามสกุลว่าง"
     */
    private function looksLikeHeaderRow(array $row, array $header): bool
    {
        $knownLabels = [
            'student_code', 'code', 'รหัส', 'รหัสนักเรียน',
            'title', 'คำนำหน้า', 'คำนำหน้านาม', 'prefix',
            'first_name', 'name', 'firstname', 'ชื่อ', 'ชื่อนักเรียน',
            'last_name', 'lastname', 'surname', 'นามสกุล',
            'gender', 'เพศ',
            'room', 'class', 'ห้อง', 'ชั้น', 'ระดับชั้น', 'ชั้นเรียน',
        ];

        $normalize = fn ($v) => $this->normalizeHeaderValue((string) $v);

        $rowValues     = array_map($normalize, $row);
        $headerValues  = array_map($normalize, $header);
        $labelMatches  = array_intersect($rowValues, $knownLabels);
        $headerMatches = array_intersect($rowValues, $headerValues);

        return count($labelMatches) >= 2 || count($headerMatches) >= 2;
    }

    /**
     * เติมข้อมูลจากตำแหน่งมาตรฐาน (code,title,first,last,gender,room) หากยังว่าง
     */
    private function fillMissingNamesFromDefaultColumns(array $mapped, array $row): array
    {
        $indices = [
            'student_code' => 0,
            'title'        => 1,
            'first_name'   => 2, // คอลัมน์ C
            'last_name'    => 3, // คอลัมน์ D
            'gender'       => 4,
            'room'         => 5,
        ];

        foreach ($indices as $key => $idx) {
            if (empty($mapped[$key]) && isset($row[$idx])) {
                $mapped[$key] = trim((string) $row[$idx]);
            }
        }

        return $mapped;
    }

    /**
     * fallback เพิ่มเติม: map ตามลำดับคอลัมน์ (code, title, first, last, gender, room)
     * เผื่อกรณี header เพี้ยนหรือมีอักขระพิเศษ
     */
    private function fillByPositionFallback(array $mapped, array $row): array
    {
        $values = array_values($row);
        $get = function (int $i) use ($values): string {
            return isset($values[$i]) ? trim((string) $values[$i]) : '';
        };

        if (empty($mapped['first_name'])) {
            foreach ([2, 1] as $i) { // ปกติอยู่คอลัมน์ C; ถัดไปลองคอลัมน์ B
                $val = $get($i);
                if ($val !== '') {
                    $mapped['first_name'] = $val;
                    break;
                }
            }
        }

        if (empty($mapped['last_name'])) {
            foreach ([3, 2] as $i) { // ปกติอยู่คอลัมน์ D; ถัดไปลองคอลัมน์ C
                $val = $get($i);
                if ($val !== '') {
                    $mapped['last_name'] = $val;
                    break;
                }
            }
        }

        if (empty($mapped['student_code'])) {
            $val = $get(0);
            if ($val !== '') {
                $mapped['student_code'] = $val;
            }
        }

        if (empty($mapped['title'])) {
            $val = $get(1);
            if ($val !== '') {
                $mapped['title'] = $val;
            }
        }

        if (empty($mapped['gender'])) {
            $val = $get(4);
            if ($val !== '') {
                $mapped['gender'] = $val;
            }
        }

        if (empty($mapped['room'])) {
            $val = $get(5);
            if ($val !== '') {
                $mapped['room'] = $val;
            }
        }

        return $mapped;
    }

    /**
     * สร้างรหัสนักเรียนถัดไปจากข้อมูลในฐาน + cache ระหว่าง import
     */
    private function nextStudentCodeForRoom(?string $room, array &$cache = null): string
    {
        $key = $room && trim($room) !== '' ? trim($room) : '_all';

        // ใช้ cache ในรอบ import เพื่อลด query และกันรหัสซ้ำในไฟล์เดียวกัน
        if ($cache !== null && isset($cache[$key])) {
            [$currentMax, $pad] = $cache[$key];
        } else {
            $query = Student::query();
            if ($room && trim($room) !== '') {
                $query->where('room', trim($room));
            }

            $maxCode = $query->max('student_code');
            [$currentMax, $pad] = $this->extractNumericParts($maxCode);
        }

        $next = $currentMax + 1;
        $pad  = max($pad, 5);

        if ($cache !== null) {
            $cache[$key] = [$next, $pad];
        }

        return str_pad((string) $next, $pad, '0', STR_PAD_LEFT);
    }

    private function extractNumericParts(?string $code): array
    {
        if ($code && preg_match('/(\d+)/', $code, $m)) {
            return [(int) $m[1], strlen($m[1])];
        }

        return [0, 5];
    }

    /**
     * ตรวจสอบคำนำหน้ากับเพศให้สอดคล้องกัน
     */
    private function validateTitleGenderCombo(array $data): void
    {
        $gender = $data['gender'] ?? '';
        $title  = trim((string) ($data['title'] ?? ''));

        if ($gender === '' || $gender === 'ไม่ระบุ' || $title === '') {
            return;
        }

        $maleTitles   = ['นาย', 'ดช', 'ด.ช.', 'เด็กชาย'];
        $femaleTitles = ['นางสาว', 'น.ส.', 'ดญ', 'ด.ญ.', 'เด็กหญิง'];

        $isMale   = $gender === 'ชาย';
        $isFemale = $gender === 'หญิง';

        $valid = true;
        if ($isMale) {
            $valid = in_array($title, $maleTitles, true);
        } elseif ($isFemale) {
            $valid = in_array($title, $femaleTitles, true);
        }

        if (! $valid) {
            $message = $isMale
                ? 'เพศชาย ต้องใช้คำนำหน้า นาย หรือ ดช.'
                : 'เพศหญิง ต้องใช้คำนำหน้า นางสาว หรือ ดญ.';

            throw ValidationException::withMessages([
                'title' => $message,
            ]);
        }
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

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw ValidationException::withMessages(['file' => ['ไม่สามารถเปิดไฟล์ได้']]);
        }

        $delimiter = $this->detectDelimiter($handle);
        $rows = [];
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw ValidationException::withMessages(['file' => ['ไฟล์ว่างหรืออ่านข้อมูลไม่ได้']]);
        }

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => ['ไม่สามารถเปิดไฟล์ Excel ได้']]);
        }

        // รวบรวมทุก worksheet (sheet1, sheet2, ...)
        $sheetPaths = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet\\d+\\.xml$#', $name)) {
                $sheetPaths[] = $name;
            }
        }

        if (empty($sheetPaths)) {
            $zip->close();
            throw ValidationException::withMessages(['file' => ['ไม่พบข้อมูลแผ่นงานในไฟล์ Excel']]);
        }

        // shared strings สำหรับทุก sheet
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $shared = simplexml_load_string($sharedStringsXml);
            if ($shared && isset($shared->si)) {
                foreach ($shared->si as $si) {
                    $text = '';
                    foreach ($si->xpath('.//t') as $t) {
                        $text .= (string) $t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $bestRows = [];
        $bestScore = -1;
        $bestHeaderMatch = false;

        foreach ($sheetPaths as $sheetPath) {
            $sheetXml = $zip->getFromName($sheetPath);
            if ($sheetXml === false) {
                continue;
            }

            // Parse with DOM to avoid namespace issues
            $dom = new \DOMDocument();
            if (! @$dom->loadXML($sheetXml)) {
                continue;
            }
            $xpath = new \DOMXPath($dom);

            $rows = [];
            foreach ($xpath->query('//*[local-name()="row"]') as $rowEl) {
                $cells = [];
                $nextIndex = 0;

                foreach ($xpath->query('.//*[local-name()="c"]', $rowEl) as $c) {
                    /** @var \DOMElement $c */
                    $ref = (string) $c->getAttribute('r');
                    $letters = preg_replace('/\d+/', '', $ref);
                    $colIndex = $this->columnIndexFromLetters($letters, $nextIndex);
                    $nextIndex = $colIndex + 1;

                    $type = (string) $c->getAttribute('t');
                    $value = '';
                    if ($type === 'inlineStr') {
                        // <is><t> or rich text <is><r><t>
                        $tNodes = $xpath->query('.//*[local-name()="is"]//*[local-name()="t"]', $c);
                        $parts = [];
                        foreach ($tNodes as $tNode) {
                            $parts[] = $tNode->textContent;
                        }
                        $value = implode('', $parts);
                    } elseif ($type === 's') {
                        $vNode = $xpath->query('.//*[local-name()="v"]', $c)->item(0);
                        $idx = $vNode ? (int) $vNode->textContent : 0;
                        $value = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'str') {
                        $vNode = $xpath->query('.//*[local-name()="v"]', $c)->item(0);
                        $value = $vNode ? $vNode->textContent : '';
                    } else {
                        $vNode = $xpath->query('.//*[local-name()="v"]', $c)->item(0);
                        $value = $vNode ? $vNode->textContent : '';
                    }

                    $cells[$colIndex] = trim($value);
                }

                if (! empty($cells)) {
                    ksort($cells);
                    $maxIndex = max(array_keys($cells));
                    $rowValues = [];
                    for ($i = 0; $i <= $maxIndex; $i++) {
                        $rowValues[] = $cells[$i] ?? '';
                    }
                    $rows[] = $rowValues;
                }
            }

            if (! empty($rows)) {
                // หาหัวแถวแรกที่ไม่ว่าง เพื่อตรวจว่ามีคอลัมน์ชื่อ/นามสกุลครบไหม
                $firstNonEmpty = collect($rows)->first(fn ($r) => count(array_filter($r, fn ($v) => trim((string) $v) !== '')) > 0) ?? [];
                $normalizedHeader = array_map(
                    fn ($v) => $this->normalizeHeaderValue((string) $v),
                    $firstNonEmpty
                );

                if ($this->hasNameColumns($normalizedHeader)) {
                    // ถ้าเจอชีตที่มี header ชื่อ/นามสกุลครบ เลือกชีตนี้ทันที
                    $bestRows = $rows;
                    $bestHeaderMatch = true;
                    break;
                }

                // ถ้ายังไม่เจอ header ที่ต้องการ เลือกชีตที่มีจำนวนเซลล์ไม่ว่างมากที่สุด
                $score = max(array_map(fn ($r) => count(array_filter($r, fn ($v) => trim((string) $v) !== '')), $rows));
                if (! $bestHeaderMatch && $score > $bestScore) {
                    $bestScore = $score;
                    $bestRows = $rows;
                }
            }
        }

        $zip->close();

        if (empty($bestRows)) {
            throw ValidationException::withMessages(['file' => ['ไฟล์ Excel ไม่มีข้อมูลที่นำเข้าได้']]);
        }

        return $bestRows;
    }

    private function columnIndexFromLetters(?string $letters, int $fallback): int
    {
        if (! $letters) {
            return $fallback;
        }

        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeHeaderValue(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value); // remove UTF-8 BOM if present
        $value = str_replace("\xC2\xA0", ' ', $value); // normalize non-breaking space
        $value = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{FEFF}]/u', '', $value); // remove zero width chars
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

        // คืนค่าเป็น null ถ้ารูปแบบห้องไม่ผ่านเงื่อนไข (ป.1-6 ห้อง 1-10)
        if (! $this->isValidRoomFormat($classroom)) {
            return [null, null];
        }

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

    private function isValidName(string $name): bool
    {
        // Allow letters plus combining marks (Thai tone/vowel marks) and whitespace
        return preg_match('/^(?!.*\d)[\p{L}\p{M}\s]+$/u', $name) === 1;
    }

    private function isValidRoomFormat(?string $room): bool
    {
        if (! $room) return false;
        if (! preg_match('/^ป\.(\d)\/(\d{1,2})$/u', trim($room), $m)) {
            return false;
        }
        $gradeNum = (int) $m[1];
        $roomNum  = (int) $m[2];
        return $gradeNum >= 1 && $gradeNum <= 6 && $roomNum >= 1 && $roomNum <= 10;
    }

    private function assertAlphabeticNames(string $firstName, string $lastName): void
    {
        $pattern = '/^(?!.*\d)[\p{L}\p{M}\s]+$/u';
        $errors = [];
        if (! preg_match($pattern, $firstName)) {
            $errors['first_name'] = 'ชื่อต้องเป็นตัวอักษรเท่านั้น';
        }
        if (! preg_match($pattern, $lastName)) {
            $errors['last_name'] = 'นามสกุลต้องเป็นตัวอักษรเท่านั้น';
        }
        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
