@extends('layouts.layout')

@section('title', 'แก้ไขหลักสูตร | แดชบอร์ดครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-6">

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <div>
            <p class="text-sm text-slate-500 uppercase tracking-wide">จัดการหลักสูตร</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">แก้ไขหลักสูตร</h2>
            <p class="text-gray-600 mt-1">
                ปรับปรุงข้อมูลของหลักสูตร <span class="font-semibold text-blue-700">{{ $course->name }}</span>
                ให้ตรงกับการสอนล่าสุด
            </p>
        </div>
    </div>

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
        <h3 class="text-xl font-semibold text-gray-800 mb-6">ข้อมูลหลักสูตร</h3>

        <form method="POST" action="{{ route('teacher.courses.update', $course) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                <input type="text" name="name" value="{{ old('name', $course->name) }}"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                       placeholder="เช่น คณิตศาสตร์พื้นฐาน ป.1" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชั้นเรียน</label>
                <select id="gradeSelect" name="grade"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                        required>
                    <option value="">-- เลือกชั้นเรียน --</option>
                    @for ($g = 1; $g <= 6; $g++)
                        @php($label = 'ป.'.$g)
                        <option value="{{ $label }}" @selected(old('grade', $course->grade) === $label)>
                            {{ $label }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    ห้องเรียนที่สอน (เลือกได้มากกว่า 1 ห้อง)
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
                        <option value="1" @selected(old('term', $course->term) == '1')>ภาคเรียนที่ 1</option>
                        <option value="2" @selected(old('term', $course->term) == '2')>ภาคเรียนที่ 2</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                    <input type="text" name="year" value="{{ old('year', $course->year) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                           placeholder="เช่น 2567">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดหลักสูตร</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                          placeholder="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้">{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="flex flex-col gap-3 md:flex-row md:justify-end">
                <a href="{{ route('course.detail', $course) }}"
                   class="inline-flex items-center justify-center px-5 py-2 rounded-2xl border border-gray-300 text-gray-600 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2 rounded-2xl bg-green-600 text-white font-semibold hover:bg-green-700">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedRooms = [];

    const gradeSelect = document.getElementById('gradeSelect');
    const oldGrade = @json(old('grade', $course->grade));
    const oldRooms = @json(old('rooms', $course->rooms ?? []));

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
