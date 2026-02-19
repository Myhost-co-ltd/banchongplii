@extends('layouts.layout-admin')

@section('title', 'แดชบอร์ดผู้ดูแลระบบ')

@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-2" data-i18n-th="แดชบอร์ดผู้ดูแลระบบ" data-i18n-en="Admin Dashboard">แดชบอร์ดผู้ดูแลระบบ</h1>
<p class="text-gray-600 mb-6" data-i18n-th="ยินดีต้อนรับ ผู้ดูแลระบบ" data-i18n-en="Welcome, Admin">ยินดีต้อนรับ ผู้ดูแลระบบ</p>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
    @php
        $statCardBase = "stat-card group";
    @endphp

    <!-- จำนวนครู -->
    <div class="{{ $statCardBase }} border-green-200 bg-green-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-green-900/80" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">จำนวนครูทั้งหมด</p>
                    <p class="stat-card__value text-green-800">{{ number_format($teacherCount ?? 0) }}</p>
                </div>
                <div class="stat-card__icon bg-gradient-to-br from-green-50 to-green-200 border-green-200">
                    <svg class="h-6 w-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5c-2.305 0-4.46-.65-6.16-1.922L12 14z" />
                    </svg>
                </div>
            </div>
            <button type="button"
                    class="stat-card__footer text-green-700 hover:text-green-900"
                    onclick="toggleSummaryPeopleModal('teachers')">
                <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-green-100 via-green-200 to-transparent"></div>
        </div>
    </div>

    <!-- จำนวนนักเรียน -->
    <div class="{{ $statCardBase }} border-blue-200 bg-blue-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-blue-900/80" data-i18n-th="จำนวนนักเรียนทั้งหมด" data-i18n-en="Total students">จำนวนนักเรียนทั้งหมด</p>
                    <p class="stat-card__value text-blue-800">{{ number_format($studentCount ?? 0) }}</p>
                </div>
                <div class="stat-card__icon bg-gradient-to-br from-blue-50 to-blue-200 border-blue-200">
                    <svg class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1m-6 6H2v-2a4 4 0 014-4h1m6-6a4 4 0 11-8 0 4 4 0 018 0zm10 4a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <button type="button"
                    class="stat-card__footer text-blue-700 hover:text-blue-900"
                    onclick="toggleSummaryPeopleModal('students')">
                <span data-i18n-th="ดูรายชื่อนักเรียน" data-i18n-en="View students">ดูรายชื่อนักเรียน</span>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-blue-100 via-blue-200 to-transparent"></div>
        </div>
    </div>

    <!-- จำนวนห้อง -->
    <div class="{{ $statCardBase }} border-purple-200 bg-purple-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-purple-900/80" data-i18n-th="จำนวนห้องเรียน" data-i18n-en="Total classrooms">จำนวนห้องเรียน</p>
                    <p class="stat-card__value text-purple-800">{{ number_format($classroomCount ?? 0) }}</p>
                </div>
                <div class="stat-card__icon bg-gradient-to-br from-purple-50 to-purple-200 border-purple-200">
                    <svg class="h-6 w-6 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8m-8-4h8m-9-4h10M7 5h10l1 2H6l1-2z" />
                    </svg>
                </div>
            </div>
            <button type="button"
                    id="roomDropdownToggle"
                    class="stat-card__footer text-purple-700 hover:text-purple-900"
                    onclick="toggleRoomDropdown(event)">
                <span data-i18n-th="ดูนักเรียนรายห้อง" data-i18n-en="View students by room">ดูนักเรียนรายห้อง</span>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-purple-100 via-purple-200 to-transparent"></div>
        </div>
    </div>

    <!-- คุณครูที่ทำหลักสูตรเสร็จ -->
    <div class="{{ $statCardBase }} border-sky-200 bg-sky-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-sky-900/80" data-i18n-th="หลักสูตรเสร็จแล้ว" data-i18n-en="Teachers with finished courses">หลักสูตรเสร็จแล้ว</p>
                    <p class="stat-card__value text-sky-800">{{ number_format($completeTeacherCount ?? 0) }}</p>
                </div>
                <div class="stat-card__icon bg-gradient-to-br from-sky-50 to-sky-200 border-sky-200">
                    <svg class="h-6 w-6 text-sky-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <button type="button"
                    class="stat-card__footer text-sky-700 hover:text-sky-900"
                    onclick="toggleTeacherModal('complete')">
                <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-sky-100 via-sky-200 to-transparent"></div>
        </div>
    </div>

    <!-- คุณครูที่ทำหลักสูตรยังไม่เสร็จ -->
    <div class="{{ $statCardBase }} border-amber-200 bg-amber-100">
        <div class="stat-card__body">
            <div class="stat-card__top">
                <div class="stat-card__content">
                    <p class="stat-card__label text-amber-900/80" data-i18n-th="หลักสูตรยังไม่เสร็จ" data-i18n-en="Teachers with unfinished courses">หลักสูตรยังไม่เสร็จ</p>
                    <p class="stat-card__value text-amber-800">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
                </div>
                <div class="stat-card__icon bg-gradient-to-br from-amber-50 to-amber-200 border-amber-200">
                    <svg class="h-6 w-6 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <button type="button"
                    class="stat-card__footer text-amber-700 hover:text-amber-900"
                    onclick="toggleTeacherModal('incomplete')">
                <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
            </button>
            <div class="stat-card__divider bg-gradient-to-r from-amber-100 via-amber-200 to-transparent"></div>
        </div>
    </div>

</div>

<!-- ภาพรวมชั่วโมงสอนของครู -->
<div class="mt-10 bg-white rounded-3xl shadow p-8 border border-gray-100">
    <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-12">
        <div class="flex-1 space-y-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">สถานะหลักสูตร</h2>
                <p class="text-sm text-gray-500">แบ่งสัดส่วนครูที่ทำหลักสูตรเสร็จ ยังไม่เสร็จ และยังไม่ได้สร้างหลักสูตร</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100">
                    <div class="flex items-center gap-2 text-sky-600 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        หลักสูตรเสร็จแล้ว
                    </div>
                    <p class="text-3xl font-bold text-sky-700 mt-2">{{ number_format($completeTeacherCount ?? 0) }}</p>
                    <p class="text-sm text-sky-700/70">คน</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <div class="flex items-center gap-2 text-amber-600 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        หลักสูตรยังไม่เสร็จ
                    </div>
                    <p class="text-3xl font-bold text-amber-700 mt-2">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
                    <p class="text-sm text-amber-700/70">คน</p>
                </div>
            </div>
            <p class="text-xs text-gray-500">รวมครูทั้งหมด {{ number_format(($completeTeacherCount ?? 0) + ($incompleteTeacherCount ?? 0)) }} คน</p>
        </div>
        <div class="flex-1 w-full flex justify-center">
            <div class="relative w-full max-w-md aspect-square">
                <canvas id="adminTeacherStatusChart" class="w-full h-full"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-sm text-gray-500">ครูทั้งหมด</p>
                    <p id="adminTeacherStatusTotal" class="text-4xl font-bold text-gray-800">{{ number_format(($completeTeacherCount ?? 0) + ($incompleteTeacherCount ?? 0)) }}</p>
                </div>
                <div id="adminTeacherStatusChartEmpty" class="absolute inset-0 hidden items-center justify-center text-sm text-gray-500 bg-white/80 rounded-full flex">
                    ยังไม่มีข้อมูลกราฟ
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS รายชื่อครู --}}
<div id="teacherModal-overlay" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-40 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 relative">
        <button type="button" onclick="toggleTeacherModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
        <h3 id="teacherModalTitle" class="text-xl font-semibold text-gray-900 mb-2">คุณครูที่ทำหลักสูตรเสร็จ</h3>
        <p id="teacherModalSubtitle" class="text-sm text-gray-500 mb-4"></p>
        <div id="teacherModalBody" class="max-h-80 overflow-y-auto">
            {{-- ใส่ผ่าน JS --}}
        </div>
    </div>
</div>
<div id="roomStudentsModal" class="fixed inset-0 z-40 hidden items-start justify-center bg-black/30 backdrop-blur-sm px-4 py-10">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900" data-i18n-th="นักเรียนรายห้อง" data-i18n-en="Students by classroom">นักเรียนรายห้อง</h3>
                <p class="text-sm text-gray-500" data-i18n-th="เลือกห้องเพื่อดูรายชื่อ" data-i18n-en="Select classroom to view list">เลือกห้องเพื่อดูรายชื่อ</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-room-modal>&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label for="roomSelect" class="block text-sm font-semibold text-gray-700 mb-2">Select classroom</label>
                <select id="roomSelect"
                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        onchange="renderRoomStudents(this.value)">
                    <option value="">Select classroom</option>
                    @foreach(($roomOptions ?? collect()) as $room)
                        <option value="{{ $room }}">
                            {{ $room }} ({{ number_format(collect($studentsByRoomPayload[$room] ?? [])->count()) }} students)
                        </option>
                    @endforeach
                </select>
            </div>
            <p id="roomDropdownSummary" class="text-sm text-gray-600"></p>
            <div id="roomDropdownBody" class="max-h-[50vh] overflow-y-auto space-y-2"></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200" data-close-room-modal>ปิด</button>
        </div>
    </div>
</div>
<div id="summaryPeopleModal" class="fixed inset-0 z-40 hidden items-start justify-center bg-black/30 backdrop-blur-sm px-4 py-10">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 id="summaryPeopleModalTitle" class="text-lg font-semibold text-gray-900">รายชื่อ</h3>
                <p id="summaryPeopleModalSubtitle" class="text-sm text-gray-500">ทั้งหมด 0 คน</p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-summary-modal>&times;</button>
        </div>
        <div class="p-6">
            <div id="summaryPeopleModalBody" class="max-h-[55vh] overflow-y-auto space-y-2"></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200" data-close-summary-modal>ปิด</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const teacherData = {
        complete: @json(($completeTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all()),
        incomplete: @json(($incompleteTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all()),
    };
    const teacherCountSummary = {
        complete: Number(@json($completeTeacherCount ?? 0)),
        incomplete: Number(@json($incompleteTeacherCount ?? 0)),
    };
    const summaryPeopleData = {
        teachers: @json($teacherListPayload ?? []),
        students: @json($studentListPayload ?? []),
    };
    const roomStudentsData = @json($studentsByRoomPayload ?? []);

    function closeRoomDropdown() {
        const modal = document.getElementById('roomStudentsModal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
    }

    function closeSummaryPeopleModal() {
        const modal = document.getElementById('summaryPeopleModal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toggleSummaryPeopleModal(type = null) {
        const modal = document.getElementById('summaryPeopleModal');
        if (!modal) return;

        if (!type) {
            closeSummaryPeopleModal();
            return;
        }

        const titleEl = document.getElementById('summaryPeopleModalTitle');
        const subtitleEl = document.getElementById('summaryPeopleModalSubtitle');
        const bodyEl = document.getElementById('summaryPeopleModalBody');
        if (!titleEl || !subtitleEl || !bodyEl) return;

        const isTeacher = type === 'teachers';
        const list = summaryPeopleData[type] || [];

        titleEl.textContent = isTeacher ? 'รายชื่อครูทั้งหมด' : 'รายชื่อนักเรียนทั้งหมด';
        subtitleEl.textContent = `ทั้งหมด ${list.length} คน`;
        bodyEl.innerHTML = '';

        if (!list.length) {
            bodyEl.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">ยังไม่มีข้อมูล</p>';
        } else {
            list.forEach((item, index) => {
                const wrapper = document.createElement('div');
                const cardClass = isTeacher
                    ? 'bg-green-50 border-green-100'
                    : 'bg-blue-50 border-blue-100';
                const metaLabel = isTeacher
                    ? (item.email ? escapeHtml(item.email) : '-')
                    : `รหัส ${escapeHtml(item.student_code || '-')}`;

                wrapper.className = `px-4 py-3 border rounded-xl ${cardClass}`;
                wrapper.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-gray-900">${escapeHtml(item.name || '-')}</p>
                        <span class="text-xs text-gray-500">${metaLabel}</span>
                    </div>
                `;

                bodyEl.appendChild(wrapper);

                if (index < list.length - 1) {
                    const separator = document.createElement('div');
                    separator.className = 'h-0.5 bg-gray-200 rounded-full my-2';
                    bodyEl.appendChild(separator);
                }
            });
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function toggleRoomDropdown(event) {
        event?.stopPropagation();
        const modal = document.getElementById('roomStudentsModal');
        const roomSelect = document.getElementById('roomSelect');
        if (!modal) return;

        const shouldOpen = modal.classList.contains('hidden');
        if (!shouldOpen) {
            closeRoomDropdown();
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (roomSelect && roomSelect.value) {
            renderRoomStudents(roomSelect.value);
        } else {
            const summaryEl = document.getElementById('roomDropdownSummary');
            const bodyEl = document.getElementById('roomDropdownBody');
            if (summaryEl) summaryEl.textContent = '';
            if (bodyEl) bodyEl.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">กรุณาเลือกห้องเรียน</p>';
        }
        roomSelect?.focus();
    }

    function renderRoomStudents(room) {
        const summaryEl = document.getElementById('roomDropdownSummary');
        const bodyEl = document.getElementById('roomDropdownBody');
        if (!summaryEl || !bodyEl) return;

        const list = room ? (roomStudentsData[room] || []) : [];

        if (!room) {
            summaryEl.textContent = '';
            bodyEl.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">กรุณาเลือกห้องเรียน</p>';
            return;
        }

        summaryEl.textContent = `ห้อง ${room} - ${list.length} คน`;
        bodyEl.innerHTML = '';

        if (!list.length) {
            bodyEl.innerHTML = '<p class="py-3 text-sm text-gray-500 text-center">ไม่พบนักเรียนในห้องนี้</p>';
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
            bodyEl.appendChild(item);

            if (index < list.length - 1) {
                const separator = document.createElement('div');
                separator.className = 'h-0.5 bg-purple-200 rounded-full my-2';
                bodyEl.appendChild(separator);
            }
        });
    }

    function toggleTeacherModal(type = null) {
        const overlay = document.getElementById('teacherModal-overlay');
        if (!overlay) return;

        if (!type) {
            overlay.classList.add('hidden');
            return;
        }

        const titleEl = document.getElementById('teacherModalTitle');
        const subtitleEl = document.getElementById('teacherModalSubtitle');
        const bodyEl = document.getElementById('teacherModalBody');
        const isComplete = type === 'complete';
        const isIncomplete = type === 'incomplete';
        if (titleEl) {
            titleEl.textContent = isComplete
                ? 'คุณครูที่ทำหลักสูตรเสร็จ'
                : isIncomplete
                    ? 'คุณครูที่ทำหลักสูตรยังไม่เสร็จ'
                    : 'คุณครูที่ยังไม่ได้สร้างหลักสูตร';
        }

        const list = teacherData[type] || [];
        subtitleEl.textContent = `ทั้งหมด ${list.length} คน`;

        bodyEl.innerHTML = '';
        if (list.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'py-3 text-sm text-gray-500 text-center';
            empty.textContent = 'ยังไม่มีข้อมูล';
            bodyEl.appendChild(empty);
        } else {
            list.forEach((item, idx) => {
                const div = document.createElement('div');
                const cardClass = isComplete
                    ? 'bg-sky-50 border-sky-100'
                    : 'bg-amber-50 border-amber-100';
                div.className = `py-3 px-4 border rounded-2xl shadow-sm ${cardClass}`;

                const name = document.createElement('p');
                name.className = 'font-semibold text-gray-900';
                name.textContent = item.name || '-';
                div.appendChild(name);

                const divider = document.createElement('div');
                divider.className = 'w-full border-t-2 border-red-500 my-2';
                div.appendChild(divider);

                if (item.email) {
                    const email = document.createElement('p');
                    email.className = 'text-sm text-gray-500';
                    email.textContent = item.email;
                    div.appendChild(email);
                }

                bodyEl.appendChild(div);

                if (idx < list.length - 1) {
                    const separator = document.createElement('div');
                    separator.className = 'h-0.5 bg-red-500 rounded-full my-4';
                    bodyEl.appendChild(separator);
                }
            });
        }

        overlay.classList.remove('hidden');
    }

    // Doughnut chart: ครูชั่วโมงสอนครบ / ไม่ครบ
    document.addEventListener('DOMContentLoaded', () => {
        const chartEl = document.getElementById('adminTeacherStatusChart');
        const chartEmpty = document.getElementById('adminTeacherStatusChartEmpty');
        const chartTotal = document.getElementById('adminTeacherStatusTotal');
        const roomModal = document.getElementById('roomStudentsModal');
        const summaryPeopleModal = document.getElementById('summaryPeopleModal');

        document.querySelectorAll('[data-close-room-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                closeRoomDropdown();
            });
        });

        roomModal?.addEventListener('click', (event) => {
            if (event.target === roomModal) {
                closeRoomDropdown();
            }
        });

        document.querySelectorAll('[data-close-summary-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                closeSummaryPeopleModal();
            });
        });

        summaryPeopleModal?.addEventListener('click', (event) => {
            if (event.target === summaryPeopleModal) {
                closeSummaryPeopleModal();
            }
        });

        if (!chartEl || !window.Chart) return;

        const complete = teacherCountSummary.complete || 0;
        const incomplete = teacherCountSummary.incomplete || 0;
        const total = complete + incomplete;

        if (chartTotal) {
            chartTotal.textContent = total.toLocaleString();
        }

        if (total === 0) {
            chartEmpty?.classList.remove('hidden');
            chartEl.classList.add('opacity-30');
            return;
        }

        chartEmpty?.classList.add('hidden');

        const ctx = chartEl.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ครูที่ทำหลักสูตรเสร็จ', 'ครูที่ทำหลักสูตรยังไม่เสร็จ'],
                datasets: [{
                    data: [complete, incomplete],
                    backgroundColor: ['#0284c7', '#f59e0b'],
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
                        labels: { usePointStyle: true },
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
    });
</script>
@endpush

@endsection
