@extends('layouts.layout')

@section('title', 'ตัดคะแนน')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $courseOptions = collect($courses ?? []);
    $studentsByRoom = collect($studentsByRoom ?? []);
    $assignedRooms = collect($assignedRooms ?? []);
    $currentTerm = $selectedTerm ?? request('term');
    $currentDate = $attendanceDate ?? now($tz)->toDateString();
    $deductionByStudent = $deductionByStudent ?? [];
    $totalDeduction = (float) ($totalDeduction ?? 0);
    $deductedCount = (int) ($deductedCount ?? 0);
    $recordedCount = (int) ($recordedCount ?? 0);
    $attendanceAffectiveCount = (int) ($attendanceAffectiveCount ?? 0);
    $attendanceAffectiveTotal = (float) ($attendanceAffectiveTotal ?? 0);
    $attendanceAffectiveScoreMap = array_merge([
        'present' => 0.0,
        'late' => 0.5,
        'leave' => 1.0,
        'absent' => 2.0,
    ], $attendanceAffectiveScoreMap ?? []);
    $studentTotal = (int) $studentsByRoom->flatten(1)->count();
    $roomOptions = collect($roomOptions ?? $studentsByRoom->keys()->values());
    $selectedRoom = (string) ($selectedRoom ?? request('room', ''));
    $courseTerm = (string) (optional($course)->term ?? '1');
    $termForGrid = (string) ($currentTerm ?: $courseTerm);
    $assignmentItems = collect(optional($course)->assignments ?? [])
        ->filter(function ($item) use ($courseTerm, $termForGrid) {
            return (string) ($item['term'] ?? $courseTerm) === $termForGrid;
        })
        ->values();
    $gridSlotCount = max(1, (int) ($gridSlotLimit ?? $assignmentItems->count()));
    $gridSlots = range(1, $gridSlotCount);
    $gridSlotDetails = collect($gridSlots)->mapWithKeys(function ($slot) use ($assignmentItems) {
        $item = $assignmentItems->get($slot - 1);
        $dueDateDisplay = '';

        if (is_array($item) && ! empty($item['due_date'])) {
            try {
                $dueDateDisplay = \Illuminate\Support\Carbon::parse($item['due_date'])
                    ->timezone('Asia/Bangkok')
                    ->addYears(543)
                    ->locale('th')
                    ->isoFormat('D MMM YYYY');
            } catch (\Throwable $e) {
                $dueDateDisplay = (string) $item['due_date'];
            }
        }

        if (! is_array($item)) {
            return [
                $slot => [
                    'configured' => false,
                    'title' => '',
                    'score' => '',
                    'due_display' => '',
                    'notes' => '',
                ],
            ];
        }

        return [
            $slot => [
                'configured' => true,
                'title' => trim((string) ($item['title'] ?? '')),
                'score' => isset($item['score']) ? (string) $item['score'] : '',
                'due_display' => $dueDateDisplay,
                'notes' => trim((string) ($item['notes'] ?? '')),
            ],
        ];
    });
    $gridSlotMaxScores = collect($gridSlotMaxScores ?? [])
        ->mapWithKeys(function ($score, $slot) {
            return [(int) $slot => max(0, min(100, (int) floor((float) $score)))];
        });
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-10">
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">ตัดคะแนน</p>
                <h1 class="text-3xl font-bold text-gray-900">บันทึกการตัดคะแนนรายวัน</h1>
                <p class="text-gray-600">เลือกหลักสูตร ภาคเรียน และวันที่ จากนั้นกรอกคะแนนของนักเรียนในตาราง</p>

                <div class="flex flex-wrap gap-3">
                    @if($course ?? false)
                        <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}"
                           class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                            กลับไปหน้ารายละเอียดหลักสูตร
                        </a>
                        <a href="{{ route('teacher.courses.attendance', ['course' => $course->id, 'term' => $currentTerm, 'date' => $currentDate, 'room' => $selectedRoom !== '' ? $selectedRoom : null]) }}"
                           class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm">
                            ไปหน้าเช็คมาเรียน
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-96 space-y-4">
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label for="deductionCourseSelector" class="block text-sm font-semibold text-gray-700 mb-2">เลือกหลักสูตร</label>
                        <select id="deductionCourseSelector"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($courseOptions as $courseOption)
                                @php
                                    $courseRouteParams = [
                                        'course' => $courseOption->id,
                                        'term' => $currentTerm,
                                        'date' => $currentDate,
                                    ];
                                    if ($selectedRoom !== '') {
                                        $courseRouteParams['room'] = $selectedRoom;
                                    }
                                @endphp
                                <option value="{{ route('teacher.courses.deductions', $courseRouteParams) }}"
                                        @selected(optional($course)->id === $courseOption->id)>
                                    {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($course ?? false)
                    <form id="deductionFilterForm" action="{{ route('teacher.courses.deductions', $course) }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ภาคเรียน</label>
                            <select name="term"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('deductionFilterForm').submit()">
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                                <option value="summer" {{ $currentTerm === 'summer' ? 'selected' : '' }}>ภาคฤดูร้อน</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ห้อง</label>
                            <select name="room"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('deductionFilterForm').submit()">
                                <option value="">ทุกห้อง</option>
                                @foreach($roomOptions as $roomOption)
                                    <option value="{{ $roomOption }}" @selected($selectedRoom === (string) $roomOption)>
                                        {{ $roomOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">วันที่</label>
                            <input type="date"
                                   name="date"
                                   value="{{ $currentDate }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                   onchange="document.getElementById('deductionFilterForm').submit()">
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-2xl px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl px-4 py-3">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless($course ?? false)
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center text-gray-600">
            ยังไม่พบหลักสูตรของครูผู้สอน กรุณาเพิ่มหลักสูตรก่อนใช้งานหน้านี้
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">นักเรียนทั้งหมด</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($studentTotal) }}</p>
                <p class="text-xs text-gray-500 mt-1">ห้อง: {{ $assignedRooms->isNotEmpty() ? $assignedRooms->join(', ') : '-' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">มีข้อมูลบันทึก</p>
                <p class="text-3xl font-bold text-blue-700">{{ number_format($recordedCount) }}</p>
                <p class="text-xs text-gray-500 mt-1">ข้อมูลวันที่ {{ \Illuminate\Support\Carbon::parse($currentDate)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">จำนวนนักเรียนที่ถูกตัด</p>
                <p class="text-3xl font-bold text-amber-700">{{ number_format($deductedCount) }}</p>
                <p class="text-xs text-gray-500 mt-1">คน (คะแนนมากกว่า 0)</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">คะแนนที่ตัดรวม</p>
                <p class="text-3xl font-bold text-rose-700">{{ number_format($totalDeduction, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">จิตพิสัยจากเช็คชื่อ {{ number_format($attendanceAffectiveTotal, 2) }} คะแนน</p>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl px-4 py-3 text-sm">
            <p class="font-semibold">คะแนนจิตพิสัยจากการเช็คมาเรียน (คำนวณอัตโนมัติ)</p>
            <p class="mt-1">
                มา {{ rtrim(rtrim(number_format((float) ($attendanceAffectiveScoreMap['present'] ?? 0), 2, '.', ''), '0'), '.') ?: '0' }}
                | สาย {{ rtrim(rtrim(number_format((float) ($attendanceAffectiveScoreMap['late'] ?? 0), 2, '.', ''), '0'), '.') ?: '0' }}
                | ลา {{ rtrim(rtrim(number_format((float) ($attendanceAffectiveScoreMap['leave'] ?? 0), 2, '.', ''), '0'), '.') ?: '0' }}
                | ขาด {{ rtrim(rtrim(number_format((float) ($attendanceAffectiveScoreMap['absent'] ?? 0), 2, '.', ''), '0'), '.') ?: '0' }}
                คะแนน
            </p>
            <p class="text-xs text-amber-700 mt-1">นักเรียนที่ได้คะแนนจากเช็คชื่อ: {{ number_format($attendanceAffectiveCount) }} คน</p>
        </div>

        @if($studentsByRoom->isEmpty())
            <div class="bg-white rounded-3xl shadow-md p-10 border border-dashed border-gray-200 text-center text-gray-500">
                ยังไม่พบนักเรียนในห้องที่ผูกกับหลักสูตรนี้
            </div>
        @else
            <form method="POST" action="{{ route('teacher.courses.deductions.store', $course) }}" class="space-y-6">
                @csrf
                <input type="hidden" name="term" value="{{ $currentTerm }}">
                <input type="hidden" name="attendance_date" value="{{ $currentDate }}">
                <input type="hidden" name="room" value="{{ $selectedRoom }}">

                @php
                    $rowIndex = 0;
                @endphp
                @foreach($studentsByRoom as $room => $students)
                    <section class="bg-white rounded-3xl shadow-md border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">ห้อง {{ $room ?: '-' }}</h3>
                            <span class="text-xs bg-white border border-gray-200 text-gray-600 px-2 py-1 rounded-full">
                                {{ number_format(collect($students)->count()) }} คน
                            </span>
                        </div>

                        <div class="px-6 py-2 bg-blue-50 border-b border-blue-100 text-xs text-black">
                            แตะช่องคะแนนเพื่อดูรายละเอียดงาน/สอบ และกรอกคะแนนในช่องตารางได้เลย (คะแนนรวมคำนวณอัตโนมัติ)
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[1420px] w-full border-collapse text-xs text-black">
                                <thead>
                                    <tr class="bg-gray-100 text-black">
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center w-12">ลำดับ</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center w-36">รหัส</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-left min-w-[220px]">ชื่อ - สกุล</th>
                                        <th colspan="{{ count($gridSlots) }}" class="px-2 py-2 border border-slate-500 text-center">บันทึกการตัดคะแนนย่อย (Grid)</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center min-w-[100px]">ระหว่างภาค</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center min-w-[110px]">จิตพิสัย<br>(เช็คชื่อ)</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center min-w-[100px]">สอบปลายภาค</th>
                                        <th rowspan="2" class="px-2 py-2 border border-slate-500 text-center min-w-[100px]">คะแนนรวม</th>
                                    </tr>
                                    <tr class="bg-slate-100 text-black">
                                        @foreach($gridSlots as $slot)
                                            @php
                                                $slotDetail = $gridSlotDetails->get($slot, [
                                                    'configured' => false,
                                                    'title' => '',
                                                    'score' => '',
                                                    'due_display' => '',
                                                    'notes' => '',
                                                ]);
                                                $titleText = $slotDetail['title'] !== '' ? $slotDetail['title'] : ('งาน/สอบครั้งที่ ' . $slot);
                                                $maxScoreDisplay = $slotDetail['score'] !== ''
                                                    ? rtrim(rtrim(number_format((float) $slotDetail['score'], 2, '.', ''), '0'), '.')
                                                    : '-';
                                                $hoverText = $slotDetail['configured']
                                                    ? ('ครั้งที่ ' . $slot . ': ' . $titleText . ' (คะแนนเต็ม ' . $maxScoreDisplay . ')')
                                                    : ('ครั้งที่ ' . $slot . ': ยังไม่กำหนดงาน/สอบ');
                                            @endphp
                                            <th class="px-1 py-1 border border-gray-300 text-center w-10">
                                                <button type="button"
                                                        class="js-grid-slot-btn w-full font-semibold text-black hover:text-black focus:outline-none focus:ring-2 focus:ring-blue-400 rounded"
                                                        data-slot="{{ $slot }}"
                                                        data-configured="{{ $slotDetail['configured'] ? '1' : '0' }}"
                                                        data-title="{{ $slotDetail['title'] }}"
                                                        data-score="{{ $slotDetail['score'] }}"
                                                        data-due="{{ $slotDetail['due_display'] }}"
                                                        title="{{ $hoverText }}">
                                                    <span class="block leading-tight text-xs">{{ $maxScoreDisplay }}</span>
                                                </button>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        @php
                                            $fieldBase = "students.$rowIndex";
                                            $record = $deductionByStudent[$student->id] ?? [];
                                            $savedTermScore = (float) ($record['term_score'] ?? 0);
                                            $savedFinalExamScore = (float) ($record['final_exam_score'] ?? 0);
                                            $savedTotalScore = (float) ($record['total_score'] ?? ($record['deduction_points'] ?? 0));
                                            $attendanceScoreValue = (float) ($record['attendance_score'] ?? 0);
                                            $attendanceScoreValue = max(0, min(100, $attendanceScoreValue));
                                            $savedGridPoints = collect($record['grid_points'] ?? [])
                                                ->mapWithKeys(fn ($value, $slot) => [(int) $slot => (float) $value])
                                                ->all();
                                            $gridValues = [];
                                            foreach ($gridSlots as $slot) {
                                                $slotMaxScore = (int) ($gridSlotMaxScores->get($slot, 0));
                                                $legacyFallbackScore = $savedTermScore > 0
                                                    ? $savedTermScore
                                                    : (float) ($record['deduction_points'] ?? 0);
                                                $fallbackValue = ($slot === 1 && empty($savedGridPoints) && $legacyFallbackScore > 0)
                                                    ? $legacyFallbackScore
                                                    : 0;
                                                $rawGridValue = (float) old(
                                                    "$fieldBase.grid_points.$slot",
                                                    $savedGridPoints[$slot] ?? $fallbackValue
                                                );
                                                $gridValues[$slot] = $slotMaxScore > 0
                                                    ? max(0, min($slotMaxScore, (int) floor($rawGridValue)))
                                                    : 0;
                                            }
                                            $gridTotalValue = collect($gridValues)->sum();
                                            $termScoreValue = $gridTotalValue > 0
                                                ? min(100, (float) $gridTotalValue)
                                                : min(100, (float) $savedTermScore);
                                            $finalExamValue = (float) old("$fieldBase.final_exam_score", $savedFinalExamScore);
                                            $finalExamValue = max(0, min(100, (int) floor($finalExamValue)));
                                            $finalExamMaxValue = max(0, 100 - (int) round($termScoreValue));
                                            $finalExamValue = min($finalExamValue, $finalExamMaxValue);
                                            $finalExamMaxDisplay = (string) ((int) $finalExamMaxValue);
                                            $overallFallback = $termScoreValue + $finalExamValue;
                                            if ($overallFallback <= 0 && $savedTotalScore > 0) {
                                                $overallFallback = $savedTotalScore;
                                            }
                                            $displayOverallValue = max(0, min(100, (float) old("$fieldBase.deduction_points", $overallFallback)));
                                            $fullName = trim(($student->title ?? '').' '.($student->first_name ?? '').' '.($student->last_name ?? ''));
                                            $attendanceScoreDisplay = rtrim(rtrim(number_format((float) $attendanceScoreValue, 2, '.', ''), '0'), '.');
                                        @endphp
                                        <tr class="hover:bg-slate-50" data-grid-row="{{ $rowIndex }}">
                                            <td class="px-2 py-1.5 border border-gray-300 text-center text-black">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-2 py-1.5 border border-gray-300 font-medium text-black">
                                                {{ $student->student_code ?? '-' }}
                                            </td>
                                            <td class="px-2 py-1.5 border border-gray-300 text-black">
                                                <input type="hidden" name="students[{{ $rowIndex }}][student_id]" value="{{ $student->id }}">
                                                {{ $fullName !== '' ? $fullName : '-' }}
                                            </td>
                                            @foreach($gridSlots as $slot)
                                                @php
                                                    $slotMaxScore = (int) ($gridSlotMaxScores->get($slot, 0));
                                                    $gridCellValue = (float) ($gridValues[$slot] ?? 0);
                                                    $gridCellDisplay = $gridCellValue > 0
                                                        ? (string) ((int) $gridCellValue)
                                                        : '';
                                                @endphp
                                                <td class="px-1 py-1 border border-gray-300 text-center bg-slate-50">
                                                    <input type="number"
                                                           name="students[{{ $rowIndex }}][grid_points][{{ $slot }}]"
                                                           value="{{ $gridCellDisplay }}"
                                                           min="0"
                                                           max="{{ $slotMaxScore }}"
                                                           step="1"
                                                           class="js-grid-input w-full min-w-[36px] border border-transparent bg-transparent rounded px-1 py-0.5 text-center text-xs text-black focus:border-blue-300 focus:ring-1 focus:ring-blue-300"
                                                           data-row="{{ $rowIndex }}"
                                                           inputmode="numeric"
                                                           @disabled($slotMaxScore <= 0)
                                                           placeholder="-">
                                                </td>
                                            @endforeach
                                            <td class="px-2 py-1.5 border border-gray-300">
                                                <input type="number"
                                                       value="{{ rtrim(rtrim(number_format((float) $termScoreValue, 2, '.', ''), '0'), '.') ?: '0' }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.5"
                                                       readonly
                                                       class="js-term-score w-full border border-gray-300 rounded px-2 py-1 text-center bg-gray-100 text-black"
                                                       data-row="{{ $rowIndex }}"
                                                       placeholder="0">
                                            </td>
                                            <td class="px-2 py-1.5 border border-gray-300 bg-amber-50/50">
                                                <input type="number"
                                                       value="{{ $attendanceScoreDisplay !== '' ? $attendanceScoreDisplay : '0' }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.5"
                                                       readonly
                                                       class="js-attendance-score w-full border border-amber-200 rounded px-2 py-1 text-center bg-amber-50 text-amber-900"
                                                       data-row="{{ $rowIndex }}"
                                                       placeholder="0">
                                            </td>
                                            <td class="px-2 py-1.5 border border-gray-300">
                                                <input type="number"
                                                       name="students[{{ $rowIndex }}][final_exam_score]"
                                                       value="{{ $finalExamValue > 0 ? (string) ((int) $finalExamValue) : '' }}"
                                                       min="0"
                                                       max="{{ $finalExamMaxDisplay !== '' ? $finalExamMaxDisplay : '0' }}"
                                                       step="1"
                                                       class="js-final-exam w-full border border-gray-300 rounded px-2 py-1 text-center text-black"
                                                       data-row="{{ $rowIndex }}"
                                                       inputmode="numeric"
                                                       placeholder="0">
                                            </td>
                                            <td class="px-2 py-1.5 border border-gray-300">
                                                <input type="number"
                                                       name="students[{{ $rowIndex }}][deduction_points]"
                                                       value="{{ rtrim(rtrim(number_format((float) $displayOverallValue, 2, '.', ''), '0'), '.') ?: '0' }}"
                                                       min="0"
                                                       max="100"
                                                       step="0.5"
                                                       readonly
                                                       class="js-row-total w-full border border-gray-300 rounded px-2 py-1 text-center bg-gray-100 text-black"
                                                       data-row="{{ $rowIndex }}"
                                                       placeholder="0">
                                            </td>
                                        </tr>
                                        @php
                                            $rowIndex++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td colspan="{{ 7 + count($gridSlots) }}" class="px-4 py-6 text-center text-gray-400 border border-gray-300">
                                                ยังไม่มีนักเรียนในห้องนี้
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach

                @if($studentTotal > 0)
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-6 py-3 bg-pink-600 text-white rounded-2xl shadow hover:bg-pink-700 transition">
                            บันทึกการตัดคะแนน
                        </button>
                    </div>
                @endif
            </form>
        @endif
    @endunless

    <div id="gridSlotInfoModal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-black/40" data-grid-slot-close></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 id="gridSlotModalTitle" class="text-lg font-semibold text-gray-900">รายละเอียดช่อง</h3>
                        <p id="gridSlotModalSub" class="text-xs text-gray-500 mt-0.5">งาน/สอบ</p>
                    </div>
                    <button type="button"
                            data-grid-slot-close
                            class="px-2 py-1 text-gray-500 hover:text-gray-700 rounded-lg border border-gray-200">
                        ปิด
                    </button>
                </div>

                <div class="px-5 py-4 space-y-3 text-sm">
                    <div class="grid grid-cols-[96px_1fr] gap-2">
                        <span class="text-gray-500">รายการ</span>
                        <span id="gridSlotModalItem" class="text-gray-900 font-medium">-</span>
                    </div>
                    <div class="grid grid-cols-[96px_1fr] gap-2">
                        <span class="text-gray-500">คะแนนเต็ม</span>
                        <span id="gridSlotModalScore" class="text-gray-900">-</span>
                    </div>
                    <div class="grid grid-cols-[96px_1fr] gap-2">
                        <span class="text-gray-500">วันกำหนดส่ง</span>
                        <span id="gridSlotModalDue" class="text-gray-900">-</span>
                    </div>

                    <p id="gridSlotModalEmptyHint" class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 hidden">
                        ช่องนี้ยังไม่มีการกำหนดงาน/สอบในภาคเรียนที่เลือก
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('deductionCourseSelector');
    selector?.addEventListener('change', (event) => {
        if (event.target.value) {
            window.location.href = event.target.value;
        }
    });

    const slotButtons = document.querySelectorAll('.js-grid-slot-btn');
    const slotModal = document.getElementById('gridSlotInfoModal');
    const slotTitleEl = document.getElementById('gridSlotModalTitle');
    const slotSubEl = document.getElementById('gridSlotModalSub');
    const slotItemEl = document.getElementById('gridSlotModalItem');
    const slotScoreEl = document.getElementById('gridSlotModalScore');
    const slotDueEl = document.getElementById('gridSlotModalDue');
    const slotEmptyHintEl = document.getElementById('gridSlotModalEmptyHint');

    const closeSlotModal = () => {
        if (!slotModal) {
            return;
        }
        slotModal.classList.add('hidden');
        slotModal.setAttribute('aria-hidden', 'true');
    };

    const openSlotModal = (button) => {
        if (!slotModal) {
            return;
        }

        const slot = button.dataset.slot || '-';
        const configured = button.dataset.configured === '1';
        const title = (button.dataset.title || '').trim();
        const score = (button.dataset.score || '').trim();
        const due = (button.dataset.due || '').trim();

        if (slotTitleEl) {
            slotTitleEl.textContent = `รายละเอียดช่อง ${slot}`;
        }
        if (slotSubEl) {
            slotSubEl.textContent = `งาน/สอบครั้งที่ ${slot}`;
        }
        if (slotItemEl) {
            slotItemEl.textContent = configured ? (title || `งาน/สอบครั้งที่ ${slot}`) : '-';
        }
        if (slotScoreEl) {
            slotScoreEl.textContent = score !== '' ? `${score} คะแนน` : '-';
        }
        if (slotDueEl) {
            slotDueEl.textContent = due !== '' ? due : '-';
        }
        if (slotEmptyHintEl) {
            slotEmptyHintEl.classList.toggle('hidden', configured);
        }

        slotModal.classList.remove('hidden');
        slotModal.setAttribute('aria-hidden', 'false');
    };

    slotButtons.forEach((button) => {
        button.addEventListener('click', () => openSlotModal(button));
    });

    slotModal?.querySelectorAll('[data-grid-slot-close]').forEach((el) => {
        el.addEventListener('click', closeSlotModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSlotModal();
        }
    });

    const formatNumber = (value) => {
        const rounded = Math.round(value * 100) / 100;
        return rounded.toString().replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
    };

    const clampScore = (value) => {
        if (!Number.isFinite(value)) {
            return 0;
        }
        return Math.max(0, Math.min(100, value));
    };

    const readScore = (input) => {
        if (!input) {
            return 0;
        }

        const raw = String(input.value ?? '').trim();
        if (raw === '') {
            return 0;
        }

        const sanitized = raw.replace(/[^\d]/g, '');
        if (sanitized === '') {
            return 0;
        }

        const parsed = Number.parseInt(sanitized, 10);
        if (Number.isNaN(parsed)) {
            return 0;
        }

        const maxRaw = String(input.max ?? '').trim();
        const maxParsed = maxRaw === '' ? 100 : Number.parseInt(maxRaw, 10);
        const maxValue = Number.isNaN(maxParsed) ? 100 : clampScore(maxParsed);

        return Math.max(0, Math.min(maxValue, parsed));
    };

    const sanitizeIntegerInput = (input) => {
        if (!input) {
            return;
        }

        const raw = String(input.value ?? '');
        if (raw.trim() === '') {
            input.value = '';
            return;
        }

        const sanitized = raw.replace(/[^\d]/g, '');
        if (sanitized === '') {
            input.value = '';
            return;
        }

        const parsed = Number.parseInt(sanitized, 10);
        if (Number.isNaN(parsed)) {
            input.value = '';
            return;
        }

        const maxRaw = String(input.max ?? '').trim();
        const maxParsed = maxRaw === '' ? 100 : Number.parseInt(maxRaw, 10);
        const maxValue = Number.isNaN(maxParsed) ? 100 : clampScore(maxParsed);
        const normalized = Math.max(0, Math.min(maxValue, parsed));
        input.value = formatNumber(normalized);
    };

    const syncRowTotal = (rowIndex) => {
        if (!rowIndex) {
            return;
        }

        let termScore = 0;
        document.querySelectorAll(`.js-grid-input[data-row="${rowIndex}"]`).forEach((input) => {
            termScore += readScore(input);
        });
        termScore = clampScore(termScore);

        const termInput = document.querySelector(`.js-term-score[data-row="${rowIndex}"]`);
        if (termInput) {
            termInput.value = formatNumber(termScore);
        }

        const finalInput = document.querySelector(`.js-final-exam[data-row="${rowIndex}"]`);
        const finalExamMax = clampScore(100 - termScore);
        if (finalInput) {
            finalInput.max = formatNumber(finalExamMax);
        }

        let finalExamScore = readScore(finalInput);
        if (finalExamScore > finalExamMax) {
            finalExamScore = finalExamMax;
            if (finalInput) {
                finalInput.value = finalExamScore > 0 ? formatNumber(finalExamScore) : '';
            }
        }

        const totalScore = clampScore(termScore + finalExamScore);

        const totalInput = document.querySelector(`.js-row-total[data-row="${rowIndex}"]`);
        if (totalInput) {
            totalInput.value = formatNumber(totalScore);
        }
    };

    const trackedRows = new Set();
    document.querySelectorAll('.js-grid-input, .js-final-exam').forEach((input) => {
        const rowIndex = input.dataset.row;
        if (!rowIndex) {
            return;
        }

        input.addEventListener('keydown', (event) => {
            if (['-', '+', '.', ',', 'e', 'E'].includes(event.key)) {
                event.preventDefault();
            }
        });

        trackedRows.add(rowIndex);
        input.addEventListener('input', () => {
            sanitizeIntegerInput(input);
            syncRowTotal(rowIndex);
        });
        input.addEventListener('change', () => {
            sanitizeIntegerInput(input);
            syncRowTotal(rowIndex);
        });
    });

    document.querySelectorAll('.js-final-exam').forEach((input) => {
        input.addEventListener('blur', () => {
            const rowIndex = input.dataset.row;
            if (rowIndex) {
                syncRowTotal(rowIndex);
            }
            sanitizeIntegerInput(input);
        });
    });

    trackedRows.forEach((rowIndex) => syncRowTotal(rowIndex));
});
</script>
@endpush
