@extends('layouts.layout')

@section('title', 'สร้างหลักสูตรการสอน | ระบบครู')

@section('content')
@php
    $usedGrades = ($courses ?? collect())->pluck('grade')->filter()->unique();
@endphp
<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    {{-- Header --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <p class="text-sm text-slate-500 uppercase tracking-widest mb-1">สำหรับครู</p>
        <h1 class="text-3xl font-bold text-gray-900">สร้างหลักสูตรการสอน</h1>
        <p class="text-gray-600">ยินดีต้อนรับ {{ Auth::user()->name }}</p>
    </div>

    {{-- Create Course Form --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">เพิ่มหลักสูตรใหม่</h3>

        @if ($errors->any())
            <div class="mb-6 border border-red-200 bg-red-50 text-red-700 rounded-2xl p-4">
                <p class="font-semibold mb-2">กรุณาตรวจสอบข้อมูลที่กรอก</p>
                <ul class="text-sm space-y-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.courses.store') }}" class="space-y-6">
            @csrf

            {{-- ชื่อหลักสูตร --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                @if(!empty($teacherMajor))
                    <input type="hidden" name="name" value="{{ $teacherMajor }}">
                    <div class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                        {{ $teacherMajor }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">วิชาเอกของคุณ: <span class="font-semibold text-blue-700">{{ $teacherMajor }}</span></p>
                @else
                    <div class="space-y-2">
                        <select id="nameSelect"
                                name="name"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <option value="">-- เลือกหลักสูตร --</option>
                            @foreach(($adminCourseOptions ?? collect()) as $adminCourse)
                                <option value="{{ $adminCourse->name }}"
                                        data-grade="{{ $adminCourse->grade }}"
                                        data-term="{{ $adminCourse->term }}"
                                        data-year="{{ $adminCourse->year }}"
                                        @selected(old('name') === $adminCourse->name)>
                                    {{ $adminCourse->name }}
                                </option>
                            @endforeach
                            <option value="__custom__" @selected(old('name') === '__custom__')>+ เพิ่มวิชาใหม่...</option>
                        </select>

                        <input type="text" id="customCourseInput"
                               name="name_custom"
                               value="{{ old('name_custom') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none @if(old('name') !== '__custom__') hidden @endif"
                               placeholder="พิมพ์ชื่อหลักสูตร/วิชาใหม่">
                        <p class="text-xs text-gray-500">ถ้าไม่พบวิชาที่ต้องการ เลือก “+ เพิ่มวิชาใหม่...” แล้วพิมพ์ชื่อ</p>
                    </div>
                @endif
            </div>

            {{-- ระดับชั้น --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชั้นเรียน</label>
                <select id="gradeSelect" name="grade"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                    <option value="">-- เลือกชั้นเรียน --</option>
                    @for ($grade = 1; $grade <= 6; $grade++)
                        @php $val = 'ป.'.$grade; $isUsed = $usedGrades->contains($val); @endphp
                        <option value="{{ $val }}"
                                @selected(old('grade') === $val)
                                @if($isUsed && old('grade') !== $val) disabled @endif>
                            ป.{{ $grade }}
                        </option>
                    @endfor
                </select>
                @if($usedGrades->isNotEmpty())
                    <p class="text-xs text-orange-600 mt-1">ชั้นเรียนที่สร้างแล้วจะถูกปิดไว้ (สร้างได้ชั้นละ 1 หลักสูตร)</p>
                @endif
            </div>

            {{-- ห้องเรียน --}}
            <div data-room-section>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    เลือกห้องเรียน (เลือกได้หลายห้อง)
                </label>
                <div id="roomCheckboxes" class="space-y-2 text-sm text-gray-700">
                    <p class="text-gray-400">-- เลือกระดับชั้นเรียนก่อน --</p>
                </div>
            </div>

            {{-- ภาคเรียน + ปีการศึกษา --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาคเรียน</label>
                    <select name="term" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        <option value="">-- เลือกภาคเรียน --</option>
                        <option value="1" @selected(old('term') == 1)>ภาคเรียนที่ 1</option>
                        <option value="2" @selected(old('term') == 2)>ภาคเรียนที่ 2</option>
                    </select>
                </div> -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                    <input type="number" name="year"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                           placeholder="2568"
                           min="2568" step="1"
                           value="{{ max((int) (old('year') ?? 2568), 2568) }}">
                </div>
            </div>

            {{-- รายละเอียดหลักสูตร --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดหลักสูตร</label>
                <textarea name="description"
                          class="w-full border rounded-lg px-3 py-2 h-28 focus:ring-2 focus:ring-blue-400"
                          placeholder="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้">{{ old('description') }}</textarea>
            </div>

            <div class="text-right">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                    สร้างหลักสูตร
                </button>
            </div>
        </form>
    </div>

    {{-- รายการหลักสูตรที่สร้างแล้ว --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">หลักสูตรที่สร้างไว้แล้ว</h3>

        @if($courses->isEmpty())
            <div class="border border-dashed border-gray-200 rounded-2xl p-6 text-center text-gray-500">
                ยังไม่มีหลักสูตรที่สร้างไว้
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-xl text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">ชื่อหลักสูตร</th>
                            <th class="py-3 px-4 text-center">ชั้นเรียน</th>
                            <th class="py-3 px-4 text-center">ห้อง</th>
                            <th class="py-3 px-4 text-center">ภาคเรียน</th>
                            <th class="py-3 px-4 text-center">ปีการศึกษา</th>
                            <th class="py-3 px-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($courses as $course)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-medium text-gray-900">
                                    <a href="{{ route('course.detail', $course) }}"
                                       class="text-blue-600 hover:underline">
                                        {{ $course->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-center">{{ $course->grade }}</td>
                                <td class="py-3 px-4 text-center">
                                    @forelse ($course->rooms ?? [] as $room)
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs mr-1">
                                            {{ $room }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endforelse
                                </td>
                                <td class="py-3 px-4 text-center">{{ $course->term ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">{{ $course->year ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('teacher.courses.destroy', $course) }}"
                                          method="POST"
                                          onsubmit="return confirm('ยืนยันการลบหลักสูตรนี้หรือไม่?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">ลบ</button>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const MIN_YEAR = 2568;
    const nameSelect     = document.getElementById('nameSelect');
    const gradeSelect    = document.getElementById('gradeSelect');
    const roomContainer  = document.getElementById('roomCheckboxes');
    const roomSection    = document.querySelector('[data-room-section]');
    const yearInput      = document.querySelector('input[name="year"]');

    const oldGrade = @json(old('grade'));
    const oldRooms = @json(old('rooms', []));

    if (yearInput) {
        yearInput.min = MIN_YEAR;
        if (yearInput.value) {
            yearInput.value = Math.max(Number(yearInput.value) || MIN_YEAR, MIN_YEAR);
        }
    }

    function renderRoomOptions(grade, selectedRooms = []) {
        roomContainer.innerHTML = '';

        if (!grade) {
            roomContainer.innerHTML = '<p class="text-gray-400">-- เลือกระดับชั้นเรียนก่อน --</p>';
            if (roomSection) roomSection.classList.add('opacity-60');
            return;
        }

        if (roomSection) roomSection.classList.remove('opacity-60');

        for (let i = 1; i <= 10; i++) {
            const value = `${grade}/${i}`;
            const isChecked = selectedRooms.includes(value);

            roomContainer.insertAdjacentHTML('beforeend', `
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="rooms[]" value="${value}"
                           class="w-4 h-4 text-blue-600"
                           ${isChecked ? 'checked' : ''}>
                    ${value}
                </label>
            `);
        }
    }

    // โหลดครั้งแรก: ถ้ามีค่าเก่าจากการ validate ไม่ผ่าน ให้ set คืน
    if (oldGrade) {
        gradeSelect.value = oldGrade;
        renderRoomOptions(oldGrade, oldRooms);
    } else {
        renderRoomOptions('', []);
    }

    // เลือกชื่อหลักสูตรจาก admin แล้วเติมข้อมูลที่เกี่ยวข้อง
    if (nameSelect) {
        nameSelect.addEventListener('change', (event) => {
            const option = event.target.selectedOptions[0];
            if (!option) return;

            const grade = option.getAttribute('data-grade') || '';
            const term  = option.getAttribute('data-term') || '';
            const year  = option.getAttribute('data-year') || '';

            if (grade) {
                gradeSelect.value = grade;
                renderRoomOptions(grade, []);
            }
            if (year && yearInput) {
                const clampedYear = Math.max(Number(year) || MIN_YEAR, MIN_YEAR);
                yearInput.value = clampedYear;
            }
            // term ช่องถูกคอมเมนต์ไว้ ถ้าเปิดใช้งานอีกครั้งให้เติมค่าที่นี่

            // toggle custom course input
            const isCustom = option.value === '__custom__';
            const customInput = document.getElementById('customCourseInput');
            if (customInput) {
                customInput.classList.toggle('hidden', !isCustom);
                customInput.name = isCustom ? 'name' : 'name_custom';
                if (isCustom) customInput.focus();
            }
        });

        // initial custom toggle
        const selected = nameSelect.selectedOptions[0];
        const customInput = document.getElementById('customCourseInput');
        if (selected?.value === '__custom__' && customInput) {
            customInput.classList.remove('hidden');
            customInput.name = 'name';
        }
    }

    // เวลาเปลี่ยนชั้นเรียน → สร้างห้องใหม่ให้เลือก
    gradeSelect.addEventListener('change', (event) => {
        const grade = event.target.value;
        renderRoomOptions(grade, []);   // reset การเลือกห้องเมื่อเปลี่ยนชั้น
    });
});
</script>
@endpush
