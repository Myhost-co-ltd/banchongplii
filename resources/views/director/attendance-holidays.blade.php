@extends('layouts.layout-director')

@section('title', 'กำหนดวันหยุด')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $courses = collect($courses ?? []);
    $selectedCourse = $selectedCourse ?? null;
    $currentCourseId = (int) ($selectedCourseId ?? request('course_id', 0));
    $currentTerm = (string) ($selectedTerm ?? request('term', '1'));
    $currentDate = $attendanceDate ?? now($tz)->toDateString();
    $holidayRecord = $holidayRecord ?? null;
    $holidayName = trim((string) optional($holidayRecord)->holiday_name);
    $holidayNote = trim((string) optional($holidayRecord)->note);
    $recentHolidays = collect($recentHolidays ?? []);
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <h2 class="text-3xl font-bold text-gray-900">กำหนดวันหยุด</h2>
        <p class="text-gray-600 mt-2">ผอ.สามารถกำหนดหรือยกเลิกวันหยุดรายวิชาได้จากหน้านี้</p>
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

    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100 space-y-6">
        <form id="directorHolidayFilterForm" action="{{ route('director.attendance-holidays') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">หลักสูตร</label>
                <select name="course_id"
                        class="w-full border border-gray-300 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                        onchange="document.getElementById('directorHolidayFilterForm').submit()">
                    @forelse($courses as $courseOption)
                        <option value="{{ $courseOption->id }}" @selected($currentCourseId === (int) $courseOption->id)>
                            {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                        </option>
                    @empty
                        <option value="">ยังไม่มีหลักสูตร</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ภาคเรียน</label>
                <select name="term"
                        class="w-full border border-gray-300 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                        onchange="document.getElementById('directorHolidayFilterForm').submit()">
                    <option value="1" @selected($currentTerm === '1')>ภาคเรียนที่ 1</option>
                    <option value="2" @selected($currentTerm === '2')>ภาคเรียนที่ 2</option>
                    <option value="summer" @selected($currentTerm === 'summer')>ภาคฤดูร้อน</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">วันที่</label>
                <input type="date"
                       name="date"
                       value="{{ $currentDate }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                       onchange="document.getElementById('directorHolidayFilterForm').submit()">
            </div>
        </form>

        @if($selectedCourse)
            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                หลักสูตรที่เลือก: <span class="font-semibold text-gray-900">{{ $selectedCourse->name }}</span>
                | ชั้น {{ $selectedCourse->grade ?? '-' }}
                | ครูผู้สอน {{ optional($selectedCourse->teacher)->name ?? '-' }}
            </div>

            @if($holidayRecord)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl px-4 py-3">
                    <p class="font-semibold">
                        วันที่ {{ \Illuminate\Support\Carbon::parse($currentDate)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }} ถูกกำหนดเป็นวันหยุดแล้ว
                    </p>
                    <p class="text-sm mt-1">
                        {{ $holidayName !== '' ? $holidayName : 'ไม่ได้ระบุชื่อวันหยุด' }}
                        @if($holidayNote !== '')
                            | {{ $holidayNote }}
                        @endif
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ route('director.attendance-holidays.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                <input type="hidden" name="term" value="{{ $currentTerm }}">
                <input type="hidden" name="attendance_date" value="{{ $currentDate }}">
                <input type="hidden" name="action" value="holiday">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ชื่อวันหยุด</label>
                    <input type="text"
                           name="holiday_name"
                           value="{{ old('holiday_name', $holidayName) }}"
                           maxlength="255"
                           class="w-full border border-gray-300 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-amber-500"
                           placeholder="เช่น วันหยุดราชการ / กิจกรรมโรงเรียน">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">หมายเหตุ</label>
                    <input type="text"
                           name="holiday_note"
                           value="{{ old('holiday_note', $holidayNote) }}"
                           maxlength="1000"
                           class="w-full border border-gray-300 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-amber-500"
                           placeholder="ระบุรายละเอียดเพิ่มเติม (ถ้ามี)">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2.5 bg-amber-500 text-white rounded-2xl shadow hover:bg-amber-600 transition">
                        บันทึกวันหยุด
                    </button>
                </div>
            </form>

            @if($holidayRecord)
                <form method="POST" action="{{ route('director.attendance-holidays.store') }}">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                    <input type="hidden" name="term" value="{{ $currentTerm }}">
                    <input type="hidden" name="attendance_date" value="{{ $currentDate }}">
                    <input type="hidden" name="action" value="clear_holiday">
                    <button type="submit"
                            class="px-5 py-2.5 bg-white border border-red-200 text-red-600 rounded-2xl hover:bg-red-50 transition">
                        ยกเลิกวันหยุด
                    </button>
                </form>
            @endif

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">รายการวันหยุดล่าสุดของภาคเรียนนี้</h3>
                @if($recentHolidays->isEmpty())
                    <div class="text-sm text-gray-500 border border-dashed border-gray-200 rounded-2xl px-4 py-6 text-center">
                        ยังไม่มีการกำหนดวันหยุดในภาคเรียนนี้
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($recentHolidays as $holidayItem)
                            <div class="border border-gray-200 rounded-xl px-4 py-3 bg-white">
                                <p class="font-semibold text-gray-900">
                                    {{ \Illuminate\Support\Carbon::parse($holidayItem->holiday_date)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}
                                </p>
                                <p class="text-sm text-gray-700">
                                    {{ trim((string) ($holidayItem->holiday_name ?? '')) !== '' ? $holidayItem->holiday_name : 'ไม่ได้ระบุชื่อวันหยุด' }}
                                </p>
                                @if(trim((string) ($holidayItem->note ?? '')) !== '')
                                    <p class="text-xs text-gray-500 mt-1">{{ $holidayItem->note }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="text-sm text-gray-500 border border-dashed border-gray-200 rounded-2xl px-4 py-6 text-center">
                ยังไม่มีหลักสูตรในระบบสำหรับกำหนดวันหยุด
            </div>
        @endif
    </div>
</div>
@endsection
