@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร')

@section('content')
@php($courseOptions = collect($courses ?? []))
<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    {{-- HEADER --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">รายละเอียดหลักสูตร</p>
                <h1 class="text-3xl font-bold text-gray-900">จัดการข้อมูลหลักสูตรที่สอนอยู่</h1>
                <p class="text-gray-600">
                    เลือกหลักสูตรด้านล่างเพื่อดูข้อมูลพื้นฐาน ชั่วโมงสอน บทเรียน และชิ้นงาน
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('teacher.course-create') }}"
                       class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                        กลับไปหน้าสร้างหลักสูตร
                    </a>
                    @if($course)
                        <a href="{{ route('teacher.courses.edit', $course) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                            แก้ไขข้อมูลหลักสูตร
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-80">
                @if($courseOptions->isNotEmpty())
                    <label for="courseSelector" class="block text-sm font-semibold text-gray-700 mb-2">
                        เลือกหลักสูตร
                    </label>
                    <select id="courseSelector"
                            class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($courseOptions as $courseOption)
                            <option value="{{ route('course.detail', $courseOption) }}"
                                    @selected(optional($course)->id === $courseOption->id)>
                                {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="border border-dashed border-gray-200 rounded-2xl p-4 text-sm text-gray-500">
                        ยังไม่มีหลักสูตรที่สร้างไว้
                    </div>
                @endif
            </div>
        </div>
    </div>

    @unless($course)
        {{-- กรณียังไม่มีหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">ยังไม่มีหลักสูตรที่จะแสดง</h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                กรุณาสร้างหลักสูตรใหม่หรือเลือกจากรายการด้านบน
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                เพิ่มหลักสูตรใหม่
            </a>
        </div>
    @else

        {{-- ข้อมูลหลักสูตรพื้นฐาน --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $course->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse($course->rooms ?? [] as $room)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">{{ $room }}</span>
                        @empty
                            <span class="text-gray-400 text-sm">ยังไม่ได้กำหนดห้องเรียน</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ภาคเรียน</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $course->term ? 'ภาคเรียนที่ '.$course->term : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ปีการศึกษา</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $course->year ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                    <p class="text-gray-700 mt-1 leading-relaxed">
                        {{ $course->description ?? 'ยังไม่มีรายละเอียด' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ชั่วโมงที่สอน (ภาพรวม) --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">ชั่วโมงที่สอน (ภาพรวม)</h3>
                    <p class="text-sm text-gray-500">หมวดหมู่และจำนวนชั่วโมง</p>
                </div>
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                        onclick="toggleForm('hourForm')">
                    เพิ่มชั่วโมง
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->teaching_hours ?? [] as $hour)
                    @php($hourId = $hour['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $hour['category'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? 'ชั่วโมง' }}
                                </p>
                                @if(!empty($hour['note']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $hour['note'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if($hourId)
                                    <button type="button"
                                            class="text-blue-600 text-sm hover:underline"
                                            onclick="toggleEditForm('hour-edit-{{ $hourId }}')">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      action="{{ route('teacher.courses.hours.destroy', ['course' => $course, 'hour' => $hourId ?? '']) }}"
                                      onsubmit="return confirm('ยืนยันการลบข้อมูลชุดนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if($hourId)
                            <form id="hour-edit-{{ $hourId }}" method="POST"
                                  action="{{ route('teacher.courses.hours.update', ['course' => $course, 'hour' => $hourId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                                @csrf
                                @method('PUT')
                                <select name="category" class="border rounded-xl px-3 py-2" required>
                                    <option value="ทฤษฎี" @selected(($hour['category'] ?? '') === 'ทฤษฎี')>ทฤษฎี</option>
                                    <option value="ปฏิบัติ" @selected(($hour['category'] ?? '') === 'ปฏิบัติ')>ปฏิบัติ</option>
                                </select>
                                <input type="number" step="0.1" name="hours"
                                       class="border rounded-xl px-3 py-2"
                                       value="{{ $hour['hours'] ?? '' }}" required>
                                <select name="unit" class="border rounded-xl px-3 py-2" required>
                                    <option value="ชั่วโมง/สัปดาห์"
                                            @selected(($hour['unit'] ?? '') === 'ชั่วโมง/สัปดาห์')>
                                        ชั่วโมง/สัปดาห์
                                    </option>
                                </select>
                                <input type="hidden" name="note" value="">
                                <div class="md:col-span-4 text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                                        บันทึก
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">ยังไม่มีข้อมูลชั่วโมงสอน</p>
                @endforelse
            </div>

            <form id="hourForm" method="POST"
                  action="{{ route('teacher.courses.hours.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="category" class="border rounded-xl px-3 py-2" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <option value="ทฤษฎี">ทฤษฎี</option>
                    <option value="ปฏิบัติ">ปฏิบัติ</option>
                </select>
                <input type="number" step="0.1" name="hours"
                       class="border rounded-xl px-3 py-2"
                       placeholder="ชั่วโมง" required>
                <select name="unit" class="border rounded-xl px-3 py-2" required>
                    <option value="">เลือกหน่วย</option>
                    <option value="ชั่วโมง/สัปดาห์">ชั่วโมง/สัปดาห์</option>
                </select>
                <input type="hidden" name="note" value="">
                <div class="md:col-span-4 text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                        บันทึกชั่วโมง
                    </button>
                </div>
            </form>
        </section>

        {{-- เนื้อหาที่สอน + ระยะเวลา --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">เนื้อหาที่สอน + ระยะเวลา</h3>
                    <p class="text-sm text-gray-500">หัวข้อบทเรียนและจำนวนชั่วโมง</p>
                </div>
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                        onclick="toggleForm('lessonForm')">
                    เพิ่มหัวข้อ
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->lessons ?? [] as $lesson)
                    @php($lessonId = $lesson['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $lesson['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $lesson['hours'] ?? 0 }} ชั่วโมง • {{ $lesson['period'] ?? '-' }}
                                </p>
                                @if(!empty($lesson['details']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if($lessonId)
                                    <button type="button"
                                            class="text-blue-600 text-sm hover:underline"
                                            onclick="toggleEditForm('lesson-edit-{{ $lessonId }}')">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lessonId ?? '']) }}"
                                      onsubmit="return confirm('ยืนยันการลบบทเรียนนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if($lessonId)
                            <form id="lesson-edit-{{ $lessonId }}" method="POST"
                                  action="{{ route('teacher.courses.lessons.update', ['course' => $course, 'lesson' => $lessonId]) }}"
                                  class="hidden space-y-4 mt-3">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input type="text" name="title"
                                           class="border rounded-xl px-3 py-2"
                                           value="{{ $lesson['title'] ?? '' }}" required>
                                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                                        @foreach([1,2,3,4,5] as $hourOption)
                                            <option value="{{ $hourOption }}"
                                                @selected(($lesson['hours'] ?? null) == $hourOption)>
                                                {{ $hourOption }} ชั่วโมง
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="period" class="border rounded-xl px-3 py-2" required>
                                        <option value="1-2 เดือน" @selected(($lesson['period'] ?? '') === '1-2 เดือน')>
                                            1–2 เดือน
                                        </option>
                                        <option value="3-4 เดือน" @selected(($lesson['period'] ?? '') === '3-4 เดือน')>
                                            3–4 เดือน
                                        </option>
                                    </select>
                                </div>
                                <textarea name="details" rows="3"
                                          class="w-full border rounded-xl px-3 py-2"
                                          placeholder="รายละเอียดเพิ่มเติม">{{ $lesson['details'] ?? '' }}</textarea>
                                <div class="text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                                        บันทึก
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">ยังไม่มีหัวข้อบทเรียน</p>
                @endforelse
            </div>

            <form id="lessonForm" method="POST"
                  action="{{ route('teacher.courses.lessons.store', $course) }}"
                  class="hidden mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="title"
                           class="border rounded-xl px-3 py-2"
                           placeholder="หัวข้อบทเรียน" required>
                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                        <option value="">เลือกชั่วโมง</option>
                        @foreach([1,2,3,4,5] as $hourOption)
                            <option value="{{ $hourOption }}">{{ $hourOption }} ชั่วโมง</option>
                        @endforeach
                    </select>
                    <select name="period" class="border rounded-xl px-3 py-2" required>
                        <option value="">เลือกช่วงเวลา</option>
                        <option value="1-2 เดือน">1–2 เดือน</option>
                        <option value="3-4 เดือน">3–4 เดือน</option>
                    </select>
                </div>
                <textarea name="details" rows="3"
                          class="w-full border rounded-xl px-3 py-2"
                          placeholder="รายละเอียดเพิ่มเติม"></textarea>
                <div class="text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                        บันทึกหัวข้อ
                    </button>
                </div>
            </form>
        </section>

        {{-- การบ้าน / ชิ้นงาน --}}
        @php($lessonTitles = collect($course->lessons ?? [])->pluck('title')->filter()->values())
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 mb-10">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">การบ้าน / ชิ้นงาน</h3>
                    <p class="text-sm text-gray-500">ข้อมูลการมอบหมายงานให้ผู้เรียน</p>
                </div>
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                        onclick="toggleForm('assignmentForm')">
                    เพิ่มการบ้าน
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->assignments ?? [] as $assignment)
                    @php($assignmentId = $assignment['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $assignment['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    คะแนนรวม: {{ $assignment['score'] ?? '-' }}
                                    @if(!empty($assignment['due_date']))
                                        <span class="mx-2 text-gray-300">|</span>
                                        กำหนดส่ง:
                                        {{ \Illuminate\Support\Carbon::parse($assignment['due_date'])->locale('th')->isoFormat('D MMM YYYY') }}
                                    @endif
                                </p>
                                @if(!empty($assignment['notes']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if($assignmentId)
                                    <button type="button"
                                            class="text-blue-600 text-sm hover:underline"
                                            onclick="toggleEditForm('assignment-edit-{{ $assignmentId }}')">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      action="{{ route('teacher.courses.assignments.destroy', ['course' => $course, 'assignment' => $assignmentId ?? '']) }}"
                                      onsubmit="return confirm('ยืนยันการลบการบ้านนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if($assignmentId)
                            <form id="assignment-edit-{{ $assignmentId }}" method="POST"
                                  action="{{ route('teacher.courses.assignments.update', ['course' => $course, 'assignment' => $assignmentId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                                @csrf
                                @method('PUT')
                                <select name="title" class="border rounded-xl px-3 py-2" required>
                                    @foreach($lessonTitles as $title)
                                        <option value="{{ $title }}"
                                            @selected(($assignment['title'] ?? '') === $title)>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="date" name="due_date"
                                       class="border rounded-xl px-3 py-2"
                                       value="{{ $assignment['due_date'] ?? '' }}">
                                <input type="number" step="0.1" name="score"
                                       class="border rounded-xl px-3 py-2"
                                       value="{{ $assignment['score'] ?? '' }}" required>
                                <textarea name="notes" rows="1"
                                          class="border rounded-xl px-3 py-2"
                                          placeholder="รายละเอียดงานเพิ่มเติม">{{ $assignment['notes'] ?? '' }}</textarea>
                                <div class="md:col-span-4 text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                                        บันทึก
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">ยังไม่มีการมอบหมายการบ้าน</p>
                @endforelse
            </div>

            <form id="assignmentForm" method="POST"
                  action="{{ route('teacher.courses.assignments.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="title" class="border rounded-xl px-3 py-2"
                        required {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>
                    <option value="">เลือกหัวข้อจากบทเรียน</option>
                    @foreach($lessonTitles as $title)
                        <option value="{{ $title }}">{{ $title }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_date"
                       class="border rounded-xl px-3 py-2">
                <input type="number" step="0.1" name="score"
                       class="border rounded-xl px-3 py-2"
                       placeholder="คะแนนรวม" required>
                <textarea name="notes" rows="1"
                          class="border rounded-xl px-3 py-2"
                          placeholder="รายละเอียดงานเพิ่มเติม"></textarea>
                <div class="md:col-span-4 text-right">
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded-xl"
                            {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>
                        บันทึกการบ้าน
                    </button>
                </div>
            </form>
        </section>
    @endunless
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const courseSelector = document.getElementById('courseSelector');
        if (courseSelector) {
            courseSelector.addEventListener('change', event => {
                const targetUrl = event.target.value;
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        }
    });

    function toggleForm(id) {
        const block = document.getElementById(id);
        if (block) {
            block.classList.toggle('hidden');
        }
    }

    function toggleEditForm(id) {
        const block = document.getElementById(id);
        if (block) {
            block.classList.toggle('hidden');
        }
    }
</script>
@endsection
