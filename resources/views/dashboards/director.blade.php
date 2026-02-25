@extends('layouts.layout-director')

@section('title', 'แดชบอร์ดผู้อำนวยการ')

@section('content')
<div class="space-y-10 overflow-y-auto pr-2">

    <!-- หัวข้อหลัก -->
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <h2 class="text-3xl font-bold text-gray-900" data-i18n-th="แดชบอร์ดผู้อำนวยการ" data-i18n-en="Director Dashboard">แดชบอร์ดผู้อำนวยการ</h2>
        <p class="text-gray-600 mt-2">
            <span data-i18n-th="ยินดีต้อนรับ" data-i18n-en="Welcome">ยินดีต้อนรับ</span>
            <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
        </p>
    </div>

   <!-- สถิติหลัก -->
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

    @php
        $statCardBase = "stat-card group";
    @endphp

    <!-- Total teachers -->
    <button type="button"
        data-teacher-status-target="all"
        class="{{ $statCardBase }} border-green-200 focus:ring-green-400 bg-green-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-green-900/80" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">
                        จำนวนครูทั้งหมด
                    </p>
                    <p class="stat-card__value text-green-800">
                        {{ number_format($teacherCount ?? 0) }}
                    </p>
                </div>

                <div class="relative">
                    <div class="absolute -inset-2 rounded-2xl bg-green-200/40 blur-lg opacity-0 transition group-hover:opacity-100"></div>
                    <div class="stat-card__icon bg-gradient-to-br from-green-50 to-green-200 border-green-200">
                        <svg class="h-6 w-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5c-2.305 0-4.46-.65-6.16-1.922L12 14z" />
                        </svg>
                    </div>
                </div>
            </div>

            <p class="stat-card__footer text-green-700">
                <span data-i18n-th="รายชื่อครู" data-i18n-en="View teachers">รายชื่อครู</span>
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </p>
            <div class="stat-card__divider bg-gradient-to-r from-green-100 via-green-200 to-transparent"></div>
        </div>
    </button>

        <button type="button"
                data-teacher-status-target="complete"
                class="{{ $statCardBase }} border-sky-200 focus:ring-sky-400 bg-sky-100">
            <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <h3 class="text-gray-600" data-i18n-th="หลักสูตรที่เรียบร้อยแล้ว" data-i18n-en="Teachers with finished courses">ที่ทำหลักสูตรเสร็จ</h3>
                    <p class="stat-card__value text-sky-800">{{ number_format($completeTeacherCount ?? 0) }}</p>
                </div>
            </div>

            <p class="stat-card__footer text-sky-700">
                <span data-i18n-th="รายชื่อครู" data-i18n-en="View teachers">รายชื่อครู</span>
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </p>
            <div class="stat-card__divider bg-gradient-to-r from-sky-100 via-sky-200 to-transparent"></div>
        </div>
    </button>

        <button type="button"
                data-teacher-status-target="incomplete"
                class="{{ $statCardBase }} border-amber-200 focus:ring-amber-400 bg-amber-100">
            <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <h3 class="text-gray-600" data-i18n-th="หลักสูตรยังไม่เรียบร้อย" data-i18n-en="Teachers with unfinished courses">หลักสูตรยังไม่เสร็จ</h3>
                    <p class="stat-card__value text-amber-800">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
                </div>
            </div>

            <p class="stat-card__footer text-amber-700">
                <span data-i18n-th="รายชื่อครู" data-i18n-en="View teachers">รายชื่อครู</span>
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </p>
            <div class="stat-card__divider bg-gradient-to-r from-amber-100 via-amber-200 to-transparent"></div>
        </div>
    </button>

    <!-- Total classrooms (คลิก dropdown) -->
    <div id="student-room-section" class="{{ $statCardBase }} border-purple-200 focus:ring-purple-400 bg-purple-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-purple-900/80" data-i18n-th="ห้องเรียนทั้งหมด" data-i18n-en="Total classrooms">
                        ห้องเรียนทั้งหมด
                    </p>
                    <p class="stat-card__value text-purple-900">
                        {{ number_format($classCount ?? 0) }}
                    </p>
                </div>

                <div class="relative">
                    <div class="absolute -inset-2 rounded-2xl bg-purple-200/40 blur-lg opacity-0 transition group-hover:opacity-100"></div>
                    <div class="stat-card__icon bg-gradient-to-br from-purple-50 to-purple-200 border-purple-200">
                        <svg class="h-6 w-6 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 21h8m-8-4h8m-9-4h10M7 5h10l1 2H6l1-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <button type="button" id="roomDropdownToggle"
                class="stat-card__footer text-purple-700 hover:text-purple-900">
                <span data-i18n-th="นักเรียนรายห้อง" data-i18n-en="View students by room">นักเรียนรายห้อง</span>
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-purple-100 via-purple-200 to-transparent"></div>
        </div>
    </div>

</div>


    <!-- กราฟสัดส่วนครูตามสถานะชั่วโมงสอน -->
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row items-center gap-8">
        <div class="flex-1 space-y-3">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">สถานะหลักสูตร</h2>
                    <p class="text-sm text-gray-500">แสดงจำนวนครูที่ทำหลักสูตรเสร็จ ยังไม่เสร็จ และยังไม่ได้สร้างหลักสูตร</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-sky-50 border border-sky-100">
                        <span class="w-3 h-3 rounded-full bg-sky-500"></span>
                        <div>
                            <p class="text-xs text-gray-600">หลักสูตรเรียบร้อยแล้ว</p>
                            <p class="text-2xl font-bold text-sky-700">{{ number_format($completeTeacherCount ?? 0) }}</p>
                            <p class="text-xs text-sky-700/80 mt-1">คน</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-amber-50 border border-amber-100">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-xs text-gray-600">หลักสูตรยังไม่เรียบร้อย</p>
                            <p class="text-2xl font-bold text-amber-700">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
                            <p class="text-xs text-amber-700/80 mt-1">คน</p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-500">รวมครูทั้งหมด {{ number_format(($completeTeacherCount ?? 0) + ($incompleteTeacherCount ?? 0)) }} คน</p>
        </div>
        <div class="flex-1 flex justify-center">
                <div class="relative w-full max-w-xs">
                    <canvas id="teacherStatusChart" class="w-full h-full"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <p class="text-xs text-gray-500" data-i18n-th="ครูทั้งหมด" data-i18n-en="Total teachers">ครูทั้งหมด</p>
                        <p id="teacherStatusTotal" class="text-3xl font-bold text-gray-800">
                            {{ number_format(($completeTeacherCount ?? 0) + ($incompleteTeacherCount ?? 0)) }}
                        </p>
                    </div>
                    <div id="teacherStatusChartEmpty" class="absolute inset-0 hidden items-center justify-center text-sm text-gray-500 bg-white/80 rounded-full"
                         data-i18n-th="ยังไม่มีข้อมูลกราฟ" data-i18n-en="No chart data yet">
                        ยังไม่มีข้อมูลกราฟ
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- วิชาเอกของครู -->
    @php
        $majorsList = $allMajors ?? collect();
    @endphp
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800"
                    data-i18n-th="วิชาเอกของครู" data-i18n-en="Teacher majors">
                    วิชาเอกของครู
                </h2>
                <p class="text-sm text-gray-500"
                   data-i18n-th="เลือกวิชาเพื่อดูครูผู้รับผิดชอบ"
                   data-i18n-en="Pick a subject to see responsible teachers">
                    เลือกวิชาเพื่อดูครูผู้รับผิดชอบ
                </p>
            </div>
            <div class="flex items-center gap-2">
                <label for="directorMajorFilter" class="text-sm text-gray-600 hidden md:block"
                       data-i18n-th="เลือกวิชา:" data-i18n-en="Select subject:">
                    เลือกวิชา:
                </label>
                <select id="directorMajorFilter" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value=""
                            data-i18n-th="วิชาเอกทั้งหมด" data-i18n-en="All majors">
                        วิชาเอกทั้งหมด
                    </option>
                    @foreach($majorsList as $major)
                        <option value="{{ $major }}">{{ $major }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto -mx-2 md:mx-0">
            <table class="w-full min-w-[640px] border border-gray-200 rounded-xl overflow-hidden text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left"
                            data-i18n-th="วิชาเอก" data-i18n-en="Major">
                            วิชาเอก
                        </th>
                        <th class="py-3 px-4 text-center w-32"
                            data-i18n-th="ดูรายละเอียด" data-i18n-en="Details">
                            ดูรายละเอียด
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($majorsList as $major)
                        @php($teachersForMajor = ($teachersByMajor[$major] ?? collect()))
                        <tr class="hover:bg-blue-50 cursor-pointer" data-major-row="{{ trim($major) }}" data-has-teacher="{{ $teachersForMajor->isNotEmpty() ? '1' : '0' }}">
                            <td class="py-2 px-4 font-semibold text-gray-900">{{ $major }}</td>
                            <td class="py-2 px-4 text-center">
                                <button type="button"
                                        class="text-blue-600 hover:underline"
                                        data-major-toggle="{{ trim($major) }}">
                                    <span data-i18n-th="รายชื่อครู" data-i18n-en="View teachers">รายชื่อครู</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-blue-50/40" data-major-detail="{{ trim($major) }}">
                            <td colspan="2" class="py-3 px-4">
                                @if($teachersForMajor->isEmpty())
                                    <div class="text-gray-500 text-sm">
                                        <span data-i18n-th="ยังไม่ระบุครูผู้รับผิดชอบสำหรับวิชา" data-i18n-en="No teacher assigned for">ยังไม่ระบุครูผู้รับผิดชอบสำหรับวิชา</span>
                                        {{ $major }}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-800 space-y-2">
                                        <div class="font-semibold text-gray-900">
                                            <span data-i18n-th="ครูผู้รับผิดชอบ" data-i18n-en="Responsible teachers">ครูผู้รับผิดชอบ</span>
                                            ({{ $teachersForMajor->count() }} <span data-i18n-th="คน" data-i18n-en="people">คน</span>)
                                        </div>
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach($teachersForMajor as $teacher)
                                                <li class="flex items-center justify-between gap-3">
                                                    <span>
                                                        <span class="font-medium">{{ $teacher->name }}</span>
                                                        <span class="text-gray-500">({{ $teacher->email ?? '-' }})</span>
                                                    </span>
                                                    <a href="{{ route('director.teacher-plans', ['q' => $teacher->name]) }}"
                                                       class="text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-200 rounded-full px-3 py-1 text-xs font-semibold transition"
                                                       title="ดูรายละเอียดแผนสอนของครูคนนี้"
                                                       data-i18n-th="ดูรายละเอียด" data-i18n-en="Details">
                                                        ดูรายละเอียด
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-4 px-4 text-center text-gray-500"
                                data-i18n-th="ยังไม่มีข้อมูลวิชาเอก" data-i18n-en="No major data yet">
                                ยังไม่มีข้อมูลวิชาเอก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-3" id="directorMajorSummary"
           data-i18n-th="แสดงวิชาเอกทั้งหมด" data-i18n-en="Showing all majors">
            แสดงวิชาเอกทั้งหมด {{ $majorsList->count() }} วิชา
        </p>
    </div>

    <!-- หลักสูตรทั้งหมด -->
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100 mb-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800"
                    data-i18n-th="หลักสูตรที่รับผิดชอบ" data-i18n-en="Courses in charge">
                    หลักสูตรที่รับผิดชอบ
                </h2>
                <p class="text-sm text-gray-500"
                   data-i18n-th="ดูหลักสูตรทั้งหมดและครูผู้สอน"
                   data-i18n-en="See all courses and teachers">
                    ดูหลักสูตรทั้งหมดและครูผู้สอน
                </p>
            </div>
            <span class="text-sm text-gray-500">
                <span data-i18n-th="จำนวน" data-i18n-en="Total">จำนวน</span>
                {{ ($courses ?? collect())->count() }}
                <span data-i18n-th="หลักสูตร" data-i18n-en="courses">หลักสูตร</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left" data-i18n-th="ชื่อหลักสูตร" data-i18n-en="Course name">ชื่อหลักสูตร</th>
                        <th class="py-3 px-4 text-left" data-i18n-th="ครูผู้สอน" data-i18n-en="Teacher">ครูผู้สอน</th>
                        <th class="py-3 px-4 text-center" data-i18n-th="ห้อง" data-i18n-en="Room">ห้อง</th>
                        <th class="py-3 px-4 text-center" data-i18n-th="จัดการ" data-i18n-en="Actions">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse(($courses ?? collect()) as $course)
                        @php($roomsText = collect($course->rooms ?? [])->filter()->join(', '))
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 text-gray-900 font-medium">{{ $course->name }}</td>
                            <td class="py-2 px-4 text-gray-900">{{ $course->teacher->name ?? '-' }}</td>
                            <td class="py-2 px-4 text-center text-gray-700">{{ $roomsText !== '' ? $roomsText : '-' }}</td>
                            <td class="py-2 px-4 text-center">
                                <a href="{{ route('director.course-detail', $course) }}"
                                   class="text-blue-600 hover:underline"
                                   data-i18n-th="ดูรายละเอียด" data-i18n-en="Details">
                                   ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500"
                                data-i18n-th="ยังไม่มีหลักสูตรในระบบ" data-i18n-en="No courses yet">
                                ยังไม่มีหลักสูตรในระบบ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
<div id="teacherStatusModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/30 backdrop-blur-sm px-4 py-10">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 id="teacherStatusModalTitle" class="text-lg font-semibold text-gray-900">หลักสูตรเสร็จ</h3>
                <p id="teacherStatusModalSubtitle" class="text-sm text-gray-500">ทั้งหมด 0 คน</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-teacher-modal>&times;</button>
        </div>
        <div id="teacherStatusModalBody" class="p-6 max-h-[65vh] overflow-y-auto space-y-4"></div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200" data-close-teacher-modal>ปิด</button>
        </div>
    </div>
</div>
<div id="roomStudentsModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/30 backdrop-blur-sm px-4 py-10">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900" data-i18n-th="นักเรียนรายห้อง" data-i18n-en="Students by classroom">นักเรียนรายห้อง</h3>
                <p id="roomModalSubtitle" class="text-sm text-gray-500" data-i18n-th="เลือกห้องเพื่อดูรายชื่อ" data-i18n-en="Select classroom to view list">เลือกห้องเพื่อดูรายชื่อ</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-room-modal>&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label for="roomModalSelect"
                       class="block text-sm font-semibold text-gray-700 mb-2"
                       data-i18n-th="เลือกห้องเรียน" data-i18n-en="Select classroom">เลือกห้องเรียน</label>
                <select id="roomModalSelect"
                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value=""
                            data-i18n-th="เลือกห้องเรียน" data-i18n-en="Select classroom">เลือกห้องเรียน</option>
                    @foreach(($roomOptions ?? collect()) as $room)
                        <option value="{{ $room }}">{{ $room }} ({{ number_format(collect($studentsByRoomPayload[$room] ?? [])->count()) }} students)</option>
                    @endforeach
                </select>
            </div>
            <p id="roomModalSummary" class="text-sm text-gray-600"></p>
            <div id="roomModalBody" class="max-h-[50vh] overflow-y-auto space-y-2"></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200" data-close-room-modal>ปิด</button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const majorFilter = document.getElementById('directorMajorFilter');
        const majorRows = Array.from(document.querySelectorAll('[data-major-row]'));
        const detailRows = Array.from(document.querySelectorAll('[data-major-detail]'));
        const summary = document.getElementById('directorMajorSummary');
        const teacherStatusData = {
            complete: @json($completeTeachers ?? []),
            incomplete: @json($incompleteTeachers ?? []),
            all: @json($homeroomTeachers ?? []),
        };
        const statusButtons = Array.from(document.querySelectorAll('[data-teacher-status-target]'));
        const statusModal = document.getElementById('teacherStatusModal');
        const statusModalTitle = document.getElementById('teacherStatusModalTitle');
        const statusModalSubtitle = document.getElementById('teacherStatusModalSubtitle');
        const statusModalBody = document.getElementById('teacherStatusModalBody');
        const statusChart = document.getElementById('teacherStatusChart');
        const statusChartEmpty = document.getElementById('teacherStatusChartEmpty');
        const statusChartTotal = document.getElementById('teacherStatusTotal');
        const roomDropdownToggle = document.getElementById('roomDropdownToggle');
        const roomModal = document.getElementById('roomStudentsModal');
        const roomModalSelect = document.getElementById('roomModalSelect');
        const roomModalSummary = document.getElementById('roomModalSummary');
        const roomModalBody = document.getElementById('roomModalBody');
        const roomStudentsData = @json($studentsByRoomPayload ?? []);

        const hideAllDetails = () => {
            detailRows.forEach(row => row.classList.add('hidden'));
            document.querySelectorAll('[data-major-toggle]').forEach(btn => btn.textContent = 'รายชื่อครู');
        };

        const applyFilter = () => {
            const selected = (majorFilter?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            majorRows.forEach(row => {
                const rowMajor = (row.dataset.majorRow || '').toLowerCase();
                const match = !selected || rowMajor === selected;
                row.style.display = match ? '' : 'none';

                const detailRow = document.querySelector(`[data-major-detail="${row.dataset.majorRow}"]`);
                if (detailRow) {
                    detailRow.style.display = match ? detailRow.style.display : 'none';
                }
                if (match) visibleCount += 1;
            });

            if (summary) {
                summary.textContent = selected
                    ? `แสดงเฉพาะวิชา ${majorFilter.value} (${visibleCount} รายการ)`
                    : `แสดงวิชาเอกทั้งหมด ${majorRows.length} วิชา`;
            }
        };

        const closeRoomModal = () => {
            roomModal?.classList.add('hidden');
            roomModal?.classList.remove('flex');
        };

        const openRoomModal = () => {
            if (!roomModal) {
                return;
            }

            roomModal.classList.remove('hidden');
            roomModal.classList.add('flex');

            if (roomModalSelect?.value) {
                renderRoomStudents(roomModalSelect.value);
            } else {
                renderRoomStudents('');
            }
            roomModalSelect?.focus();
        };

        const renderRoomStudents = (room) => {
            if (!roomModalSummary || !roomModalBody) {
                return;
            }

            const list = room ? (roomStudentsData[room] || []) : [];

            if (!room) {
                roomModalSummary.textContent = '';
                roomModalBody.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">กรุณาเลือกห้องเรียน</p>';
                return;
            }

            roomModalSummary.textContent = `ห้อง ${room} - ${list.length} คน`;
            roomModalBody.innerHTML = '';

            if (!list.length) {
                roomModalBody.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">ไม่พบนักเรียนในห้องนี้</p>';
                return;
            }

            list.forEach((student, index) => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 border rounded-xl bg-purple-50 border-purple-100';
                item.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-gray-900">${student.name || '-'}</p>
                        <span class="text-xs text-gray-500">รหัส ${student.student_code || '-'}</span>
                    </div>
                `;
                roomModalBody.appendChild(item);

                if (index < list.length - 1) {
                    const separator = document.createElement('div');
                    separator.className = 'h-0.5 bg-purple-200 rounded-full my-2';
                    roomModalBody.appendChild(separator);
                }
            });
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const getCourseProgress = (course) => {
            const hasCoursePlan = Boolean(course?.has_hours);
            const hasAssignments = Boolean(course?.has_assignments);
            const isComplete = Boolean(course?.complete);

            if (isComplete || (hasCoursePlan && hasAssignments)) {
                return {
                    badgeClass: 'bg-green-100 text-green-700',
                    badgeText: 'เสร็จแล้ว',
                    detailText: 'เสร็จแล้ว: หลักสูตร และกำหนดงาน',
                };
            }

            if (hasCoursePlan && !hasAssignments) {
                return {
                    badgeClass: 'bg-amber-100 text-amber-700',
                    badgeText: 'เสร็จบางส่วน',
                    detailText: 'เสร็จแล้ว: หลักสูตร | ค้าง: กำหนดงาน',
                };
            }

            if (!hasCoursePlan && hasAssignments) {
                return {
                    badgeClass: 'bg-amber-100 text-amber-700',
                    badgeText: 'เสร็จบางส่วน',
                    detailText: 'เสร็จแล้ว: กำหนดงาน | ค้าง: หลักสูตร',
                };
            }

            return {
                badgeClass: 'bg-rose-100 text-rose-700',
                badgeText: 'ยังไม่เสร็จ',
                detailText: 'ค้าง: หลักสูตร และกำหนดงาน',
            };
        };

        const UNKNOWN_GRADE_LABEL = 'ไม่ระบุชั้น';

        const normalizeGradeLabel = (grade) => {
            const value = String(grade ?? '').trim();
            return value !== '' ? value : UNKNOWN_GRADE_LABEL;
        };

        const uniqueCourses = (courses) => {
            const seen = new Set();
            return (courses || []).filter((course) => {
                const key = `${course?.id ?? ''}|${course?.name ?? ''}|${normalizeGradeLabel(course?.grade)}`;
                if (seen.has(key)) {
                    return false;
                }
                seen.add(key);
                return true;
            });
        };

        const getTeacherRecords = (statusKey) => {
            const source = statusKey === 'all'
                ? [...(teacherStatusData.complete || []), ...(teacherStatusData.incomplete || [])]
                : (teacherStatusData[statusKey] || []);

            const byTeacher = new Map();

            source.forEach((item, index) => {
                const teacherInfo = item?.teacher || item || {};
                const teacherKey = String(
                    teacherInfo.id ?? teacherInfo.email ?? `${teacherInfo.name ?? 'teacher'}-${index}`
                );

                const current = byTeacher.get(teacherKey) || {
                    teacher: teacherInfo,
                    courses: [],
                    complete: Boolean(item?.complete),
                };

                const courses = Array.isArray(item?.courses) ? item.courses : [];
                current.courses.push(...courses);
                current.complete = current.complete && Boolean(item?.complete);
                byTeacher.set(teacherKey, current);
            });

            return Array.from(byTeacher.values()).map((entry) => {
                const courses = uniqueCourses(entry.courses || []);
                return {
                    teacher: entry.teacher || {},
                    courses,
                    complete: courses.length ? courses.every((course) => Boolean(course?.complete)) : false,
                };
            });
        };

        const groupTeachersByGrade = (records) => {
            const grouped = new Map();

            records.forEach((record) => {
                const courseList = Array.isArray(record.courses) ? record.courses : [];
                const gradeList = courseList.length
                    ? Array.from(new Set(courseList.map((course) => normalizeGradeLabel(course?.grade))))
                    : [UNKNOWN_GRADE_LABEL];

                gradeList.forEach((grade) => {
                    if (!grouped.has(grade)) {
                        grouped.set(grade, []);
                    }

                    grouped.get(grade).push({
                        ...record,
                        courses: courseList.filter((course) => normalizeGradeLabel(course?.grade) === grade),
                    });
                });
            });

            return Array.from(grouped.entries()).sort(([gradeA], [gradeB]) => {
                if (gradeA === UNKNOWN_GRADE_LABEL) return 1;
                if (gradeB === UNKNOWN_GRADE_LABEL) return -1;
                return gradeA.localeCompare(gradeB, 'th');
            });
        };

        const renderTeacherStatus = (statusKey) => {
            if (!statusModalBody) return;

            const list = getTeacherRecords(statusKey);
            statusModalBody.innerHTML = '';

            if (!list.length) {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-gray-500';
                empty.textContent = 'ยังไม่มีข้อมูลในหมวดนี้';
                statusModalBody.appendChild(empty);
                return;
            }

            const groupedSections = groupTeachersByGrade(list);

            groupedSections.forEach(([grade, teachers]) => {
                const section = document.createElement('section');
                section.className = 'rounded-2xl border border-gray-100 bg-gray-50/40 p-4 space-y-3';

                const gradeLabel = grade === UNKNOWN_GRADE_LABEL ? UNKNOWN_GRADE_LABEL : `ชั้น ${grade}`;
                section.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-semibold text-gray-800">${escapeHtml(gradeLabel)}</h4>
                        <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
                            ${teachers.length} คน
                        </span>
                    </div>
                `;

                const cardsContainer = document.createElement('div');
                cardsContainer.className = 'space-y-3';

                teachers.forEach((item) => {
                    const teacherInfo = item.teacher || {};
                    const card = document.createElement('div');

                    const isCompleteStatus = statusKey === 'complete';
                    const isIncompleteStatus = statusKey === 'incomplete';
                    const cardColor = isCompleteStatus
                        ? 'bg-sky-50 border-sky-100'
                        : isIncompleteStatus
                            ? 'bg-amber-50 border-amber-100'
                            : 'bg-slate-50 border-slate-200';
                    card.className = `border rounded-2xl p-4 shadow-sm ${cardColor}`;

                    const showCourses = isCompleteStatus || isIncompleteStatus;
                    const rawCourses = showCourses && Array.isArray(item.courses) ? item.courses : [];
                    const courses = isIncompleteStatus
                        ? [...rawCourses].sort((a, b) => Number(a.complete) - Number(b.complete))
                        : rawCourses;
                    const incompleteCourses = courses.filter((course) => !course.complete);

                    let incompleteGradeSummary = '';
                    if (isIncompleteStatus) {
                        if (!courses.length) {
                            incompleteGradeSummary = `
                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-100/70 px-3 py-2 text-xs text-amber-900">
                                    ยังไม่มีรายวิชาที่ได้รับมอบหมาย
                                </div>
                            `;
                        } else if (!incompleteCourses.length) {
                            incompleteGradeSummary = `
                                <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-100/70 px-3 py-2 text-xs text-emerald-800">
                                    รายวิชาทั้งหมดเรียบร้อยแล้ว
                                </div>
                            `;
                        } else {
                            const grouped = incompleteCourses.reduce((acc, course) => {
                                const subjectName = String(course?.name || 'ไม่ระบุรายวิชา').trim() || 'ไม่ระบุรายวิชา';
                                const gradeName = normalizeGradeLabel(course?.grade);
                                if (!acc[subjectName]) {
                                    acc[subjectName] = new Set();
                                }
                                acc[subjectName].add(gradeName);
                                return acc;
                            }, {});

                            const summaryRows = Object.entries(grouped)
                                .map(([subjectName, grades]) => `
                                    <div>
                                        <span class="font-semibold">${escapeHtml(subjectName)}</span>:
                                        ชั้นที่ยังไม่เรียบร้อย ${escapeHtml(Array.from(grades).join(', '))}
                                    </div>
                                `)
                                .join('');

                            incompleteGradeSummary = `
                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-100/70 px-3 py-2">
                                    <p class="text-xs font-semibold text-amber-900">ชั้นที่ยังไม่เรียบร้อย</p>
                                    <div class="mt-1 space-y-1 text-xs text-amber-900">${summaryRows}</div>
                                </div>
                            `;
                        }
                    }

                    const coursesHtml = !showCourses
                        ? ''
                        : (courses.map((course) => {
                            const progress = getCourseProgress(course);
                            const gradeText = String(course?.grade || '').trim() !== ''
                                ? `ชั้นเรียน ${course.grade}`
                                : 'ไม่ระบุชั้นเรียน';

                            return `
                                <div class="rounded-xl border border-gray-200 bg-white/80 px-3 py-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-900">${escapeHtml(course?.name || '-')}</div>
                                            <div class="text-xs text-gray-500">${escapeHtml(gradeText)}</div>
                                            <div class="text-xs text-gray-600 mt-1">${escapeHtml(progress.detailText)}</div>
                                        </div>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full ${progress.badgeClass}">
                                            ${escapeHtml(progress.badgeText)}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }).join('') || '<div class="text-xs text-gray-500">ยังไม่มีรายวิชา</div>');

                    const coursesSection = coursesHtml
                        ? `<div class="mt-3 space-y-2">${incompleteGradeSummary}${coursesHtml}</div>`
                        : incompleteGradeSummary;

                    const headerBadgeClass = isCompleteStatus
                        ? 'bg-sky-100 text-sky-700'
                        : isIncompleteStatus
                            ? 'bg-amber-100 text-amber-700'
                            : 'bg-slate-200 text-slate-700';
                    const headerBadgeText = isCompleteStatus
                        ? 'หลักสูตรเสร็จแล้ว'
                        : isIncompleteStatus
                            ? 'หลักสูตรยังไม่เสร็จ'
                            : 'ข้อมูลครู';

                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-base font-semibold text-gray-900">${escapeHtml(teacherInfo.name || '-')}</div>
                                <div class="text-xs text-gray-500">${escapeHtml(teacherInfo.email || '')}</div>
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full ${headerBadgeClass}">
                                ${escapeHtml(headerBadgeText)}
                            </span>
                        </div>
                        ${coursesSection}
                    `;

                    cardsContainer.appendChild(card);
                });

                section.appendChild(cardsContainer);
                statusModalBody.appendChild(section);
            });
        };
        const openStatusModal = (statusKey) => {
            if (!statusModal) return;

            const isComplete = statusKey === 'complete';
            const isIncomplete = statusKey === 'incomplete';
            const isAll = statusKey === 'all';
            const count = getTeacherRecords(statusKey).length;

            if (statusModalTitle) {
                statusModalTitle.textContent = statusKey === 'complete'
                    ? 'คุณครูที่ทำหลักสูตรเรียบร้อยแล้ว'
                    : statusKey === 'incomplete'
                        ? 'คุณครูที่ทำหลักสูตรยังไม่เรียบร้อย'
                        : 'ครูทั้งหมด';

            statusModalSubtitle.textContent = `ทั้งหมด ${count} คน`;
            }

            renderTeacherStatus(statusKey);
            statusModal.classList.remove('hidden');
            statusModal.classList.add('flex');
        };

        document.querySelectorAll('[data-major-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const major = btn.dataset.majorToggle;
                const detailRow = document.querySelector(`[data-major-detail="${major}"]`);
                if (!detailRow) return;

                const isHidden = detailRow.classList.contains('hidden');
                hideAllDetails();

                if (isHidden) {
                    detailRow.classList.remove('hidden');
                    btn.textContent = 'ซ่อนรายชื่อ';
                }
            });
        });

        if (majorFilter) {
            majorFilter.addEventListener('change', () => {
                hideAllDetails();
                applyFilter();
            });
        }

        statusButtons.forEach(btn => {
            btn.addEventListener('click', () => openStatusModal(btn.dataset.teacherStatusTarget));
        });

        roomDropdownToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            openRoomModal();
        });

        roomModalSelect?.addEventListener('change', (event) => {
            renderRoomStudents(event.target.value);
        });

        document.querySelectorAll('[data-close-room-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                closeRoomModal();
            });
        });

        roomModal?.addEventListener('click', (event) => {
            if (event.target === roomModal) {
                closeRoomModal();
            }
        });

        const handleStudentRoomHash = () => {
            if (window.location.hash === '#student-room-section') {
                openRoomModal();
            }
        };
        handleStudentRoomHash();
        window.addEventListener('hashchange', handleStudentRoomHash);

        document.querySelectorAll('[data-close-teacher-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                statusModal?.classList.add('hidden');
                statusModal?.classList.remove('flex');
            });
        });

        statusModal?.addEventListener('click', (e) => {
            if (e.target === statusModal) {
                statusModal.classList.add('hidden');
                statusModal.classList.remove('flex');
            }
        });

        applyFilter();

        // Allow clicking the whole row to toggle details (in addition to the button)
        majorRows.forEach(row => {
            row.addEventListener('click', (e) => {
                // Prevent double toggle when the button itself was clicked
                if (e.target.closest('button')) {
                    return;
                }

                const major = row.dataset.majorRow;
                const toggleBtn = document.querySelector(`[data-major-toggle="${major}"]`);
                if (!toggleBtn) return;

                const detailRow = document.querySelector(`[data-major-detail="${major}"]`);
                if (!detailRow) return;

                const isHidden = detailRow.classList.contains('hidden');
                hideAllDetails();

                if (isHidden) {
                    detailRow.classList.remove('hidden');
                    toggleBtn.textContent = 'ซ่อนรายชื่อ';
                }
            });
        });

        // Pie chart: ครูชั่วโมงสอนครบ / ไม่ครบ
        if (statusChart && window.Chart) {
            const completeCount = Number(@json($completeTeacherCount ?? 0));
            const incompleteCount = Number(@json($incompleteTeacherCount ?? 0));
            const total = completeCount + incompleteCount;

            if (statusChartTotal) {
                statusChartTotal.textContent = total.toLocaleString();
            }

            if (total === 0) {
                statusChart.classList.add('opacity-30');
                statusChartEmpty?.classList.remove('hidden');
            } else {
                statusChartEmpty?.classList.add('hidden');

                const ctx = statusChart.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    
                    data: {
                        labels: ['ครูที่ทำหลักสูตรเรียบร้อยแล้ว', 'ครูที่ทำหลักสูตรยังไม่เรียบร้อย'],
                        datasets: [{
                            data: [completeCount, incompleteCount],
                            backgroundColor: ['#0ea5e9', '#f59e0b'],
                            borderWidth: 0,
                            hoverOffset: 6,
                        }],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const value = context.parsed;
                                        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${context.label}: ${value.toLocaleString()} คน (${percent}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            }
        }
    });
</script>
@endsection
