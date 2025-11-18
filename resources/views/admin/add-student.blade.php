@extends('layouts.layout-admin')

@section('title', 'จัดการข้อมูลนักเรียน')

@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-6">จัดการข้อมูลนักเรียน</h1>

<div class="flex justify-between items-center mb-6">

    <div class="flex gap-3">
        <button onclick="openRoomSelector()"
            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-5 rounded-xl shadow">
            เพิ่มนักเรียน
        </button>

        <button onclick="openImportModal()"
            class="bg-green-600 hover:bg-green-700 text-white py-2 px-5 rounded-xl shadow">
            Import Excel
        </button>
    </div>

    <div class="bg-white border-2 border-blue-600 rounded-xl p-3 shadow-sm">
        <input type="text" id="searchInput"
            onkeyup="searchStudent()"
            placeholder="ค้นหาชื่อ หรือ รหัสนักเรียน..."
            class="input w-72 border-0 focus:ring-0">
    </div>
</div>

<div class="mb-6">
    <label class="font-semibold text-black">เลือกห้องเรียน:</label>
    <select id="roomFilter" onchange="filterRoom()" class="input w-48 ml-3">
        <option value="all">ทั้งหมด</option>
        <option value="ป1/1">ป.1/1</option>
        <option value="ป1/2">ป.1/2</option>
        <option value="ป1/3">ป.1/3</option>
    </select>
</div>

<div class="bg-white p-6 rounded-2xl shadow-md overflow-x-auto">
    
    <div class="max-h-[50vh] overflow-y-auto relative">
        <table class="w-full border-collapse">
            <thead>
            <tr class="bg-blue-600 text-white sticky top-0">
                <th class="p-3">#</th>
                <th class="p-3">รหัส</th>
                <th class="p-3">ชื่อ</th>
                <th class="p-3">นามสกุล</th>
                <th class="p-3">เพศ</th>
                <th class="p-3">ห้อง</th>
                <th class="p-3 text-center">จัดการ</th>
            </tr>
            </thead>

            <tbody id="studentTable">

            @php
                $rooms = ['ป1/1', 'ป1/2', 'ป1/3'];
                $firstNames = ['กิตติ','อนันต์','ศิริชัย','นภัสกร','สุรเดช','ธีรภัทร','ชญาน์ทิพย์','กมลชนก','ธนพร'];
                $lastNames = ['บุญมี','ใจดี','แก้วดี','ทองดี','เพ็งดี','พรมมา','แก้วดวงดี','หมื่นไทย'];
                $genders = ['ชาย','หญิง'];
            @endphp

            @for ($i=1;$i<=60;$i++)
                @php
                    $room = $rooms[array_rand($rooms)];
                    $fname = $firstNames[array_rand($firstNames)];
                    $lname = $lastNames[array_rand($lastNames)];
                    $gender = $genders[array_rand($genders)];
                    $code = 11000 + $i;
                @endphp

                <tr class="border-b student-row"
                    data-room="{{ $room }}"
                    data-name="{{ strtolower($fname.' '.$lname) }}"
                    data-code="{{ $code }}">
                    <td class="p-3 text-center">{{ $i }}</td>
                    <td class="p-3 text-center font-semibold">{{ $code }}</td>
                    <td class="p-3">{{ $fname }}</td>
                    <td class="p-3">{{ $lname }}</td>
                    <td class="p-3 text-center">{{ $gender }}</td>
                    <td class="p-3 text-center text-blue-600 font-semibold">{{ $room }}</td>

                    <td class="p-3 text-center">
                        <button onclick="alert('แก้ไข (Mock)')" class="text-yellow-600 font-semibold">แก้ไข</button> |
                        <button onclick="alert('ลบ (Mock)')" class="text-red-600 font-semibold">ลบ</button>
                    </td>
                </tr>
            @endfor

            </tbody>
        </table>
    </div>
</div>

@endsection


{{-- ===================================================================== --}}
{{-- POPUP #1 เลือกห้อง --}}
{{-- ===================================================================== --}}
<div id="roomSelectorModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">

        <button onclick="closeRoomSelector()"
            class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-black mb-4">เลือกห้องเรียน</h2>

        <label class="font-semibold text-black">ชั้นเรียน</label>
        <select id="gradeSelect" onchange="updateRooms()" class="input w-full mb-4">
            <option value="">-- เลือกชั้น --</option>
            @for ($g = 1; $g <= 6; $g++)
                <option value="ป{{$g}}">ป.{{$g}}</option>
            @endfor
        </select>

        <label class="font-semibold text-black">ห้อง</label>
        <select id="roomSelect" class="input w-full mb-4" disabled>
            <option value="">-- เลือกห้อง --</option>
        </select>

        <label class="font-semibold text-black">ประเภทห้อง</label>
        <select id="roomType" class="input w-full mb-6" disabled>
            <option value="">-- เลือกประเภท --</option>
            <option value="normal">ห้องธรรมดา</option>
            <option value="special">ห้องพิเศษ</option>
        </select>

        <button onclick="goToAddStudent()"
            class="bg-black hover:bg-gray-900 text-white w-full py-2 rounded-xl">
            ต่อไป ➜
        </button>
    </div>
</div>


{{-- ===================================================================== --}}
{{-- POPUP #2 Import Excel --}}
{{-- ===================================================================== --}}
<div id="importModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
        <button onclick="closeImportModal()"
            class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-black mb-4">นำเข้ารายชื่อนักเรียน (Excel)</h2>
        
        <input type="file" class="input w-full mb-4">

        <button onclick="alert('Import Excel (Mock)')"
            class="bg-black hover:bg-gray-900 w-full text-white py-2 rounded-xl">
            อัปโหลดไฟล์
        </button>
    </div>
</div>


{{-- ===================================================================== --}}
{{-- POPUP #3 เพิ่มนักเรียน --}}
{{-- ===================================================================== --}}
<div id="addStudentModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">

        <button onclick="closeAddStudentModal()"
            class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>

        <h2 class="text-xl font-bold text-black mb-4">
            เพิ่มนักเรียน (ห้อง: <span id="selectedRoomText" class="text-black"></span>)
        </h2>

        <div class="mb-4">
            <label class="font-semibold">ชื่อนักเรียน</label>
            <input type="text" id="newFirstName" class="input w-full" placeholder="ชื่อจริง">
        </div>

        <div class="mb-4">
            <label class="font-semibold">นามสกุล</label>
            <input type="text" id="newLastName" class="input w-full" placeholder="นามสกุล">
        </div>

        {{-- ปุ่มย้อนกลับ --}}
        <button onclick="backToRoomSelector()"
            class="bg-gray-300 hover:bg-gray-400 text-black w-full py-2 rounded-xl mb-3">
            ← ย้อนกลับ
        </button>

        <button onclick="addStudent()"
            class="bg-black hover:bg-gray-900 text-white w-full py-2 rounded-xl">
             เพิ่มนักเรียน
        </button>
    </div>
</div>


{{-- ===================================================================== --}}
{{-- SCRIPT --}}
{{-- ===================================================================== --}}
<script>

function searchStudent() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll(".student-row").forEach(row => {
        let name = row.dataset.name;
        let code = row.dataset.code;
        row.style.display = (name.includes(input) || code.includes(input)) ? "" : "none";
    });
}

function filterRoom() {
    let selected = document.getElementById("roomFilter").value;
    document.querySelectorAll(".student-row").forEach(row => {
        let roomData = row.dataset.room.split(' ')[0]; 
        row.style.display = (selected === "all" || roomData === selected) ? "" : "none";
    });
}

function updateRooms() {
    let grade = document.getElementById("gradeSelect").value;
    let roomSelect = document.getElementById("roomSelect");
    let roomType = document.getElementById("roomType");

    roomSelect.innerHTML = `<option value="">-- เลือกห้อง --</option>`;
    roomSelect.disabled = true;
    roomType.disabled = true;

    if (!grade) return;

    for (let i = 1; i <= 3; i++) {
        roomSelect.innerHTML += `<option value="${grade}/${i}">${grade}/${i}</option>`;
    }

    roomSelect.disabled = false;
    roomSelect.onchange = () => {
        roomType.disabled = roomSelect.value === "";
    };
}

function openRoomSelector() {
    document.getElementById("roomSelectorModal").classList.remove("hidden");
}

function closeRoomSelector() {
    document.getElementById("roomSelectorModal").classList.add("hidden");
}

function goToAddStudent() {
    let grade = document.getElementById("gradeSelect").value;
    let room = document.getElementById("roomSelect").value;
    let type = document.getElementById("roomType").value;

    if (!grade || !room || !type) {
        alert("กรุณาเลือกชั้น ห้อง และประเภทห้องให้ครบ");
        return;
    }

    let typeText = type === "normal" ? "ธรรมดา" : "พิเศษ";
    let fullRoomName = room + " (" + typeText + ")";

    closeRoomSelector();

    document.getElementById("selectedRoomText").innerText = fullRoomName;
    document.getElementById("addStudentModal").classList.remove("hidden");
}

function closeAddStudentModal() {
    document.getElementById("addStudentModal").classList.add("hidden");
}

function backToRoomSelector() {
    document.getElementById("addStudentModal").classList.add("hidden");
    document.getElementById("roomSelectorModal").classList.remove("hidden");
}

function addStudent() {

    let fname = document.getElementById("newFirstName").value.trim();
    let lname = document.getElementById("newLastName").value.trim();
    let room = document.getElementById("selectedRoomText").innerText;

    if (!fname || !lname) {
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    let table = document.getElementById("studentTable");
    let newCode = Math.floor(10000 + Math.random() * 89999);
    let gender = Math.random() < 0.5 ? 'ชาย' : 'หญิง'; 

    let newRow = `
        <tr class="border-b student-row"
            data-room="${room}"
            data-name="${(fname + ' ' + lname).toLowerCase()}"
            data-code="${newCode}">
            <td class="p-3 text-center">ใหม่</td>
            <td class="p-3 text-center font-semibold">${newCode}</td>
            <td class="p-3">${fname}</td>
            <td class="p-3">${lname}</td>
            <td class="p-3 text-center">${gender}</td>
            <td class="p-3 text-center text-blue-600 font-semibold">${room}</td>

            <td class="p-3 text-center">
                <button onclick="alert('แก้ไข (Mock)')" class="text-yellow-600 font-semibold">แก้ไข</button> |
                <button onclick="this.parentNode.parentNode.remove()" class="text-red-600 font-semibold">ลบ</button>
            </td>
        </tr>
    `;

    table.insertAdjacentHTML('afterbegin', newRow);

    closeAddStudentModal();
    document.getElementById("newFirstName").value = "";
    document.getElementById("newLastName").value = "";

    alert("เพิ่มนักเรียนเรียบร้อย (Mock)");
}

function openImportModal() {
    document.getElementById("importModal").classList.remove("hidden");
}

function closeImportModal() {
    document.getElementById("importModal").classList.add("hidden");
}

</script>


