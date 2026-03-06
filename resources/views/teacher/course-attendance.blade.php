@extends('layouts.layout')

@section('title', 'เช็คมาเรียน')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $courseOptions = collect($courses ?? []);
    $studentsByRoom = collect($studentsByRoom ?? []);
    $assignedRooms = collect($assignedRooms ?? []);
    $currentTerm = $selectedTerm ?? request('term');
    $currentDate = $attendanceDate ?? now($tz)->toDateString();
    $attendanceByStudent = $attendanceByStudent ?? [];
    $statusSummary = array_merge([
        'present' => 0,
        'late' => 0,
        'leave' => 0,
        'absent' => 0,
    ], $statusSummary ?? []);
    $recordedCount = (int) ($recordedCount ?? 0);
    $studentTotal = (int) $studentsByRoom->flatten(1)->count();
    $holidayRecord = $holidayRecord ?? null;
    $isHoliday = (bool) ($isHoliday ?? false);
    $holidayName = trim((string) optional($holidayRecord)->holiday_name);
    $holidayNote = trim((string) optional($holidayRecord)->note);
    $roomOptions = collect($roomOptions ?? $studentsByRoom->keys()->values());
    $selectedRoom = (string) ($selectedRoom ?? request('room', ''));
    $minAttendanceDate = $minAttendanceDate ?? now($tz)->toDateString();
    $maxAttendanceDate = $maxAttendanceDate ?? $minAttendanceDate;
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-10">
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">เช็คมาเรียน</p>
                <h1 class="text-3xl font-bold text-gray-900">บันทึกการมาเรียนรายวัน</h1>
                <p class="text-gray-600">เลือกหลักสูตร ภาคเรียน และวันที่ จากนั้นทำเครื่องหมายมา/สาย/ลา/ขาดของนักเรียน</p>

                <div class="flex flex-wrap gap-3">
                    @if($course ?? false)
                        <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}"
                           class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                            กลับไปหน้ารายละเอียดหลักสูตร
                        </a>
                        <a href="{{ route('teacher.courses.deductions', ['course' => $course->id, 'term' => $currentTerm, 'date' => $currentDate, 'room' => $selectedRoom !== '' ? $selectedRoom : null]) }}"
                           class="px-4 py-2 bg-pink-600 text-white rounded-xl text-sm hover:bg-pink-700 transition">
                            ไปหน้าตัดคะแนน
                        </a>
                        <a href="{{ route('teacher.courses.attendance.report', ['course' => $course->id, 'term' => $currentTerm, 'date' => $currentDate, 'room' => $selectedRoom !== '' ? $selectedRoom : null]) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">
                            รายงานย้อนหลัง
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-96 space-y-4">
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label for="attendanceCourseSelector" class="block text-sm font-semibold text-gray-700 mb-2">เลือกหลักสูตร</label>
                        <select id="attendanceCourseSelector"
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
                                <option value="{{ route('teacher.courses.attendance', $courseRouteParams) }}"
                                        @selected(optional($course)->id === $courseOption->id)>
                                    {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($course ?? false)
                    <form id="attendanceFilterForm" action="{{ route('teacher.courses.attendance', $course) }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ภาคเรียน</label>
                            <select name="term"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('attendanceFilterForm').submit()">
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                                <option value="summer" {{ $currentTerm === 'summer' ? 'selected' : '' }}>ภาคฤดูร้อน</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ห้อง</label>
                            <select name="room"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('attendanceFilterForm').submit()">
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
                                   min="{{ $minAttendanceDate }}"
                                   max="{{ $maxAttendanceDate }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500">
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
        @if($isHoliday)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl px-4 py-3">
                <p class="font-semibold">วันที่ {{ \Illuminate\Support\Carbon::parse($currentDate)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }} ถูกกำหนดเป็นวันหยุดโดยผอ.</p>
                <p class="text-sm mt-1">
                    {{ $holidayName !== '' ? $holidayName : 'ไม่ได้ระบุชื่อวันหยุด' }}
                    @if($holidayNote !== '')
                        | {{ $holidayNote }}
                    @endif
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">นักเรียนทั้งหมด</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($studentTotal) }}</p>
                <p class="text-xs text-gray-500 mt-1">ห้อง: {{ $assignedRooms->isNotEmpty() ? $assignedRooms->join(', ') : '-' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">เช็คแล้ว</p>
                <p class="text-3xl font-bold text-blue-700">{{ number_format($recordedCount) }}</p>
                <p class="text-xs text-gray-500 mt-1">ข้อมูลวันที่ {{ \Illuminate\Support\Carbon::parse($currentDate)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">มาเรียน</p>
                <p class="text-3xl font-bold text-green-700">{{ number_format($statusSummary['present'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">สาย {{ number_format($statusSummary['late'] ?? 0) }} คน</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">ลา / ขาด</p>
                <p class="text-3xl font-bold text-amber-700">{{ number_format(($statusSummary['leave'] ?? 0) + ($statusSummary['absent'] ?? 0)) }}</p>
                <p class="text-xs text-gray-500 mt-1">ลา {{ number_format($statusSummary['leave'] ?? 0) }} | ขาด {{ number_format($statusSummary['absent'] ?? 0) }}</p>
            </div>
        </div>

        @if($studentsByRoom->isEmpty())
            <div class="bg-white rounded-3xl shadow-md p-10 border border-dashed border-gray-200 text-center text-gray-500">
                ยังไม่พบนักเรียนในห้องที่ผูกกับหลักสูตรนี้
            </div>
        @else
            <form method="POST" action="{{ route('teacher.courses.attendance.store', $course) }}" class="space-y-6">
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

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead class="bg-slate-50 text-gray-700">
                                    <tr>
                                        <th class="px-3 py-3 border border-gray-200 text-center w-14">ลำดับ</th>
                                        <th class="px-3 py-3 border border-gray-200 text-left w-28">รหัส</th>
                                        <th class="px-3 py-3 border border-gray-200 text-left min-w-[220px]">ชื่อ - สกุล</th>
                                        <th class="px-3 py-3 border border-gray-200 text-center w-16">มา</th>
                                        <th class="px-3 py-3 border border-gray-200 text-center w-16">สาย</th>
                                        <th class="px-3 py-3 border border-gray-200 text-center w-16">ลา</th>
                                        <th class="px-3 py-3 border border-gray-200 text-center w-16">ขาด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        @php
                                            $fieldBase = "students.$rowIndex";
                                            $record = $attendanceByStudent[$student->id] ?? [];
                                            $statusValue = old("$fieldBase.status", $record['status'] ?? 'present');
                                            $fullName = trim(($student->title ?? '').' '.($student->first_name ?? '').' '.($student->last_name ?? ''));
                                        @endphp
                                        <tr class="hover:bg-blue-50/40">
                                            <td class="px-3 py-2 border border-gray-200 text-center text-gray-700">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 font-medium text-gray-700">
                                                {{ $student->student_code ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 text-gray-700">
                                                <input type="hidden" name="students[{{ $rowIndex }}][student_id]" value="{{ $student->id }}">
                                                {{ $fullName !== '' ? $fullName : '-' }}
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 text-center">
                                                <input type="radio"
                                                       name="students[{{ $rowIndex }}][status]"
                                                       value="present"
                                                       class="h-4 w-4 accent-green-600"
                                                       @disabled($isHoliday)
                                                       @checked($statusValue === 'present')>
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 text-center">
                                                <input type="radio"
                                                       name="students[{{ $rowIndex }}][status]"
                                                       value="late"
                                                       class="h-4 w-4 accent-yellow-600"
                                                       @disabled($isHoliday)
                                                       @checked($statusValue === 'late')>
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 text-center">
                                                <input type="radio"
                                                       name="students[{{ $rowIndex }}][status]"
                                                       value="leave"
                                                       class="h-4 w-4 accent-blue-600"
                                                       @disabled($isHoliday)
                                                       @checked($statusValue === 'leave')>
                                            </td>
                                            <td class="px-3 py-2 border border-gray-200 text-center">
                                                <input type="radio"
                                                       name="students[{{ $rowIndex }}][status]"
                                                       value="absent"
                                                       class="h-4 w-4 accent-red-600"
                                                       @disabled($isHoliday)
                                                       @checked($statusValue === 'absent')>
                                            </td>
                                        </tr>
                                        @php
                                            $rowIndex++;
                                        @endphp
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-gray-400 border border-gray-200">
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
                        @if($isHoliday)
                            <span class="px-4 py-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl">
                                วันนี้ถูกตั้งเป็นวันหยุด จึงไม่สามารถบันทึกเช็คชื่อได้
                            </span>
                        @else
                            <button type="submit"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-700 transition">
                                บันทึกการเช็คมาเรียน
                            </button>
                        @endif
                    </div>
                @endif
            </form>
        @endif
    @endunless
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('attendanceCourseSelector');
    selector?.addEventListener('change', (event) => {
        if (event.target.value) {
            window.location.href = event.target.value;
        }
    });

    const dateInput = document.querySelector('#attendanceFilterForm input[name="date"]');
    if (dateInput) {
        const minDate = dateInput.getAttribute('min') || '';
        const maxDate = dateInput.getAttribute('max') || '';
        dateInput.addEventListener('change', () => {
            if (minDate !== '' && dateInput.value !== '' && dateInput.value < minDate) {
                dateInput.value = minDate;
            }
            if (maxDate !== '' && dateInput.value !== '' && dateInput.value > maxDate) {
                dateInput.value = maxDate;
            }
            document.getElementById('attendanceFilterForm')?.submit();
        });
    }
});
</script>
@endpush
