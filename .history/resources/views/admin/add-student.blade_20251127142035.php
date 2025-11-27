@extends('layouts.layout-admin')

@section('title', 'จัดการนักเรียน')

@section('content')

@php
    // คำนำหน้า
    $titleOptions = ['ด.ช.', 'ด.ญ.', 'นาย', 'นางสาว', 'นาง'];

    /**
     * ปรับรูปแบบระดับชั้นให้เป็นรูปแบบเดียวกัน เช่น
     * ม1, ม.1,  ม. 1  → ม.1
     */
    $normalizeGrade = function (?string $grade): string {
        if (! $grade) {
            return '';
        }

        // ตัดช่องว่างออกทั้งหมด
        $clean = preg_replace('/\s+/u', '', $grade);
        if (! $clean) {
            return '';
        }

        // ถ้าไม่มีจุด เช่น ม1 → ม.1
        if (! str_contains($clean, '.')) {
            $clean = preg_replace('/^([^\d]+)(\d+)/u', '$1.$2', $clean);
        }

        return $clean;
    };

    // ระดับชั้น ม.1 - ม.6
    $baseGrades = collect(['ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6']);
    $roomOptions = collect($rooms ?? [])->filter()->values();

    // ใช้รายการระดับชั้นแบบ fix
    $gradeOptions = $baseGrades;

    // ห้องเรียนแยกตามระดับชั้น
    $roomsByGrade = [];

    foreach ($roomOptions as $room) {
        $gradePart = trim(preg_split('/\s*\/\s*/', $room, 2)[0] ?? '');
        $gradeKey = $normalizeGrade($gradePart);

        if ($gradeKey === '') {
            continue;
        }
        $roomsByGrade[$gradeKey][] = trim($room);
    }
@endphp

<h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight"
    data-i18n-th="จัดการนักเรียน"
    data-i18n-en="Manage Students">
    จัดการนักเรียน
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
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-5 rounded-xl shadow-md font-medium"
            data-i18n-th="+ เพิ่มนักเรียน" data-i18n-en="+ Add Student">
            + เพิ่มนักเรียน
        </button>

        <form id="importForm" action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white py-2 px-5 rounded-xl shadow-md cursor-pointer font-medium">
                <span data-i18n-th="นำเข้า CSV" data-i18n-en="Import CSV">Import CSV</span>
                <input type="file" name="file" accept=".csv,text/csv" class="hidden"
                       onchange="document.getElementById('importForm').submit()">
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
                   data-i18n-placeholder-th="ค้นหาชื่อ / รหัสนักเรียน..."
                   data-i18n-placeholder-en="Search name / student code..."
                   class="w-72 border-none focus:ring-0 placeholder-gray-400 text-gray-700">
        </div>

        <p class="text-xs text-gray-500"
           data-i18n-th="* ไฟล์ CSV ควรมี: student_code, first_name, last_name, gender (ไม่บังคับ), room"
           data-i18n-en="* CSV file should include: student_code, first_name, last_name, gender (optional), room">
            * ไฟล์ CSV ควรมี: student_code, first_name, last_name, gender (ไม่บังคับ), room
        </p>
    </div>

</div>

{{-- FILTER --}}
<div class="mb-6 flex flex-col md:flex-row md:items-center gap-3">
    <div class="flex items-center gap-2">
        <label class="font-semibold text-gray-700"
               data-i18n-th="เลือกระดับชั้น:" data-i18n-en="Select grade:">
            เลือกระดับชั้น:
        </label>
        <select id="gradeFilter"
                class="border border-gray-300 rounded-xl px-3 py-2 shadow-sm w-40">
            <option value="all" data-i18n-th="ทั้งหมด" data-i18n-en="All">ทั้งหมด</option>
            @foreach($gradeOptions as $grade)
                <option value="{{ $grade }}">{{ $grade }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-2">
        <label class="font-semibold text-gray-700"
               data-i18n-th="เลือกห้องเรียน:" data-i18n-en="Select classroom:">
            เลือกห้องเรียน:
        </label>
        <select id="roomFilter"
                class="border border-gray-300 rounded-xl px-3 py-2 shadow-sm w-48">
            <option value="all" data-i18n-th="ทั้งหมด" data-i18n-en="All">ทั้งหมด</option>
            @foreach(($rooms ?? []) as $room)
                <option value="{{ $room }}">{{ $room }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- TABLE CARD --}}
<div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">

    <div class="max-h-[55vh] overflow-y-auto relative rounded-xl">
        <table class="w-full border-collapse">

            {{-- HEADER --}}
            <thead class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-sm">
            <tr class="text-sm uppercase tracking-wide">
                <th class="p-3 text-left" data-i18n-th="#" data-i18n-en="#">#</th>
                <th class="p-3 text-left" data-i18n-th="รหัส" data-i18n-en="Code">รหัส</th>
                <th class="p-3 text-left" data-i18n-th="ชื่อ" data-i18n-en="First Name">ชื่อ</th>
                <th class="p-3 text-left" data-i18n-th="นามสกุล" data-i18n-en="Last Name">นามสกุล</th>
                <th class="p-3 text-left" data-i18n-th="เพศ" data-i18n-en="Gender">เพศ</th>
                <th class="p-3 text-left" data-i18n-th="ห้อง" data-i18n-en="Room">ห้อง</th>
                <th class="p-3 text-center" data-i18n-th="จัดการ" data-i18n-en="Actions">จัดการ</th>
            </tr>
            </thead>

            {{-- BODY --}}
            <tbody id="studentTable" class="text-gray-700">

            @forelse (($students ?? []) as $index => $student)
                @php($fullName = trim(($student->title ? $student->title . ' ' : '') . $student->first_name . ' ' . $student->last_name))

                <tr class="border-b hover:bg-gray-50 transition student-row"
                    data-room="{{ $student->room ?? '' }}"
                    data-grade="{{ $normalizeGrade(trim(preg_split('/\s*\/\s*/', $student->room ?? '', 2)[0] ?? '')) }}"
                    data-name="{{ mb_strtolower($fullName) }}"
                    data-code="{{ $student->student_code }}"
                    data-gender="{{ $student->gender ?? '' }}">

                    <td class="p-3">{{ $index + 1 }}</td>
                    <td class="p-3 font-semibold text-blue-700">{{ $student->student_code }}</td>
                    <td class="p-3">{{ $student->first_name }}</td>
                    <td class="p-3">{{ $student->last_name }}</td>
                    <td class="p-3">{{ $student->gender ?? '-' }}</td>
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
                        <form action="{{ route('admin.students.destroy', $student) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('ยืนยันการลบนักเรียนคนนี้หรือไม่?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 font-semibold hover:underline">
                                ลบ
                            </button>
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="p-4 text-center text-gray-400"
                        data-i18n-th="ยังไม่มีข้อมูลนักเรียน" data-i18n-en="No student data yet">
                        ยังไม่มีข้อมูลนักเรียน
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>

</div>

{{-- MODALS --}}
{{-- ADD STUDENT --}}
<div id="addStudentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeAddStudentModal()"
                class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-900 mb-4"
            data-i18n-th="เพิ่มนักเรียน" data-i18n-en="Add Student">
            เพิ่มนักเรียน
        </h2>

        <form id="addStudentForm"
              action="{{ route('admin.students.store') }}"
              method="POST"
              class="space-y-4">
            @csrf
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="รหัสนักเรียน" data-i18n-en="Student Code">
                    รหัสนักเรียน
                </label>
                <input type="text" name="student_code"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                       placeholder="เช่น 11001"
                       data-i18n-placeholder-th="เช่น 11001"
                       data-i18n-placeholder-en="e.g. 11001"
                       required>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="คำนำหน้า" data-i18n-en="Title">
                        คำนำหน้า
                    </label>
                    <select name="title"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value=""
                                data-i18n-th="เลือกคำนำหน้า"
                                data-i18n-en="Select title">
                            เลือกคำนำหน้า
                        </option>
                        @foreach($titleOptions as $title)
                            <option value="{{ $title }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ชื่อ" data-i18n-en="First Name">
                        ชื่อ
                    </label>
                    <input type="text" name="first_name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                           required>
                </div>
            </div>
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="นามสกุล" data-i18n-en="Last Name">
                    นามสกุล
                </label>
                <input type="text" name="last_name"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                       required>
            </div>
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="เพศ" data-i18n-en="Gender">
                    เพศ
                </label>
                <select name="gender"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    <option value=""
                            data-i18n-th="เลือกเพศ"
                            data-i18n-en="Select gender">
                        เลือกเพศ
                    </option>
                    <option value="ชาย" data-i18n-th="ชาย" data-i18n-en="Male">ชาย</option>
                    <option value="หญิง" data-i18n-th="หญิง" data-i18n-en="Female">หญิง</option>
                    <option value="ไม่ระบุ"
                            data-i18n-th="ไม่ระบุ"
                            data-i18n-en="Prefer not to say">
                        ไม่ระบุ
                    </option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ระดับชั้น" data-i18n-en="Grade">
                        ระดับชั้น
                    </label>
                    <select id="addGradeSelect"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value=""
                                data-i18n-th="เลือกระดับชั้น"
                                data-i18n-en="Select grade">
                            เลือกระดับชั้น
                        </option>
                        @foreach($gradeOptions as $grade)
                            <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ห้อง" data-i18n-en="Room">
                        ห้อง
                    </label>
                    <select name="room" id="addRoomSelect"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 disabled:bg-gray-100 disabled:text-gray-400"
                            disabled>
                        <option value=""
                                data-i18n-th="เลือกระดับชั้นก่อน"
                                data-i18n-en="Select grade first">
                            เลือกระดับชั้นก่อน
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeAddStudentModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-black py-2 rounded-xl"
                        data-i18n-th="ยกเลิก" data-i18n-en="Cancel">
                    ยกเลิก
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl"
                        data-i18n-th="บันทึก" data-i18n-en="Save">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT STUDENT --}}
<div id="editStudentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeEditStudentModal()"
                class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-gray-900 mb-4"
            data-i18n-th="แก้ไขนักเรียน" data-i18n-en="Edit Student">
            แก้ไขนักเรียน
        </h2>

        <form id="editStudentForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="รหัสนักเรียน" data-i18n-en="Student Code">
                    รหัสนักเรียน
                </label>
                <input type="text" name="student_code"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                       required>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="คำนำหน้า" data-i18n-en="Title">
                        คำนำหน้า
                    </label>
                    <select name="title"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value=""
                                data-i18n-th="เลือกคำนำหน้า"
                                data-i18n-en="Select title">
                            เลือกคำนำหน้า
                        </option>
                        @foreach($titleOptions as $title)
                            <option value="{{ $title }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ชื่อ" data-i18n-en="First Name">
                        ชื่อ
                    </label>
                    <input type="text" name="first_name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                           required>
                </div>
            </div>
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="นามสกุล" data-i18n-en="Last Name">
                    นามสกุล
                </label>
                <input type="text" name="last_name"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                       required>
            </div>
            <div>
                <label class="font-semibold text-gray-800"
                       data-i18n-th="เพศ" data-i18n-en="Gender">
                    เพศ
                </label>
                <select name="gender"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    <option value=""
                            data-i18n-th="เลือกเพศ"
                            data-i18n-en="Select gender">
                        เลือกเพศ
                    </option>
                    <option value="ชาย" data-i18n-th="ชาย" data-i18n-en="Male">ชาย</option>
                    <option value="หญิง" data-i18n-th="หญิง" data-i18n-en="Female">หญิง</option>
                    <option value="ไม่ระบุ"
                            data-i18n-th="ไม่ระบุ"
                            data-i18n-en="Prefer not to say">
                        ไม่ระบุ
                    </option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ระดับชั้น" data-i18n-en="Grade">
                        ระดับชั้น
                    </label>
                    <select id="editGradeSelect"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value=""
                                data-i18n-th="เลือกระดับชั้น"
                                data-i18n-en="Select grade">
                            เลือกระดับชั้น
                        </option>
                        @foreach($gradeOptions as $grade)
                            <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-gray-800"
                           data-i18n-th="ห้อง" data-i18n-en="Room">
                        ห้อง
                    </label>
                    <select name="room" id="editRoomSelect"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 disabled:bg-gray-100 disabled:text-gray-400"
                            disabled>
                        <option value=""
                                data-i18n-th="เลือกระดับชั้นก่อน"
                                data-i18n-en="Select grade first">
                            เลือกระดับชั้นก่อน
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeEditStudentModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-black py-2 rounded-xl"
                        data-i18n-th="ยกเลิก" data-i18n-en="Cancel">
                    ยกเลิก
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl"
                        data-i18n-th="บันทึกการเปลี่ยนแปลง"
                        data-i18n-en="Save changes">
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const roomsByGrade = @json($roomsByGrade);
const allRooms = @json($rooms ?? []);

// -------------------- SEARCH --------------------
function searchStudent() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll(".student-row").forEach(row => {
        const name = row.dataset.name || '';
        const code = row.dataset.code || '';
        row.style.display = (name.includes(input) || code.includes(input)) ? "" : "none";
    });
}

// -------------------- NORMALIZE GRADE (JS) --------------------
function normalizeGrade(grade) {
    if (!grade) return '';
    // ตัดช่องว่าง และปรับรูปแบบ เช่น ม1, ม.1 → ม.1
    let clean = grade.replace(/\s+/g, '');
    clean = clean.replace(/^([^.]+)\.?(\d+)$/, '$1.$2');
    return clean;
}

function getGradeFromRoom(room) {
    if (!room) return '';
    const parts = room.split('/');
    return normalizeGrade((parts[0] || '').trim());
}

// -------------------- FILTER ROOM --------------------
function filterRoom() {
    const gradeSelect = document.getElementById("gradeFilter");
    const roomSelect = document.getElementById("roomFilter");
    const selectedGrade = gradeSelect ? normalizeGrade(gradeSelect.value) : 'all';
    const selectedRoom = roomSelect ? roomSelect.value : 'all';

    document.querySelectorAll(".student-row").forEach(row => {
        const rowRoom = (row.dataset.room || '').trim();
        const rowGrade = normalizeGrade(row.dataset.grade || getGradeFromRoom(rowRoom));

        const gradeMatch = (selectedGrade === 'all') || (rowGrade === selectedGrade);
        const roomMatch  = (selectedRoom === 'all') || (rowRoom === selectedRoom);

        row.style.display = (gradeMatch && roomMatch) ? "" : "none";
    });
}

function updateRoomFilterOptions(grade) {
    const roomSelect = document.getElementById("roomFilter");
    if (!roomSelect) return;

    const normalizedGrade = normalizeGrade(grade);
    roomSelect.innerHTML = '';

    const allLabel = roomSelect.getAttribute('data-label-all') || 'ทั้งหมด';
    roomSelect.add(new Option(allLabel, 'all', true, normalizedGrade === 'all'));

    const rooms = normalizedGrade === 'all'
        ? allRooms
        : (roomsByGrade[normalizedGrade] || []);

    rooms.forEach(room => roomSelect.add(new Option(room, room)));
}

// -------------------- ROOM OPTIONS (MODAL) --------------------
function renderRoomOptions(selectEl, grade, selectedRoom = '') {
    if (!selectEl) return;
    grade = normalizeGrade(grade);
    selectEl.innerHTML = '';

    const hasGrade = !!grade;
    const knownRooms = hasGrade ? (roomsByGrade[grade] || []) : [];
    let roomList = hasGrade ? knownRooms : [];

    // fallback: ถ้าระดับชั้นนี้ยังไม่มี mapping ห้อง ให้แสดงทุกห้อง
    if (hasGrade && roomList.length === 0) {
        roomList = allRooms;
    }

    let placeholder = 'เลือกระดับชั้นก่อน';
    if (hasGrade) {
        placeholder = roomList.length ? 'เลือกห้อง' : 'ไม่มีห้องสำหรับระดับชั้นนี้';
    }

    selectEl.add(new Option(placeholder, '', true, !selectedRoom));

    roomList.forEach(room => {
        selectEl.add(new Option(room, room, false, room === selectedRoom));
    });

    if (selectedRoom && !roomList.includes(selectedRoom)) {
        selectEl.add(new Option(selectedRoom, selectedRoom, true, true));
    }

    selectEl.disabled = !hasGrade;
}

function setSelectValue(select, value) {
    if (!select) return;
    const exists = Array.from(select.options).some(opt => opt.value === value);
    if (value && !exists) {
        select.add(new Option(value, value, true, true));
    } else {
        select.value = value || '';
    }
}

// -------------------- MODAL: ADD --------------------
function openAddStudentModal() {
    document.getElementById("addStudentModal").classList.remove("hidden");
    if (addGradeSelect) {
        addGradeSelect.value = '';
    }
    if (addForm?.gender) {
        setSelectValue(addForm.gender, '');
    }
    renderRoomOptions(addRoomSelect, '', '');
}

function closeAddStudentModal() {
    document.getElementById("addStudentModal").classList.add("hidden");
}

// -------------------- MODAL: EDIT --------------------
const addForm         = document.getElementById('addStudentForm');
const editForm        = document.getElementById('editStudentForm');
const editModal       = document.getElementById('editStudentModal');
const updateUrlTemplate = "{{ url('/admin/students/__ID__') }}";

const addGradeSelect  = document.getElementById('addGradeSelect');
const addRoomSelect   = document.getElementById('addRoomSelect');
const editGradeSelect = document.getElementById('editGradeSelect');
const editRoomSelect  = document.getElementById('editRoomSelect');

function openEditStudentModal(button) {
    const ds = button.dataset;

    editForm.action = updateUrlTemplate.replace('__ID__', ds.id);
    editForm.student_code.value = ds.code || '';
    setSelectValue(editForm.title, ds.title || '');
    editForm.first_name.value = ds.first || '';
    editForm.last_name.value  = ds.last || '';
    setSelectValue(editForm.gender, ds.gender || '');

    const derivedGrade = getGradeFromRoom(ds.room || '');
    setSelectValue(editGradeSelect, derivedGrade);
    renderRoomOptions(editRoomSelect, derivedGrade, ds.room || '');

    editModal.classList.remove('hidden');
}

function closeEditStudentModal() {
    editModal.classList.add('hidden');
}

// -------------------- INIT --------------------
renderRoomOptions(addRoomSelect,
    addGradeSelect ? addGradeSelect.value : '',
    addRoomSelect ? addRoomSelect.value : ''
);
renderRoomOptions(editRoomSelect,
    editGradeSelect ? editGradeSelect.value : '',
    editRoomSelect ? editRoomSelect.value : ''
);

addGradeSelect?.addEventListener('change', (event) => {
    renderRoomOptions(addRoomSelect, event.target.value, '');
});

editGradeSelect?.addEventListener('change', (event) => {
    renderRoomOptions(editRoomSelect, event.target.value, '');
});

// Filter grade -> rebuild room filter + filter rows
const gradeFilter = document.getElementById('gradeFilter');
const roomFilter  = document.getElementById('roomFilter');

if (gradeFilter) {
    gradeFilter.addEventListener('change', (e) => {
        updateRoomFilterOptions(e.target.value || 'all');
        filterRoom();
    });
}

if (roomFilter) {
    roomFilter.addEventListener('change', filterRoom);
}

// initial sync
updateRoomFilterOptions(gradeFilter ? gradeFilter.value : 'all');
</script>
@endpush

@endsection
