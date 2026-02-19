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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

        <div class="relative p-6 min-h-[190px] bg-gradient-to-br from-blue-50 to-blue-200 border border-blue-500 rounded-2xl shadow">
            <div class="pr-16">
                <h3 class="text-gray-600 leading-tight min-h-[4.5rem]" data-i18n-th="จำนวนนักเรียนทั้งหมด" data-i18n-en="Total students">จำนวนนักเรียนทั้งหมด</h3>
                <p class="text-sm mt-2 invisible select-none">ดูรายชื่อครู</p>
            </div>
            <p class="absolute top-6 right-6 text-4xl font-bold text-blue-800 text-right leading-none">{{ number_format($studentCount ?? 0) }}</p>
        </div>


        <div id="student-room-section" class="relative p-6 min-h-[190px] bg-gradient-to-br from-purple-50 to-purple-200 border border-purple-300 rounded-2xl shadow w-full text-left transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="pr-16">
                <h3 class="text-gray-600 leading-tight min-h-[4.5rem]" data-i18n-th="ห้องเรียนทั้งหมด" data-i18n-en="Total classrooms">ห้องเรียนทั้งหมด</h3>
                <button type="button" id="roomDropdownToggle" class="text-sm text-purple-800 mt-2 underline hover:text-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-400 rounded" data-i18n-th="ดูนักเรียนรายห้อง" data-i18n-en="View students by room">ดูนักเรียนรายห้อง</button>
            </div>
            <p class="absolute top-6 right-6 text-4xl font-bold text-purple-800 text-right leading-none">{{ number_format($classCount ?? 0) }}</p>
            <div id="roomDropdownPanel"
                 class="hidden absolute left-0 top-full mt-3 w-[22rem] max-w-[85vw] rounded-2xl border border-purple-200 bg-white p-4 shadow-2xl z-30">
                <label for="roomSelect" class="block text-sm font-medium text-gray-700 mb-1">Select classroom</label>
                <select id="roomSelect"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Select classroom</option>
                    @foreach(($roomOptions ?? collect()) as $room)
                        <option value="{{ $room }}">{{ $room }} ({{ number_format(collect($studentsByRoomPayload[$room] ?? [])->count()) }} students)</option>
                    @endforeach
                </select>
                <p id="roomDropdownSummary" class="text-sm text-gray-600 mt-3 mb-2"></p>
                <div id="roomDropdownBody" class="max-h-72 overflow-y-auto space-y-2"></div>
            </div>
        </div>

         <button type="button"
                data-teacher-status-target="all"
                class="relative p-6 min-h-[190px] bg-gradient-to-br from-green-50 to-green-200 border border-green-300 rounded-2xl shadow w-full text-left transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2">
            <div class="pr-16">
                <h3 class="text-gray-600 leading-tight min-h-[4.5rem]" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">จำนวนครูทั้งหมด</h3>
                <p class="text-sm text-green-800 mt-2 underline" data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</p>
            </div>
            <p class="absolute top-6 right-6 text-4xl font-bold text-green-800 text-right leading-none">{{ number_format($teacherCount ?? 0) }}</p>
        </button>

        <button type="button"
                data-teacher-status-target="complete"
                class="relative p-6 min-h-[190px] bg-gradient-to-br from-sky-50 to-sky-200 border border-sky-300 rounded-2xl shadow w-full text-left transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2">
            <div class="pr-16">
                <h3 class="text-gray-600 leading-tight min-h-[4.5rem]" data-i18n-th="หลักสูตรเสร็จแล้ว" data-i18n-en="Teachers with finished courses">หลักสูตรเสร็จแล้ว</h3>
                <p class="text-sm text-sky-800 mt-2 underline" data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</p>
            </div>
            <p class="absolute top-6 right-6 text-4xl font-bold text-sky-800 text-right leading-none">{{ number_format($completeTeacherCount ?? 0) }}</p>
        </button>

        <button type="button"
                data-teacher-status-target="incomplete"
                class="relative p-6 min-h-[190px] bg-gradient-to-br from-amber-50 to-amber-200 border border-amber-300 rounded-2xl shadow w-full text-left transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
            <div class="pr-16">
                <h3 class="text-gray-600 leading-tight min-h-[4.5rem]" data-i18n-th="หลักสูตรยังไม่เสร็จ" data-i18n-en="Teachers with unfinished courses">หลักสูตรยังไม่เสร็จ</h3>
                <p class="text-sm text-amber-800 mt-2 underline" data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</p>
            </div>
            <p class="absolute top-6 right-6 text-4xl font-bold text-amber-800 text-right leading-none">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
        </button>

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
                            <p class="text-xs text-gray-600">ครูที่ทำหลักสูตรเสร็จ</p>
                            <p class="text-2xl font-bold text-sky-700">{{ number_format($completeTeacherCount ?? 0) }}</p>
                            <p class="text-xs text-sky-700/80 mt-1">คน</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-amber-50 border border-amber-100">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-xs text-gray-600">ครูที่ทำหลักสูตรยังไม่เสร็จ</p>
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
                                    <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
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
        const roomDropdownPanel = document.getElementById('roomDropdownPanel');
        const roomSelect = document.getElementById('roomSelect');
        const roomDropdownSummary = document.getElementById('roomDropdownSummary');
        const roomDropdownBody = document.getElementById('roomDropdownBody');
        const roomStudentsData = @json($studentsByRoomPayload ?? []);

        const hideAllDetails = () => {
            detailRows.forEach(row => row.classList.add('hidden'));
            document.querySelectorAll('[data-major-toggle]').forEach(btn => btn.textContent = 'ดูรายชื่อครู');
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

        const closeRoomDropdown = () => {
            roomDropdownPanel?.classList.add('hidden');
        };

        const openRoomDropdown = () => {
            if (!roomDropdownPanel) {
                return;
            }

            roomDropdownPanel.classList.remove('hidden');
            if (roomSelect?.value) {
                renderRoomStudents(roomSelect.value);
            } else {
                renderRoomStudents('');
            }
            roomSelect?.focus();
        };

        const renderRoomStudents = (room) => {
            if (!roomDropdownSummary || !roomDropdownBody) {
                return;
            }

            const list = room ? (roomStudentsData[room] || []) : [];

            if (!room) {
                roomDropdownSummary.textContent = '';
                roomDropdownBody.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">Please select a classroom</p>';
                return;
            }

            roomDropdownSummary.textContent = `Room ${room} - ${list.length} students`;
            roomDropdownBody.innerHTML = '';

            if (!list.length) {
                roomDropdownBody.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">No students in this classroom</p>';
                return;
            }

            list.forEach((student, index) => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 border rounded-xl bg-purple-50 border-purple-100';
                item.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-gray-900">${student.name || '-'}</p>
                        <span class="text-xs text-gray-500">Code ${student.student_code || '-'}</span>
                    </div>
                `;
                roomDropdownBody.appendChild(item);

                if (index < list.length - 1) {
                    const separator = document.createElement('div');
                    separator.className = 'h-0.5 bg-purple-200 rounded-full my-2';
                    roomDropdownBody.appendChild(separator);
                }
            });
        };

        const renderTeacherStatus = (statusKey) => {
            if (!statusModalBody) return;

            const list = teacherStatusData[statusKey] || [];
            statusModalBody.innerHTML = '';

            if (!list.length) {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-gray-500';
                empty.textContent = 'ยังไม่มีข้อมูลในหมวดนี้';
                statusModalBody.appendChild(empty);
                return;
            }

            list.forEach((item) => {
                const teacherInfo = item.teacher || item || {};
                const card = document.createElement('div');
                const isCompleteStatus = statusKey === 'complete';
                const cardColor = isCompleteStatus ? 'bg-sky-50 border-sky-100' : 'bg-amber-50 border-amber-100';
                card.className = `border rounded-2xl p-4 shadow-sm ${cardColor}`;
                const showCourses = statusKey === 'complete' || statusKey === 'incomplete';
                const courses = showCourses && Array.isArray(item.courses) ? item.courses : [];
                const coursesHtml = !showCourses
                    ? ''
                    : (courses.map((course) => {
                        const statusClass = course.complete ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                        const detailText = course.complete
                            ? 'ครบ'
                            : [
                                course.has_hours ? null : 'ขาดชั่วโมงสอน',
                                course.has_assignments ? null : 'ขาดงานที่มอบหมาย',
                            ].filter(Boolean).join(', ') || 'ไม่ครบ';
                        const gradeText = course.grade ? `ชั้นเรียน ${course.grade}` : '';

                        return `
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">${course.name || '-'}</div>
                                    <div class="text-xs text-gray-500">${gradeText}</div>
                                </div>
                                <span class="text-xs font-semibold px-3 py-1 rounded-full ${
                                statusKey === 'complete'
                                    ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                                    : statusKey === 'incomplete'
                                        ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
                                : 'ไม่มีข้อมูล'
                            }">
                            ${
                                statusKey === 'complete'
                                    ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                                    : statusKey === 'incomplete'
                                    ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
                                : 'ไม่มีข้อมูล'
                            }
                        </span>
                            </div>
                        `;
                    }).join('') || '<div class="text-xs text-gray-500">ยังไม่มีรายวิชา</div>');

                const coursesSection = coursesHtml
                    ? `<div class="mt-3 space-y-2">${coursesHtml}</div>`
                    : '';

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-base font-semibold text-gray-900">${teacherInfo.name || '-'}</div>
                            <div class="text-xs text-gray-500">${teacherInfo.email || ''}</div>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full ${
                                statusKey === 'complete'
                                    ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                                    : statusKey === 'incomplete'
                                        ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
                                : 'ไม่มีข้อมูล'
                            }">
                            ${
                                statusKey === 'complete'
                                    ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                                : statusKey === 'incomplete'
                                    ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
                                    : 'ไม่มีข้อมูล'
                            }
                        </span>
                    </div>
                    ${coursesSection}
                `;

                statusModalBody.appendChild(card);
            });
        };

        const openStatusModal = (statusKey) => {
            if (!statusModal) return;

            const isComplete = statusKey === 'complete';
            const isIncomplete = statusKey === 'incomplete';
            const isAll = statusKey === 'all';
            const count = teacherStatusData[statusKey]?.length ?? 0;

            if (statusModalTitle) {
                statusModalTitle.textContent = statusKey === 'complete'
                    ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                    : statusKey === 'incomplete'
                        ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
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
            if (!roomDropdownPanel) {
                return;
            }

            const shouldOpen = roomDropdownPanel.classList.contains('hidden');
            if (!shouldOpen) {
                closeRoomDropdown();
                return;
            }

            openRoomDropdown();
        });

        roomSelect?.addEventListener('change', (event) => {
            renderRoomStudents(event.target.value);
        });

        roomDropdownPanel?.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', (event) => {
            if (!roomDropdownPanel || roomDropdownPanel.classList.contains('hidden')) {
                return;
            }
            const clickedInsidePanel = roomDropdownPanel.contains(event.target);
            const clickedToggle = roomDropdownToggle?.contains(event.target);
            if (!clickedInsidePanel && !clickedToggle) {
                closeRoomDropdown();
            }
        });

        const handleStudentRoomHash = () => {
            if (window.location.hash === '#student-room-section') {
                openRoomDropdown();
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
                        labels: ['ครูที่ทำหลักสูตรเสร็จ', 'ครูที่ทำหลักสูตรยังไม่เสร็จ'],
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
