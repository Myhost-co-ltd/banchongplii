@extends('layouts.layout-admin')

@section('title', 'แดชบอร์ดผู้ดูแลระบบ')

@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-2" data-i18n-th="แดชบอร์ดผู้ดูแลระบบ" data-i18n-en="Admin Dashboard">แดชบอร์ดผู้ดูแลระบบ</h1>
<p class="text-gray-600 mb-6" data-i18n-th="ยินดีต้อนรับ ผู้ดูแลระบบ" data-i18n-en="Welcome, Admin">ยินดีต้อนรับ ผู้ดูแลระบบ</p>

<div class="grid grid-cols-1 md:grid-cols-5 gap-6">

    <!-- จำนวนครู -->
    <div class="p-6 bg-green-100 border border-green-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">จำนวนครูทั้งหมด</h3>
        <p class="text-4xl font-bold text-green-700">{{ number_format($teacherCount ?? 0) }}</p>
    </div>

    <!-- จำนวนนักเรียน -->
    <div class="p-6 bg-blue-100 border border-blue-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนนักเรียนทั้งหมด" data-i18n-en="Total students">จำนวนนักเรียนทั้งหมด</h3>
        <p class="text-4xl font-bold text-blue-700">{{ number_format($studentCount ?? 0) }}</p>
    </div>

    <!--จำนวนห้อง -->
    <div class="p-6 bg-purple-100 border border-purple-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนห้องเรียน" data-i18n-en="Total classrooms">จำนวนห้องเรียน</h3>
        <p class="text-4xl font-bold text-purple-700">{{ number_format($classroomCount ?? 0) }}</p>
    </div>

    <!-- ครูที่มีชั่วโมงสอนครบ -->
    <div class="p-6 bg-sky-100 border border-sky-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="ครูที่มีชั่วโมงสอนครบ" data-i18n-en="Teachers complete">ครูที่มีชั่วโมงสอนครบ</h3>
        <p class="text-4xl font-bold text-sky-800">{{ number_format($completeTeacherCount ?? 0) }}</p>
        <button type="button"
                class="mt-3 text-sm text-sky-700 font-semibold hover:underline"
                onclick="toggleTeacherModal('complete')">
            <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
        </button>
    </div>

    <!-- ครูที่ชั่วโมงสอนไม่ครบ -->
    <div class="p-6 bg-amber-100 border border-amber-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="ครูที่ชั่วโมงสอนไม่ครบ" data-i18n-en="Teachers incomplete">ครูที่ชั่วโมงสอนไม่ครบ</h3>
        <p class="text-4xl font-bold text-amber-700">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
        <button type="button"
                class="mt-3 text-sm text-amber-700 font-semibold hover:underline"
                onclick="toggleTeacherModal('incomplete')">
            <span data-i18n-th="ดูรายชื่อครู" data-i18n-en="View teachers">ดูรายชื่อครู</span>
        </button>
    </div>

</div>

<!-- ภาพรวมชั่วโมงสอนของครู -->
<div class="mt-10 bg-white rounded-3xl shadow p-8 border border-gray-100">
    <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-12">
        <div class="flex-1 space-y-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">ภาพรวมชั่วโมงสอนของครู</h2>
                <p class="text-sm text-gray-500">สัดส่วนครูที่กรอกชั่วโมงสอนครบและไม่ครบ</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100">
                    <div class="flex items-center gap-2 text-sky-600 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        ชั่วโมงสอนครบ
                    </div>
                    <p class="text-3xl font-bold text-sky-700 mt-2">{{ number_format($completeTeacherCount ?? 0) }}</p>
                    <p class="text-sm text-sky-700/70">คน</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <div class="flex items-center gap-2 text-amber-600 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        ชั่วโมงสอนไม่ครบ
                    </div>
                    <p class="text-3xl font-bold text-amber-700 mt-2">{{ number_format($incompleteTeacherCount ?? 0) }}</p>
                    <p class="text-sm text-amber-700/70">คน</p>
                </div>
            </div>
            <p class="text-xs text-gray-500">รวมครูที่มีการสอนทั้งหมด {{ number_format(($completeTeacherCount ?? 0) + ($incompleteTeacherCount ?? 0)) }} คน</p>
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
        <h3 id="teacherModalTitle" class="text-xl font-semibold text-gray-900 mb-2">ครูที่ชั่วโมงสอนครบ</h3>
        <p id="teacherModalSubtitle" class="text-sm text-gray-500 mb-4"></p>
        <div id="teacherModalBody" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            {{-- ใส่ผ่าน JS --}}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const teacherData = {
        complete: @json(($completeTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all()),
        incomplete: @json(($incompleteTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all())
    };
    const teacherCountSummary = {
        complete: Number(@json($completeTeacherCount ?? 0)),
        incomplete: Number(@json($incompleteTeacherCount ?? 0)),
    };

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
        titleEl.textContent = isComplete ? 'ครูที่ชั่วโมงสอนครบ' : 'ครูที่ชั่วโมงสอนไม่ครบ';

        const list = teacherData[type] || [];
        subtitleEl.textContent = `ทั้งหมด ${list.length} คน`;

        bodyEl.innerHTML = '';
        if (list.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'py-3 text-sm text-gray-500 text-center';
            empty.textContent = 'ยังไม่มีข้อมูล';
            bodyEl.appendChild(empty);
        } else {
            list.forEach(item => {
                const div = document.createElement('div');
                div.className = 'py-3';

                const name = document.createElement('p');
                name.className = 'font-semibold text-gray-900';
                name.textContent = item.name || '-';

                div.appendChild(name);

                if (item.email) {
                    const email = document.createElement('p');
                    email.className = 'text-sm text-gray-500';
                    email.textContent = item.email;
                    div.appendChild(email);
                }

                bodyEl.appendChild(div);
            });
        }

        overlay.classList.remove('hidden');
    }

    // Doughnut chart: ครูชั่วโมงสอนครบ / ไม่ครบ
    document.addEventListener('DOMContentLoaded', () => {
        const chartEl = document.getElementById('adminTeacherStatusChart');
        const chartEmpty = document.getElementById('adminTeacherStatusChartEmpty');
        const chartTotal = document.getElementById('adminTeacherStatusTotal');

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
                labels: ['ชั่วโมงสอนครบ', 'ชั่วโมงสอนไม่ครบ'],
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
