@extends('layouts.layout')

@section('title', 'สร้างหลักสูตรการสอน | ระบบครู')

@section('content')
@php
    $usedGrades = ($courses ?? collect())->pluck('grade')->filter()->unique();
    $currentYearAd = now(config('app.timezone', 'Asia/Bangkok'))->year;
    $currentYearBe = $currentYearAd + 543;
@endphp
<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    {{-- Header --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <p class="text-sm text-slate-500 uppercase tracking-widest mb-1"
           data-i18n-th="สำหรับครู"
           data-i18n-en="For teachers">
            สำหรับครู
        </p>
        <h1 class="text-3xl font-bold text-gray-900"
            data-i18n-th="สร้างหลักสูตรการสอน"
            data-i18n-en="Create teaching course">
            สร้างหลักสูตรการสอน
        </h1>
        <p class="text-gray-600"
           data-i18n-th="ยินดีต้อนรับ {{ Auth::user()->name }}"
           data-i18n-en="Welcome {{ Auth::user()->name }}">
            ยินดีต้อนรับ {{ Auth::user()->name }}
        </p>
    </div>

    {{-- Create Course Form --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-6"
            data-i18n-th="เพิ่มหลักสูตรใหม่"
            data-i18n-en="Add new course">
            เพิ่มหลักสูตรใหม่
        </h3>

        @if ($errors->any())
            <div class="mb-6 border border-red-200 bg-red-50 text-red-700 rounded-2xl p-4">
                <p class="font-semibold mb-2"
                   data-i18n-th="กรุณาตรวจสอบข้อมูลที่กรอก"
                   data-i18n-en="Please review the submitted information">
                    กรุณาตรวจสอบข้อมูลที่กรอก
                </p>
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
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="ชื่อหลักสูตร"
                       data-i18n-en="Course name">
                    ชื่อหลักสูตร
                </label>
                @if(!empty($teacherMajor))
                    <input type="hidden" name="name" value="{{ $teacherMajor }}">
                    <div class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                        {{ $teacherMajor }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1"
                       data-i18n-th="วิชาเอกของคุณ: <span class=&quot;font-semibold text-blue-700&quot;>{{ $teacherMajor }}</span>"
                       data-i18n-en="Your major: <span class=&quot;font-semibold text-blue-700&quot;>{{ $teacherMajor }}</span>">
                        วิชาเอกของคุณ:
                    </p>
                @else
                    <div class="space-y-2">
                        <select id="nameSelect"
                                name="name"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <option value=""
                                    data-i18n-th="-- เลือกหลักสูตร --"
                                    data-i18n-en="-- Select course --">-- เลือกหลักสูตร --</option>
                            @foreach(($adminCourseOptions ?? collect()) as $adminCourse)
                                <option value="{{ $adminCourse->name }}"
                                        data-grade="{{ $adminCourse->grade }}"
                                        data-term="{{ $adminCourse->term }}"
                                        data-year="{{ $adminCourse->year }}"
                                        @selected(old('name') === $adminCourse->name)>
                                    {{ $adminCourse->name }}
                                </option>
                            @endforeach
                            <option value="__custom__" @selected(old('name') === '__custom__')
                                    data-i18n-th="+ เพิ่มวิชาใหม่..."
                                    data-i18n-en="+ Add new subject...">+ เพิ่มวิชาใหม่...</option>
                        </select>

                        <input type="text" id="customCourseInput"
                               name="name_custom"
                               value="{{ old('name_custom') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none @if(old('name') !== '__custom__') hidden @endif"
                               placeholder="พิมพ์ชื่อหลักสูตร/วิชาใหม่"
                               data-i18n-placeholder-th="พิมพ์ชื่อหลักสูตร/วิชาใหม่"
                               data-i18n-placeholder-en="Type new course/subject name">
                        <p class="text-xs text-gray-500"
                           data-i18n-th="ถ้าไม่พบวิชาที่ต้องการ เลือก “+ เพิ่มวิชาใหม่...” แล้วพิมพ์ชื่อ"
                           data-i18n-en="If not listed, choose “+ Add new subject...” then type the name">
                            ถ้าไม่พบวิชาที่ต้องการ เลือก “+ เพิ่มวิชาใหม่...” แล้วพิมพ์ชื่อ
                        </p>
                    </div>
                @endif
            </div>

            {{-- ระดับชั้น --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="ชั้นเรียน"
                       data-i18n-en="Grade">
                    ชั้นเรียน
                </label>
                <select id="gradeSelect" name="grade"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                    <option value=""
                            data-i18n-th="-- เลือกชั้นเรียน --"
                            data-i18n-en="-- Select grade --">-- เลือกชั้นเรียน --</option>
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
                    <p class="text-xs text-orange-600 mt-1"
                       data-i18n-th="ชั้นเรียนที่สร้างแล้วจะถูกปิดไว้ (สร้างได้ชั้นละ 1 หลักสูตร)"
                       data-i18n-en="Existing grades are locked (one course per grade)">
                        ชั้นเรียนที่สร้างแล้วจะถูกปิดไว้ (สร้างได้ชั้นละ 1 หลักสูตร)
                    </p>
                @endif
            </div>

            {{-- ห้องเรียน --}}
            <div data-room-section>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <span data-i18n-th="เลือกห้องเรียน (เลือกได้หลายห้อง)"
                          data-i18n-en="Select classrooms (multiple allowed)">
                        เลือกห้องเรียน (เลือกได้หลายห้อง)
                    </span>
                </label>
                <div id="roomCheckboxes" class="space-y-2 text-sm text-gray-700">
                    <p class="text-gray-400"
                       data-i18n-th="-- เลือกระดับชั้นเรียนก่อน --"
                       data-i18n-en="-- Select grade first --">
                        -- เลือกระดับชั้นเรียนก่อน --
                    </p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                           data-i18n-th="ปีการศึกษา"
                           data-i18n-en="Academic year">
                        ปีการศึกษา
                    </label>
                    <input type="number" name="year"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400"
                           placeholder="2567"
                           value="{{ old('year') }}">
                </div>
            </div>

            {{-- รายละเอียดหลักสูตร --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="รายละเอียดหลักสูตร"
                       data-i18n-en="Course description">
                    รายละเอียดหลักสูตร
                </label>
                <textarea name="description"
                          class="w-full border rounded-lg px-3 py-2 h-28 focus:ring-2 focus:ring-blue-400"
                          placeholder="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้"
                          data-i18n-placeholder-th="ใส่คำอธิบายรายวิชา / จุดประสงค์ / เนื้อหาการเรียนรู้"
                          data-i18n-placeholder-en="Add course description / objectives / content">{{ old('description') }}</textarea>
            </div>

            {{-- เพดานคะแนนเก็บ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="เพดานคะแนนเก็บ (ไม่เกิน 100)"
                       data-i18n-en="Assignment score cap (max 100)">
                    เพดานคะแนนเก็บ (ไม่เกิน 100)
                </label>
                <input type="number"
                       name="assignment_cap"
                       min="1" max="100" step="0.5"
                       value="{{ old('assignment_cap', 70) }}"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
                <p class="text-xs text-gray-500 mt-1">
                    <span data-i18n-th="ใช้เป็นคะแนนเก็บรวมสูงสุดของงานในแต่ละภาคเรียน" data-i18n-en="Maximum total assignment score per term">ใช้เป็นคะแนนเก็บรวมสูงสุดของงานในแต่ละภาคเรียน</span>
                </p>
            </div>

            <div class="text-right">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                    <span data-i18n-th="สร้างหลักสูตร" data-i18n-en="Create course">สร้างหลักสูตร</span>
                </button>
            </div>
        </form>
    </div>

    {{-- รายการหลักสูตรที่สร้างแล้ว --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"
            data-i18n-th="หลักสูตรที่สร้างไว้แล้ว"
            data-i18n-en="Existing courses">
            หลักสูตรที่สร้างไว้แล้ว
        </h3>

        @if($courses->isEmpty())
            <div class="border border-dashed border-gray-200 rounded-2xl p-6 text-center text-gray-500"
                 data-i18n-th="ยังไม่มีหลักสูตรที่สร้างไว้"
                 data-i18n-en="No courses created yet">
                ยังไม่มีหลักสูตรที่สร้างไว้
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-xl text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left"
                                data-i18n-th="ชื่อหลักสูตร" data-i18n-en="Course">ชื่อหลักสูตร</th>
                            <th class="py-3 px-4 text-center"
                                data-i18n-th="ชั้นเรียน" data-i18n-en="Grade">ชั้นเรียน</th>
                            <th class="py-3 px-4 text-center"
                                data-i18n-th="ห้อง" data-i18n-en="Rooms">ห้อง</th>
                            <th class="py-3 px-4 text-center"
                                data-i18n-th="ภาคเรียน" data-i18n-en="Term">ภาคเรียน</th>
                            <th class="py-3 px-4 text-center"
                                data-i18n-th="ปีการศึกษา" data-i18n-en="Year">ปีการศึกษา</th>
                            <th class="py-3 px-4 text-center"
                                data-i18n-th="จัดการ" data-i18n-en="Actions">จัดการ</th>
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
                                        <button type="submit" class="text-red-600 hover:underline"
                                                data-i18n-th="ลบ" data-i18n-en="Delete">ลบ</button>
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

    // ปีการศึกษา: ไม่ให้ย้อนหลัง
    if (yearInput) {
        const currentBe = parseInt(yearInput.dataset.yearBe, 10);

        yearInput.addEventListener('change', () => {
            const raw = parseInt(yearInput.value, 10);
            if (Number.isNaN(raw)) return;

            if (raw < 2400) {
                alert('กรุณากรอกปีการศึกษาเป็น พ.ศ.');
                yearInput.value = currentBe;
                return;
            }

            if (raw < currentBe) {
                alert(`ปีการศึกษาต้องไม่ย้อนหลัง (ตั้งแต่ พ.ศ. ${currentBe} ขึ้นไป)`);
                yearInput.value = currentBe;
            }
        });
    }
});
</script>
@endpush
