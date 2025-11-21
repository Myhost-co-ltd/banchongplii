@extends('layouts.layout')

@section('title', 'สร้างหลักสูตรการสอน | ครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <div>
            <p class="text-sm text-slate-500 uppercase tracking-wide">สำหรับครู</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">สร้างหลักสูตรการสอน</h2>
            <p class="text-gray-600 mt-1">
                ยินดีต้อนรับ <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">เพิ่มหลักสูตรใหม่</h3>

        <form method="POST" action="{{ route('teacher.courses') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                       placeholder="เช่น คณิตศาสตร์พื้นฐาน ชั้น ป.1" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชั้นเรียน</label>
                <select id="gradeSelect" name="grade"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                        required>
                    <option value="">-- เลือกชั้นเรียน --</option>
                    @for ($g = 1; $g <= 6; $g++)
                        <option value="ป.{{ $g }}" @selected(old('grade') === 'ป.'.$g)>
                            ป.{{ $g }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    เลือกห้องเรียน (เลือกหลายห้องได้)
                </label>
                <div id="roomCheckboxes" class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-700">
                    <p class="text-gray-400 col-span-2 md:col-span-3">-- เลือกชั้นเรียนก่อน --</p>
                </div>
                <div id="selectedRooms" class="mt-4 space-y-2"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาคเรียน</label>
                    <select name="term" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        <option value="">-- เลือกภาคเรียน --</option>
                        <option value="1" @selected(old('term') == '1')>ภาคเรียนที่ 1</option>
                        <option value="2" @selected(old('term') == '2')>ภาคเรียนที่ 2</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                    <input type="text" name="year" value="{{ old('year') }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                           placeholder="เช่น 2567">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดหลักสูตร</label>
                <textarea name="description"
                          class="w-full border rounded-lg px-3 py-2 h-28 focus:ring-2 focus:ring-blue-400"
                          placeholder="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้">{{ old('description') }}</textarea>
            </div>

            <div class="text-right">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition">
                    สร้างหลักสูตร
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-10">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">หลักสูตรที่ถูกสร้างไว้</h3>

        @if ($courses->isEmpty())
            <p class="text-gray-500">ยังไม่มีหลักสูตรที่สร้างไว้</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-xl text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left font-medium">ชื่อหลักสูตร</th>
                            <th class="py-3 px-4 text-center font-medium">ห้องเรียน</th>
                            <th class="py-3 px-4 text-center font-medium">ภาคเรียน</th>
                            <th class="py-3 px-4 text-center font-medium">ปีการศึกษา</th>
                            <th class="py-3 px-4 text-center font-medium">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($courses as $course)
                            <tr class="hover:bg-blue-50">
                                <td class="py-2 px-4 font-medium">{{ $course->name }}</td>
                                <td class="py-2 px-4 text-center">
                                    @foreach ($course->rooms ?? [] as $room)
                                        <span class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-xs mr-1">
                                            {{ $room }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="py-2 px-4 text-center">{{ $course->term ?? '-' }}</td>
                                <td class="py-2 px-4 text-center">{{ $course->year ?? '-' }}</td>
                                <td class="py-2 px-4 text-center text-sm space-x-3">
                                    <a href="{{ route('course.detail', $course) }}"
                                       class="text-blue-600 font-semibold hover:underline">
                                        ดูรายละเอียด
                                    </a>
                                    <a href="{{ route('teacher.courses.edit', $course) }}"
                                       class="text-amber-600 font-semibold hover:underline">
                                        แก้ไข
                                    </a>
                                    <form method="POST"
                                          action="{{ route('teacher.courses.destroy', $course) }}"
                                          class="inline"
                                          onsubmit="return confirm('ยืนยันการลบหลักสูตร {{ $course->name }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 font-semibold hover:underline">
                                            ลบ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    let selectedRooms = [];

    const gradeSelect = document.getElementById('gradeSelect');
    const oldGrade = @json(old('grade'));
    const oldRooms = @json(old('rooms', []));

    function updateRoomOptions(preselected = []) {
        const grade = gradeSelect.value;
        const container = document.getElementById('roomCheckboxes');

        selectedRooms = [];
        container.innerHTML = '';

        if (!grade) {
            container.innerHTML = `<p class="text-gray-400 col-span-2 md:col-span-3">-- เลือกชั้นเรียนก่อน --</p>`;
            renderSelectedRooms();
            return;
        }

        for (let i = 1; i <= 10; i++) {
            const roomName = `${grade}/${i}`;
            const checked = preselected.includes(roomName) ? 'checked' : '';
            if (checked) {
                selectedRooms.push(roomName);
            }

            container.innerHTML += `
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="rooms[]" value="${roomName}"
                           onchange="toggleRoom(this)"
                           class="w-4 h-4 text-blue-600" ${checked}>
                    ${roomName}
                </label>
            `;
        }

        renderSelectedRooms();
    }

    function toggleRoom(checkbox) {
        const room = checkbox.value;
        if (checkbox.checked) {
            if (!selectedRooms.includes(room)) selectedRooms.push(room);
        } else {
            selectedRooms = selectedRooms.filter(r => r !== room);
        }
        renderSelectedRooms();
    }

    function renderSelectedRooms() {
        const container = document.getElementById('selectedRooms');
        if (!selectedRooms.length) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = selectedRooms.map(room => `
            <div class="flex items-center justify-between bg-blue-100 text-blue-800 px-3 py-2 rounded-lg text-sm">
                <span>${room}</span>
                <button type="button" onclick="removeRoom('${room}')" class="text-red-600 hover:text-red-800">
                    ลบ
                </button>
            </div>
        `).join('');
    }

    function removeRoom(room) {
        selectedRooms = selectedRooms.filter(r => r !== room);
        document.querySelectorAll("#roomCheckboxes input").forEach(cb => {
            if (cb.value === room) cb.checked = false;
        });
        renderSelectedRooms();
    }

    gradeSelect.addEventListener('change', () => updateRoomOptions());

    if (oldGrade) {
        gradeSelect.value = oldGrade;
        updateRoomOptions(oldRooms);
    }
</script>
@endsection


