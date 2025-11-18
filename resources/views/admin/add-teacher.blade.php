@extends('layouts.layout-admin')

@section('title', 'จัดการข้อมูลครู')

@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6">จัดการข้อมูลครู</h1>

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

        {{-- Mock data --}}
        @php
            $names = ['สมชาย','สายฝน','วรภัทร','กันยา','ศิริพร','อภิชัย','ชุติมา','วราภรณ์','ทศพล'];
            $last = ['ใจดี','ทองมาก','พัฒนะ','ศรีวรณ์','บัวแก้ว','พงศ์ไชย','จันทร์คำ'];
            $roles = ['teacher','assistant'];
        @endphp

        @for ($i=1;$i<=25;$i++)
            @php
                $fname = $names[array_rand($names)];
                $lname = $last[array_rand($last)];
                $role = $roles[array_rand($roles)];
                $email = strtolower($fname.$i).'@school.com';
                $phone = '08'.rand(10000000,99999999);

                // random classroom
                $classroom = 'ป.'.rand(1,6).'/'.rand(1,3);
            @endphp

            <tr class="border-b teacher-row"
                data-name="{{ strtolower($fname.' '.$lname) }}"
                data-role="{{ $role }}"
                data-email="{{ strtolower($email) }}"
                data-phone="{{ $phone }}"
                data-classroom="{{ strtolower($classroom) }}">

                <td class="p-3 text-center">{{ $i }}</td>
                <td class="p-3">{{ $fname.' '.$lname }}</td>
                <td class="p-3">{{ $email }}</td>
                <td class="p-3">{{ substr($phone,0,3) }}xxx{{ substr($phone,-4) }}</td>
                <td class="p-3 text-blue-600 font-semibold text-center">
                    {{ $role == 'teacher' ? 'ครู' : 'ครู' }}
                </td>

                <td class="p-3 text-center">{{ $classroom }}</td>

                <td class="p-3 text-center">
                    <button onclick="alert('แก้ไข (Mock)')" class="text-yellow-600 font-semibold">แก้ไข</button> |
                    <button onclick="alert('ลบ (Mock)')" class="text-red-600 font-semibold">ลบ</button>
                </td>
            </tr>

        @endfor

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

        <div class="mb-3">
            <label class="font-semibold">ชื่อ</label>
            <input type="text" id="tFirstName" class="input w-full border border-gray-300 shadow-sm" placeholder="ชื่อจริง">
        </div>

        <div class="mb-3">
            <label class="font-semibold">นามสกุล</label>
            <input type="text" id="tLastName" class="input w-full border border-gray-300 shadow-sm" placeholder="นามสกุล">
        </div>

        <div class="mb-3">
            <label class="font-semibold">อีเมล</label>
            <input type="email" id="tEmail" class="input w-full border border-gray-300 shadow-sm" placeholder="example@mail.com">
        </div>

        <div class="mb-3">
            <label class="font-semibold">เบอร์โทร</label>
            <input type="text" id="tPhone" class="input w-full border border-gray-300 shadow-sm" placeholder="0812345678">
        </div>

       <div class="mb-3">
            <label class="font-semibold">ห้องเรียนประจำชั้น</label>
            <select id="tClassroom" class="input w-full border border-gray-300 shadow-sm">
                <option value="">-- เลือกห้องเรียน --</option>
                @for ($c = 1; $c <= 6; $c++)
                    @for ($r = 1; $r <= 3; $r++)
                        <option value="ป.{{ $c }}/{{ $r }}">ป.{{ $c }}/{{ $r }}</option>
                    @endfor
                @endfor
            </select>
        </div>


        <div class="mb-3">
            <label class="font-semibold">บทบาท</label>
            <select id="tRole" class="input w-full border border-gray-300 shadow-sm">
                <option value="teacher">ครู</option>
            </select>
        </div>

        <button onclick="addTeacher()"
            class="bg-blue-600 hover:bg-blue-700 w-full text-white py-2 rounded-xl">
             เพิ่มข้อมูล
        </button>

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

function addTeacher() {

    let fname = document.getElementById("tFirstName").value.trim();
    let lname = document.getElementById("tLastName").value.trim();
    let email = document.getElementById("tEmail").value.trim();
    let phone = document.getElementById("tPhone").value.trim();
    let classroom = document.getElementById("tClassroom").value.trim();
    let role = document.getElementById("tRole").value;

    if (!fname || !lname || !email || !phone || !classroom) {
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    let table = document.getElementById("teacherTable");

    let roleText = role === "teacher" ? "ครู" : "ผู้ช่วยครู";
    // Mask phone number for display: 081xxx5678
    let maskedPhone = phone.substring(0, 3) + "xxx" + phone.substring(6);

    let row = `
        <tr class="border-b teacher-row"
            data-name="${(fname + ' ' + lname).toLowerCase()}"
            data-role="${role}"
            data-email="${email.toLowerCase()}"
            data-phone="${phone}"
            data-classroom="${classroom.toLowerCase()}">

            <td class="p-3 text-center">ใหม่</td>
            <td class="p-3">${fname} ${lname}</td>
            <td class="p-3">${email}</td>
            <td class="p-3">${maskedPhone}</td>
            <td class="p-3 text-center text-blue-600 font-semibold">${roleText}</td>
            <td class="p-3 text-center">${classroom}</td>

            <td class="p-3 text-center">
                <button onclick="alert('แก้ไข (mock)')" class="text-yellow-600 font-semibold">แก้ไข</button> |
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-600 font-semibold">ลบ</button>
            </td>
        </tr>
    `;

    table.insertAdjacentHTML("afterbegin", row);

    closeAddTeacher();
    alert("เพิ่มข้อมูลครูสำเร็จ (mock)");
}

</script>