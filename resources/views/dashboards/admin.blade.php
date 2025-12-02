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
<script>
    const teacherData = {
        complete: @json(($completeTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all()),
        incomplete: @json(($incompleteTeachers ?? collect())->values()->map(fn($t) => ['name' => $t->name, 'email' => $t->email])->all())
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
</script>
@endpush

@endsection
