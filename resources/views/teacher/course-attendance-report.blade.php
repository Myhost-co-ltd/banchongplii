@extends('layouts.layout')

@section('title', 'รายงานการมาเรียน')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $courseOptions = collect($courses ?? []);
    $canManageCourse = (bool) ($canManageCourse ?? true);
    $studentsByRoom = collect($studentsByRoom ?? []);
    $assignedRooms = collect($assignedRooms ?? []);
    $currentTerm = $selectedTerm ?? request('term');

    $currentDate = $reportDate ?? now($tz)->toDateString();
    $currentMonth = $reportMonth ?? now($tz)->format('Y-m');
    $maxReportDate = $maxReportDate ?? now($tz)->toDateString();
    $maxReportMonth = $maxReportMonth ?? now($tz)->format('Y-m');

    $reportMonthLabel = $reportMonthLabel
        ?? \Illuminate\Support\Carbon::parse($currentDate, $tz)->addYears(543)->locale('th')->isoFormat('MMMM YYYY');

    $monthDates = collect($monthDates ?? []);
    $attendanceGrid = $attendanceGrid ?? [];
    $studentStatusSummary = $studentStatusSummary ?? [];

    $statusSummaryTemplate = [
        'present' => 0,
        'late' => 0,
        'leave' => 0,
        'absent' => 0,
    ];

    $statusSummary = array_merge($statusSummaryTemplate, $statusSummary ?? []);

    $recordedCount = (int) ($recordedCount ?? 0);
    $studentTotal = (int) $studentsByRoom->flatten(1)->count();
    $roomOptions = collect($roomOptions ?? $studentsByRoom->keys()->values());
    $selectedRoom = (string) ($selectedRoom ?? request('room', ''));

    $statusLabels = [
        'present' => 'มา',
        'late' => 'สาย',
        'leave' => 'ลา',
        'absent' => 'ขาด',
    ];

    $statusShortLabels = [
        'present' => 'ม',
        'late' => 'ส',
        'leave' => 'ล',
        'absent' => 'ข',
    ];

    $statusClassMap = [
        'present' => 'bg-green-50 text-green-700 border-green-200',
        'late' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'leave' => 'bg-blue-50 text-blue-700 border-blue-200',
        'absent' => 'bg-red-50 text-red-700 border-red-200',
    ];

    $gridDateCount = max(1, $monthDates->count());
    $gridColspan = 7 + $gridDateCount;
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-10">
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">รายงานการมาเรียน</p>
                <h1 class="text-3xl font-bold text-gray-900">รายงานเช็คชื่อแบบ Grid รายเดือน</h1>
                <p class="text-gray-600">เลือกหลักสูตร ภาคเรียน ห้อง และเดือน เพื่อดูสถานะรายวันของนักเรียนทั้งเดือนในตารางเดียว</p>

                <div class="flex flex-wrap gap-3">
                    @if($course ?? false)
                        <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}"
                           class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                            กลับไปหน้ารายละเอียดหลักสูตร
                        </a>
                        @if($canManageCourse)
                            <a href="{{ route('teacher.courses.attendance', ['course' => $course->id, 'term' => $currentTerm, 'date' => $maxReportDate, 'room' => $selectedRoom !== '' ? $selectedRoom : null]) }}"
                               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">
                                ไปหน้าเช็คมาเรียน (วันนี้)
                            </a>
                        @else
                            <span class="px-4 py-2 bg-amber-100 text-amber-700 rounded-xl text-sm">
                                โหมดดูย้อนหลัง
                            </span>
                        @endif
                        <a href="{{ route('teacher.courses.attendance.report.export', ['course' => $course->id, 'term' => $currentTerm, 'month' => $currentMonth, 'room' => $selectedRoom !== '' ? $selectedRoom : null]) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm hover:bg-green-700 transition">
                            Export PDF
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-96 space-y-4">
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label for="attendanceReportCourseSelector" class="block text-sm font-semibold text-gray-700 mb-2">เลือกหลักสูตร</label>
                        <select id="attendanceReportCourseSelector"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($courseOptions as $courseOption)
                                @php
                                    $courseRouteParams = [
                                        'course' => $courseOption->id,
                                        'term' => $currentTerm,
                                        'month' => $currentMonth,
                                    ];
                                    if ($selectedRoom !== '') {
                                        $courseRouteParams['room'] = $selectedRoom;
                                    }
                                @endphp
                                <option value="{{ route('teacher.courses.attendance.report', $courseRouteParams) }}"
                                        @selected(optional($course)->id === $courseOption->id)>
                                    {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($course ?? false)
                    <form id="attendanceReportFilterForm" action="{{ route('teacher.courses.attendance.report', $course) }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ภาคเรียน</label>
                            <select name="term"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('attendanceReportFilterForm').submit()">
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                                <option value="summer" {{ $currentTerm === 'summer' ? 'selected' : '' }}>ภาคฤดูร้อน</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ห้อง</label>
                            <select name="room"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('attendanceReportFilterForm').submit()">
                                <option value="">ทุกห้อง</option>
                                @foreach($roomOptions as $roomOption)
                                    <option value="{{ $roomOption }}" @selected($selectedRoom === (string) $roomOption)>
                                        {{ $roomOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">เดือน</label>
                            <input type="month"
                                   name="month"
                                   value="{{ $currentMonth }}"
                                   max="{{ $maxReportMonth }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                   onchange="document.getElementById('attendanceReportFilterForm').submit()">
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">นักเรียนทั้งหมด</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($studentTotal) }}</p>
                <p class="text-xs text-gray-500 mt-1">ห้อง: {{ $assignedRooms->isNotEmpty() ? $assignedRooms->join(', ') : '-' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">มีข้อมูลบันทึก</p>
                <p class="text-3xl font-bold text-blue-700">{{ number_format($recordedCount) }}</p>
                <p class="text-xs text-gray-500 mt-1">ข้อมูลเดือน {{ $reportMonthLabel }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">มาเรียน</p>
                <p class="text-3xl font-bold text-green-700">{{ number_format($statusSummary['present'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">สาย {{ number_format($statusSummary['late'] ?? 0) }} คน</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">ลา</p>
                <p class="text-3xl font-bold text-blue-700">{{ number_format($statusSummary['leave'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">คน</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-sm text-gray-500">ขาด</p>
                <p class="text-3xl font-bold text-red-700">{{ number_format($statusSummary['absent'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">คน</p>
            </div>
        </div>

        @if($recordedCount === 0)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl px-4 py-3">
                ยังไม่มีข้อมูลเช็คชื่อของเดือน {{ $reportMonthLabel }}
            </div>
        @endif

        @if($studentsByRoom->isEmpty())
            <div class="bg-white rounded-3xl shadow-md p-10 border border-dashed border-gray-200 text-center text-gray-500">
                ยังไม่พบนักเรียนในห้องที่ผูกกับหลักสูตรนี้
            </div>
        @else
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
                                    <th class="px-3 py-3 border border-gray-200 text-center w-14" rowspan="2">ลำดับ</th>
                                    <th class="px-3 py-3 border border-gray-200 text-left w-28" rowspan="2">รหัส</th>
                                    <th class="px-3 py-3 border border-gray-200 text-left min-w-[220px]" rowspan="2">ชื่อ - สกุล</th>
                                    <th class="px-3 py-3 border border-gray-200 text-center" colspan="{{ $gridDateCount }}">
                                        วันที่ ({{ $reportMonthLabel }})
                                    </th>
                                    <th class="px-3 py-3 border border-gray-200 text-center" colspan="4">สรุปทั้งเดือน</th>
                                </tr>
                                <tr>
                                    @forelse($monthDates as $dateKey)
                                        @php
                                            $dateObj = \Illuminate\Support\Carbon::parse($dateKey, $tz);
                                            $dayLabel = $dateObj->format('j');
                                            $fullDateLabel = $dateObj->copy()->addYears(543)->locale('th')->isoFormat('D MMM YYYY');
                                        @endphp
                                        <th class="px-2 py-2 border border-gray-200 text-center min-w-[36px]" title="{{ $fullDateLabel }}">
                                            {{ $dayLabel }}
                                        </th>
                                    @empty
                                        <th class="px-2 py-2 border border-gray-200 text-center min-w-[36px]">-</th>
                                    @endforelse
                                    <th class="px-3 py-2 border border-gray-200 text-center w-14">มา</th>
                                    <th class="px-3 py-2 border border-gray-200 text-center w-14">สาย</th>
                                    <th class="px-3 py-2 border border-gray-200 text-center w-14">ลา</th>
                                    <th class="px-3 py-2 border border-gray-200 text-center w-14">ขาด</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php
                                        $fullName = trim(($student->title ?? '').' '.($student->first_name ?? '').' '.($student->last_name ?? ''));
                                        $dailyStatuses = $attendanceGrid[$student->id] ?? [];
                                        $rowSummary = array_merge($statusSummaryTemplate, $studentStatusSummary[$student->id] ?? []);
                                    @endphp
                                    <tr class="hover:bg-blue-50/40">
                                        <td class="px-3 py-2 border border-gray-200 text-center text-gray-700">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-3 py-2 border border-gray-200 font-medium text-gray-700">
                                            {{ $student->student_code ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 border border-gray-200 text-gray-700">
                                            {{ $fullName !== '' ? $fullName : '-' }}
                                        </td>

                                        @forelse($monthDates as $dateKey)
                                            @php
                                                $statusCode = trim((string) ($dailyStatuses[$dateKey] ?? ''));
                                                $statusLabel = $statusShortLabels[$statusCode] ?? '-';
                                                $statusClass = $statusClassMap[$statusCode] ?? 'bg-gray-50 text-gray-500 border-gray-200';
                                                $statusTitle = $statusLabels[$statusCode] ?? '-';
                                            @endphp
                                            <td class="px-1 py-1 border border-gray-200 text-center" title="{{ $statusTitle }}">
                                                <span class="inline-flex w-6 h-6 items-center justify-center rounded-md border text-[11px] font-semibold {{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                        @empty
                                            <td class="px-1 py-1 border border-gray-200 text-center">
                                                <span class="inline-flex w-6 h-6 items-center justify-center rounded-md border text-[11px] font-semibold bg-gray-50 text-gray-500 border-gray-200">-</span>
                                            </td>
                                        @endforelse

                                        <td class="px-3 py-2 border border-gray-200 text-center text-green-700 font-semibold">{{ number_format($rowSummary['present'] ?? 0) }}</td>
                                        <td class="px-3 py-2 border border-gray-200 text-center text-yellow-700 font-semibold">{{ number_format($rowSummary['late'] ?? 0) }}</td>
                                        <td class="px-3 py-2 border border-gray-200 text-center text-blue-700 font-semibold">{{ number_format($rowSummary['leave'] ?? 0) }}</td>
                                        <td class="px-3 py-2 border border-gray-200 text-center text-red-700 font-semibold">{{ number_format($rowSummary['absent'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $gridColspan }}" class="px-4 py-6 text-center text-gray-400 border border-gray-200">
                                            ยังไม่มีนักเรียนในห้องนี้
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        @endif
    @endunless
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('attendanceReportCourseSelector');
    selector?.addEventListener('change', (event) => {
        if (event.target.value) {
            window.location.href = event.target.value;
        }
    });
});
</script>
@endpush
