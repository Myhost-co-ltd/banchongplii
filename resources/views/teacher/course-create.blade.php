@extends('layouts.layout')

@section('title', 'สร้างหลักสูตร | ครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <!-- Header -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <div>
            <p class="text-sm text-slate-500 uppercase tracking-wide">สำหรับครู</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">สร้างหลักสูตรการสอน</h2>
            <p class="text-gray-600 mt-1">
                ยินดีต้อนรับ <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
            </p>
        </div>
    </div>

    <!-- Create Course Form -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <h3 class="text-xl font-semibold text-gray-800 mb-6">เพิ่มหลักสูตรใหม่</h3>

        <form class="space-y-6">

            <!-- ชื่อหลักสูตร -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                <input type="text"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                       placeholder="เช่น คณิตศาสตร์พื้นฐาน ชั้น ป.1">
            </div>

            <!-- ชั้นเรียน -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">ชั้นเรียน</label>

    <select id="gradeSelect"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
        <option value="">-- เลือกชั้นเรียน --</option>
        @for ($g = 1; $g <= 6; $g++)
            <option value="ป.{{ $g }}">ป.{{ $g }}</option>
        @endfor
    </select>
</div>

<!-- เพิ่มห้องไม่จำกัด -->
<div class="mt-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        เพิ่มห้องเรียน (ไม่จำกัดจำนวน)
    </label>

    <div class="flex gap-3">

        <!-- ช่องกรอกเลขห้อง -->
        <input id="roomNumberInput"
               type="number"
               class="w-32 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
               placeholder="เช่น 1">

        <!-- ปุ่มเพิ่ม -->
        <button type="button"
                onclick="addRoom()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
            เพิ่มห้อง
        </button>
    </div>

    <p class="text-xs text-gray-500 mt-1">
        ระบบจะสร้างเป็น "ป.X/ห้อง" อัตโนมัติ เช่น ป.2/5
    </p>

    <!-- แสดงรายการห้องที่เลือก -->
    <div id="selectedRooms" class="mt-3 space-y-2"></div>
</div>


            <!-- ห้องเรียนหลายห้อง -->
            {{-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 mt-3">
                    ห้องเรียนที่สอนได้ (เลือกได้หลายห้อง)
                </label>

                <div class="flex gap-3">
                    <select id="roomSelect"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                            disabled>
                        <option value="">-- เลือกชั้นเรียนก่อน --</option>
                    </select>

                    <button type="button"
                            onclick="addRoom()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                        เพิ่ม
                    </button>
                </div>

                
                <div id="selectedRooms" class="mt-3 space-y-2"></div>
            </div> --}}

            <!-- ภาคเรียน + ปี -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาคเรียน</label>
                    <select class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        <option value="1">ภาคเรียนที่ 1</option>
                        <option value="2">ภาคเรียนที่ 2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                    <input type="number"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                           placeholder="เช่น 2567">
                </div>

            </div>

            <!-- รายละเอียดหลักสูตร -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดหลักสูตร</label>
                <textarea class="w-full border rounded-lg px-3 py-2 h-28 focus:ring-2 focus:ring-blue-400"
                          placeholder="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl shadow">
                    สร้างหลักสูตร
                </button>
            </div>
        </form>

    </div>

    <!-- Course List -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <h3 class="text-xl font-semibold text-gray-800 mb-4">หลักสูตรที่ถูกสร้างไว้</h3>

        @php
            $mockCourses = [
                ['name' => 'คณิตศาสตร์พื้นฐาน ป.1', 'rooms' => ['ป.1/1','ป.1/2'], 'term' => '1', 'year' => '2567'],
                ['name' => 'ภาษาไทยเพื่อการสื่อสาร ป.1', 'rooms' => ['ป.1/3'], 'term' => '1', 'year' => '2567'],
            ];
        @endphp

        <table class="min-w-full border border-gray-200 rounded-xl text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">ชื่อหลักสูตร</th>
                    <th class="py-3 px-4 text-center">ห้องเรียน</th>
                    <th class="py-3 px-4 text-center">ภาคเรียน</th>
                    <th class="py-3 px-4 text-center">ปีการศึกษา</th>
                    <th class="py-3 px-4 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($mockCourses as $c)
                <tr class="hover:bg-blue-50 transition">
                    <td class="py-2 px-4">{{ $c['name'] }}</td>

                    <td class="py-2 px-4 text-center">
                        @foreach ($c['rooms'] as $r)
                            <span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs mr-1">
                                {{ $r }}
                            </span>
                        @endforeach
                    </td>

                    <td class="py-2 px-4 text-center">{{ $c['term'] }}</td>
                    <td class="py-2 px-4 text-center">{{ $c['year'] }}</td>

                    <td class="py-2 px-4 text-center">
                        <button class="text-yellow-600 hover:underline">แก้ไข</button> |
                        <button class="text-red-600 hover:underline">ลบ</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection


<!-- ============================================== -->
<!--                  SCRIPT DROPDOWN               -->
<!-- ============================================== -->
<script>
let selectedRoomList = [];

/* เพิ่มห้องเข้า list */
function addRoom() {
    let grade = document.getElementById("gradeSelect").value;
    let roomNumber = document.getElementById("roomNumberInput").value.trim();

    if (!grade) {
        alert("กรุณาเลือกชั้นเรียนก่อน");
        return;
    }

    if (!roomNumber || roomNumber <= 0) {
        alert("กรุณากรอกเลขห้องให้ถูกต้อง");
        return;
    }

    let roomName = `${grade}/${roomNumber}`;

    if (selectedRoomList.includes(roomName)) {
        alert("เพิ่มห้องนี้แล้ว");
        return;
    }

    selectedRoomList.push(roomName);
    document.getElementById("roomNumberInput").value = ""; // clear input
    renderSelectedRooms();
}

/* ลบห้อง */
function removeRoom(room) {
    selectedRoomList = selectedRoomList.filter(r => r !== room);
    renderSelectedRooms();
}

/* แสดงรายการห้องที่เลือก */
function renderSelectedRooms() {
    let container = document.getElementById("selectedRooms");
    container.innerHTML = "";

    selectedRoomList.forEach(room => {
        container.innerHTML += `
            <div class="flex items-center justify-between bg-blue-100 text-blue-800 px-3 py-2 rounded-lg">
                <span>${room}</span>
                <button onclick="removeRoom('${room}')"
                    class="text-red-600 hover:text-red-800">ลบ</button>
            </div>
        `;
    });
}
</script>
