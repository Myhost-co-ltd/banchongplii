@extends('layouts.layout-admin')

@section('title', 'จัดการข้อมูลครู')

@section('content')
@php
    $tz = config('app.timezone', 'Asia/Bangkok');
@endphp

<h1 class="text-3xl font-bold text-gray-800 mb-6">จัดการข้อมูลครู</h1>

@if (session('status'))
    <div class="mb-4 rounded-xl bg-green-50 text-green-700 border border-green-200 px-4 py-3 text-sm shadow">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm shadow">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex justify-between items-center mb-6">

    <div class="flex gap-3">
        <button onclick="openAddTeacher()"
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-5 rounded-xl shadow">
            เพิ่มครู
        </button>
    </div>

    <div class="bg-white border-2 border-blue-600 rounded-xl p-3">
        <input type="text" id="searchInput"
                onkeyup="searchTeacher()"
                placeholder="ค้นหาชื่อ / อีเมล / เบอร์โทร..."
                class="w-full border-0 outline-none">
    </div>
</div>

<div class="mb-6">
    <label class="font-semibold text-gray-700">เลือกบทบาท:</label>
    <select id="roleFilter" onchange="filterRole()" class="input w-48 ml-3">
        <option value="all">ทั้งหมด</option>
        <option value="teacher">ครู</option>
    </select>
</div>

<div class="bg-white p-6 rounded-2xl shadow-md border overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
        <tr class="bg-blue-600 text-white">
            <th class="p-3">#</th>
            <th class="p-3">ชื่อ</th>
            <th class="p-3">อีเมล</th>
            <th class="p-3">เบอร์โทร</th>
            <th class="p-3">บทบาท</th>
            <th class="p-3">ห้องเรียน</th>
            <th class="p-3 text-center">จัดการ</th>
        </tr>
        </thead>

        <tbody id="teacherTable">

        @forelse (($teachers ?? []) as $index => $teacher)
            @php
                $phone = $teacher->phone;
                $maskedPhone = $phone ? substr($phone,0,3) . 'xxx' . substr($phone,-4) : '-';
                [$firstName, $lastName] = array_pad(explode(' ', $teacher->name, 2), 2, '');
            @endphp
            <tr class="border-b teacher-row"
                data-name="{{ mb_strtolower($teacher->name ?? '') }}"
                data-role="{{ $teacher->role->name ?? 'teacher' }}"
                data-email="{{ strtolower($teacher->email ?? '') }}"
                data-phone="{{ $teacher->phone ?? '' }}"
                data-classroom="{{ mb_strtolower($teacher->homeroom ?? '') }}"
                data-homeroom="{{ $teacher->homeroom ?? '' }}"
                data-first="{{ $firstName }}"
                data-last="{{ $lastName }}"
                data-id="{{ $teacher->id }}">

                <td class="p-3 text-center">{{ $index + 1 }}</td>
                <td class="p-3">
                    <p>{{ $teacher->name }}</p>
                    @if($teacher->created_at)
                        <p class="text-xs text-gray-400 mt-1">
                            สร้างเมื่อ: {{ \Illuminate\Support\Carbon::parse($teacher->created_at)->timezone($tz)->locale('th')->isoFormat('D MMM YYYY HH:mm') }}
                        </p>
                    @endif
                </td>
                <td class="p-3">{{ $teacher->email }}</td>
                <td class="p-3">{{ $teacher->phone ? $maskedPhone : '-' }}</td>
                <td class="p-3 text-blue-600 font-semibold text-center">
                    {{ $teacher->role->name === 'teacher' ? 'ครู' : $teacher->role->name }}
                </td>
                <td class="p-3 text-center">{{ $teacher->homeroom ?? '-' }}</td>

                <td class="p-3 text-center">
                    <button type="button"
                            class="text-yellow-600 font-semibold hover:underline"
                            onclick="openEditTeacher(this)">
                        แก้ไข
                    </button>
                    <span class="mx-1 text-gray-300">|</span>
                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="inline"
                          onsubmit="return confirm('ต้องการลบครูคนนี้หรือไม่?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 font-semibold hover:underline">ลบ</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">ยังไม่มีข้อมูลครู</td>
            </tr>
        @endforelse

        </tbody>
    </table>
</div>

@endsection


{{-- ========================================= --}}
{{-- POPUP เพิ่มครู --}}
{{-- ========================================= --}}
<div id="addTeacherModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeAddTeacher()"
            class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-800 mb-4">เพิ่มข้อมูลครู</h2>

        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-3">
            @csrf
            <div class="mb-3">
                <label class="font-semibold">ชื่อ</label>
                <input type="text" name="first_name" class="input w-full border border-gray-300 shadow-sm" placeholder="ชื่อจริง" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">นามสกุล</label>
                <input type="text" name="last_name" class="input w-full border border-gray-300 shadow-sm" placeholder="นามสกุล" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">อีเมล</label>
                <input type="email" name="email" class="input w-full border border-gray-300 shadow-sm" placeholder="example@mail.com" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">เบอร์โทร</label>
                <input type="text" name="phone" class="input w-full border border-gray-300 shadow-sm" placeholder="0812345678">
            </div>

            <div class="mb-3">
                <label class="font-semibold">ห้องเรียนประจำชั้น</label>
                <select name="homeroom" class="input w-full border border-gray-300 shadow-sm">
                    <option value="">-- เลือกห้องเรียน --</option>
                    @for ($c = 1; $c <= 6; $c++)
                        @for ($r = 1; $r <= 3; $r++)
                            <option value="ป.{{ $c }}/{{ $r }}">ป.{{ $c }}/{{ $r }}</option>
                        @endfor
                    @endfor
                </select>
            </div>

            <p class="text-xs text-gray-500">รหัสผ่านเริ่มต้น: 12345678 (กรุณาให้ครูเปลี่ยนเองภายหลัง)</p>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 w-full text-white py-2 rounded-xl">
                 เพิ่มข้อมูล
            </button>
        </form>

    </div>
</div>

{{-- ========================================= --}}
{{-- POPUP แก้ไขครู --}}
{{-- ========================================= --}}
<div id="editTeacherModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeEditTeacher()"
            class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-800 mb-4">แก้ไขข้อมูลครู</h2>

        <form method="POST" id="editTeacherForm" class="space-y-3">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="font-semibold">ชื่อ</label>
                <input type="text" name="first_name" class="input w-full border border-gray-300 shadow-sm" placeholder="ชื่อจริง" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">นามสกุล</label>
                <input type="text" name="last_name" class="input w-full border border-gray-300 shadow-sm" placeholder="นามสกุล" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">อีเมล</label>
                <input type="email" name="email" class="input w-full border border-gray-300 shadow-sm" placeholder="example@mail.com" required>
            </div>

            <div class="mb-3">
                <label class="font-semibold">เบอร์โทร</label>
                <input type="text" name="phone" class="input w-full border border-gray-300 shadow-sm" placeholder="0812345678">
            </div>

            <div class="mb-3">
                <label class="font-semibold">ห้องเรียนประจำชั้น</label>
                <select name="homeroom" class="input w-full border border-gray-300 shadow-sm">
                    <option value="">-- เลือกห้องเรียน --</option>
                    @for ($c = 1; $c <= 6; $c++)
                        @for ($r = 1; $r <= 3; $r++)
                            <option value="ป.{{ $c }}/{{ $r }}">ป.{{ $c }}/{{ $r }}</option>
                        @endfor
                    @endfor
                </select>
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 w-full text-white py-2 rounded-xl">
                 บันทึกการแก้ไข
            </button>
        </form>

    </div>
</div>


{{-- ========================================= --}}
{{-- SCRIPT --}}
{{-- ========================================= --}}
<script>

function searchTeacher() {
    let value = document.getElementById("searchInput").value.toLowerCase();

    document.querySelectorAll(".teacher-row").forEach(row => {
        let name = row.dataset.name;
        let email = row.dataset.email;
        let phone = row.dataset.phone;
        let classroom = row.dataset.classroom;

        row.style.display =
            name.includes(value) ||
            email.includes(value) ||
            phone.includes(value) ||
            classroom.includes(value)
            ? "" : "none";
    });
}

function filterRole() {
    let selected = document.getElementById("roleFilter").value;

    document.querySelectorAll(".teacher-row").forEach(row => {
        let role = row.dataset.role;
        row.style.display = (selected === "all" || role === selected) ? "" : "none";
    });
}


/* === Popup Add Teacher === */
function openAddTeacher() {
    document.getElementById("addTeacherModal").classList.remove("hidden");
}
function closeAddTeacher() {
    document.getElementById("addTeacherModal").classList.add("hidden");
}

const editTeacherModal = document.getElementById("editTeacherModal");
const editTeacherForm = document.getElementById("editTeacherForm");
const updateTeacherUrlTemplate = "{{ url('/admin/teachers/__ID__') }}";

function openEditTeacher(button) {
    const ds = button.closest('tr').dataset;
    editTeacherForm.action = updateTeacherUrlTemplate.replace('__ID__', ds.id);
    editTeacherForm.first_name.value = ds.first || '';
    editTeacherForm.last_name.value = ds.last || '';
    editTeacherForm.email.value = ds.email || '';
    editTeacherForm.phone.value = ds.phone || '';
    editTeacherForm.homeroom.value = ds.homeroom || '';
    editTeacherModal.classList.remove('hidden');
}

function closeEditTeacher() {
    editTeacherModal.classList.add("hidden");
}

</script>
