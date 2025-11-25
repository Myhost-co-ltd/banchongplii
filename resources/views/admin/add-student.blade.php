@extends('layouts.layout-admin')

@section('title', 'จัดการข้อมูลนักเรียน')

@section('content')

<h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">
    จัดการข้อมูลนักเรียน
</h1>

{{-- SUCCESS --}}
@if (session('status'))
    <div class="mb-4 rounded-xl bg-green-50 text-green-700 border border-green-200 px-4 py-3 text-sm shadow-sm">
        {{ session('status') }}
    </div>
@endif

{{-- ERROR --}}
@if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- TOP ACTION BAR --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">

    <div class="flex gap-3">
        <button onclick="openAddStudentModal()"
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-5 rounded-xl shadow-md font-medium">
            + เพิ่มนักเรียน
        </button>

        <form id="importForm" action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white py-2 px-5 rounded-xl shadow-md cursor-pointer font-medium">
                Import CSV
                <input type="file" name="file" accept=".csv,text/csv" class="hidden" onchange="document.getElementById('importForm').submit()">
            </label>
        </form>
    </div>

    <div class="flex flex-col gap-2">
        <div class="bg-white border border-gray-300 rounded-xl px-4 py-2 shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1016.65 2a7.5 7.5 0 000 14.65z" />
            </svg>
            <input type="text" id="searchInput"
                   onkeyup="searchStudent()"
                   placeholder="ค้นหาชื่อ / รหัสนักเรียน..."
                   class="w-72 border-none focus:ring-0 placeholder-gray-400 text-gray-700">
        </div>

        <p class="text-xs text-gray-500">
            * ไฟล์นำเข้าควรเป็น CSV: student_code, first_name, last_name, gender, room
        </p>
    </div>

</div>

{{-- FILTER --}}
<div class="mb-6 flex items-center gap-3">
    <label class="font-semibold text-gray-700">เลือกห้องเรียน:</label>
    <select id="roomFilter" onchange="filterRoom()"
            class="border border-gray-300 rounded-xl px-3 py-2 shadow-sm w-48">
        <option value="all">ทั้งหมด</option>
        @foreach(($rooms ?? []) as $room)
            <option value="{{ $room }}">{{ $room }}</option>
        @endforeach
    </select>
</div>

{{-- TABLE CARD --}}
<div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">

    <div class="max-h-[55vh] overflow-y-auto relative rounded-xl">
        <table class="w-full border-collapse">

            {{-- HEADER --}}
            <thead class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-sm">
            <tr class="text-sm uppercase tracking-wide">
                <th class="p-3 text-left">#</th>
                <th class="p-3 text-left">รหัส</th>
                <th class="p-3 text-left">ชื่อ</th>
                <th class="p-3 text-left">นามสกุล</th>
                <th class="p-3 text-left">เพศ</th>
                <th class="p-3 text-left">ห้อง</th>
                <th class="p-3 text-center">จัดการ</th>
            </tr>
            </thead>

            {{-- BODY --}}
            <tbody id="studentTable" class="text-gray-700">

            @forelse (($students ?? []) as $index => $student)
                @php($fullName = trim(($student->title ? $student->title . ' ' : '') . $student->first_name . ' ' . $student->last_name))

                <tr class="border-b hover:bg-gray-50 transition student-row"
                    data-room="{{ $student->room ?? '' }}"
                    data-name="{{ mb_strtolower($fullName) }}"
                    data-code="{{ $student->student_code }}">

                    <td class="p-3">{{ $index + 1 }}</td>
                    <td class="p-3 font-semibold text-blue-700">{{ $student->student_code }}</td>
                    <td class="p-3">{{ $student->first_name }}</td>
                    <td class="p-3">{{ $student->last_name }}</td>
                    <td class="p-3">{{ $student->gender ?? 'ไม่ระบุ' }}</td>
                    <td class="p-3 text-blue-600 font-semibold">{{ $student->room ?? '-' }}</td>

                    <td class="p-3 text-center text-gray-400">
                        <button type="button"
                                class="text-yellow-600 font-semibold hover:underline"
                                onclick="openEditStudentModal(this)"
                                data-id="{{ $student->id }}"
                                data-code="{{ $student->student_code }}"
                                data-title="{{ $student->title }}"
                                data-first="{{ $student->first_name }}"
                                data-last="{{ $student->last_name }}"
                                data-gender="{{ $student->gender }}"
                                data-room="{{ $student->room }}">
                            แก้ไข
                        </button>
                        <span class="mx-1 text-gray-300">|</span>
                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline"
                              onsubmit="return confirm('ต้องการลบนักเรียนคนนี้หรือไม่?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-semibold hover:underline">ลบ</button>
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="p-4 text-center text-gray-400">ยังไม่มีข้อมูลนักเรียน</td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>

</div>

{{-- MODALS --}}
<div id="addStudentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeAddStudentModal()"
                class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-900 mb-4">เพิ่มนักเรียน</h2>

        <form id="addStudentForm" action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="font-semibold text-gray-800">รหัสนักเรียน</label>
                <input type="text" name="student_code" class="input w-full" placeholder="เช่น 11001" required>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-semibold text-gray-800">คำนำหน้า</label>
                    <input type="text" name="title" class="input w-full" placeholder="ด.ช. / ด.ญ. / นาย / นางสาว">
                </div>
                <div class="col-span-2">
                    <label class="font-semibold text-gray-800">ชื่อ</label>
                    <input type="text" name="first_name" class="input w-full" required>
                </div>
            </div>
            <div>
                <label class="font-semibold text-gray-800">นามสกุล</label>
                <input type="text" name="last_name" class="input w-full" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold text-gray-800">เพศ</label>
                    <select name="gender" class="input w-full">
                        <option value="">ไม่ระบุ</option>
                        <option value="ชาย">ชาย</option>
                        <option value="หญิง">หญิง</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-gray-800">ห้อง</label>
                    <input type="text" name="room" class="input w-full" placeholder="เช่น ป1/1">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeAddStudentModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-black py-2 rounded-xl">
                    ยกเลิก
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editStudentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeEditStudentModal()"
                class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-900 mb-4">แก้ไขข้อมูลนักเรียน</h2>

        <form id="editStudentForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="font-semibold text-gray-800">รหัสนักเรียน</label>
                <input type="text" name="student_code" class="input w-full" required>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-semibold text-gray-800">คำนำหน้า</label>
                    <input type="text" name="title" class="input w-full" placeholder="ด.ช. / ด.ญ. / นาย / นางสาว">
                </div>
                <div class="col-span-2">
                    <label class="font-semibold text-gray-800">ชื่อ</label>
                    <input type="text" name="first_name" class="input w-full" required>
                </div>
            </div>
            <div>
                <label class="font-semibold text-gray-800">นามสกุล</label>
                <input type="text" name="last_name" class="input w-full" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold text-gray-800">เพศ</label>
                    <select name="gender" class="input w-full">
                        <option value="">ไม่ระบุ</option>
                        <option value="ชาย">ชาย</option>
                        <option value="หญิง">หญิง</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-gray-800">ห้อง</label>
                    <input type="text" name="room" class="input w-full" placeholder="เช่น ป1/1">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeEditStudentModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-black py-2 rounded-xl">
                    ยกเลิก
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function searchStudent() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll(".student-row").forEach(row => {
        const name = row.dataset.name || '';
        const code = row.dataset.code || '';
        row.style.display = (name.includes(input) || code.includes(input)) ? "" : "none";
    });
}

function filterRoom() {
    const selected = document.getElementById("roomFilter").value;
    document.querySelectorAll(".student-row").forEach(row => {
        const roomData = (row.dataset.room || '').split(' ')[0];
        row.style.display = (selected === "all" || roomData === selected) ? "" : "none";
    });
}

function openAddStudentModal() {
    document.getElementById("addStudentModal").classList.remove("hidden");
}

function closeAddStudentModal() {
    document.getElementById("addStudentModal").classList.add("hidden");
}

const editForm = document.getElementById('editStudentForm');
const editModal = document.getElementById('editStudentModal');
const updateUrlTemplate = "{{ url('/admin/students/__ID__') }}";

function openEditStudentModal(button) {
    const ds = button.dataset;
    editForm.action = updateUrlTemplate.replace('__ID__', ds.id);
    editForm.student_code.value = ds.code || '';
    editForm.title.value = ds.title || '';
    editForm.first_name.value = ds.first || '';
    editForm.last_name.value = ds.last || '';
    editForm.gender.value = ds.gender || '';
    editForm.room.value = ds.room || '';
    editModal.classList.remove('hidden');
}

function closeEditStudentModal() {
    editModal.classList.add('hidden');
}
</script>
@endpush

@endsection
