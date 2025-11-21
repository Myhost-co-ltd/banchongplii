@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร | แดชบอร์ดครู')

@section('content')
@php($courseOptions = collect($courses ?? []))
<div class="space-y-8 overflow-y-auto pr-2 pb-8">

    {{-- ส่วนหัว --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <p class="text-sm text-slate-500 uppercase tracking-wide">รายละเอียดหลักสูตร</p>
        <h2 class="text-3xl font-bold text-gray-900 mt-1">รายละเอียดหลักสูตร</h2>
        <p class="text-gray-600 mt-1">
            ดูรายละเอียดของหลักสูตรที่ครูกำลังสอนและจัดการข้อมูลที่เกี่ยวข้องทั้งหมด
        </p>

        <div class="mt-4 flex gap-3">
            <a href="{{ route('teacher.course-create') }}"
               class="px-4 py-2 bg-gray-200 rounded-xl text-gray-700 text-sm">
                กลับไปหน้าสร้างหลักสูตร
            </a>

            @if($course)
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                    แก้ไขข้อมูลหลักสูตร
                </a>
            @endif
        </div>

        {{-- เลือกหลักสูตรอื่น --}}
        <div class="mt-6 border-t border-gray-100 pt-6">
            @if($courseOptions->isNotEmpty())
                <label for="courseSelector" class="block text-sm font-semibold text-gray-700">
                    เลือกหลักสูตรที่ต้องการดูรายละเอียด
                </label>
                <div class="mt-2">
                    <select id="courseSelector"
                            class="w-full md:w-80 border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($courseOptions as $courseOption)
                            <option value="{{ route('course.detail', $courseOption) }}"
                                    @selected(optional($course)->id === $courseOption->id)>
                                {{ $courseOption->name }} ({{ $courseOption->grade }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <p class="text-gray-500 text-sm">
                    ยังไม่มีข้อมูลหลักสูตร กรุณาสร้างหลักสูตรก่อน
                </p>
            @endif
        </div>
    </div>

    {{-- ถ้ามีหลักสูตร --}}
    @if($course)
        {{-- กล่องข้อมูลหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                    <p class="font-semibold text-gray-900 text-lg">{{ $course->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse ($course->rooms ?? [] as $room)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">{{ $room }}</span>
                        @empty
                            <span class="text-gray-400 text-sm">ยังไม่มีการระบุห้องเรียน</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ภาคเรียน</p>
                    <p class="font-semibold text-gray-900">{{ $course->term ? 'ภาคเรียนที่ '.$course->term : '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ปีการศึกษา</p>
                    <p class="font-semibold text-gray-900">{{ $course->year ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                    <p class="text-gray-700 mt-1 leading-relaxed">{{ $course->description ?? '-' }}</p>
                </div>
            </div>
        </div>

        @php
            $teachingHours = $course->teaching_hours ?? [];
            $lessons       = $course->lessons ?? [];
            $assignments   = $course->assignments ?? [];
            $lessonTitles  = collect($lessons)->pluck('title')->filter()->values();
        @endphp

        {{-- ชั่วโมงการสอน --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">ชั่วโมงที่สอน (ภาพรวม)</h3>
                    @if(empty($teachingHours))
                        <p class="text-gray-500 text-sm">ยังไม่มีการบันทึกชั่วโมงสอนในหลักสูตรนี้</p>
                    @endif
                </div>
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold"
                        data-toggle-target="hoursForm"
                        data-label-show="เพิ่มชั่วโมง"
                        data-label-hide="ยกเลิก">
                    เพิ่มชั่วโมง
                </button>
            </div>

            <div class="space-y-4">
                @forelse ($teachingHours as $hour)
                    @php $hourId = $hour['id'] ?? null; @endphp
                    <div class="border rounded-2xl p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $hour['category'] ?? 'หัวข้อ' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? 'ชั่วโมง' }}
                                </p>
                                @if (!empty($hour['note']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $hour['note'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if ($hourId)
                                    <button type="button"
                                            class="text-blue-600 text-sm font-semibold hover:underline"
                                            data-toggle-target="hours-edit-{{ $hourId }}"
                                            data-label-show="แก้ไข"
                                            data-label-hide="ยกเลิก">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      onsubmit="return confirm('ยืนยันการลบชั่วโมงนี้?')"
                                      action="{{ route('teacher.courses.hours.destroy', ['course' => $course, 'hour' => $hourId ?? '']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if ($hourId)
                            <form id="hours-edit-{{ $hourId }}"
                                  method="POST"
                                  action="{{ route('teacher.courses.hours.update', ['course' => $course, 'hour' => $hourId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 border-t md:pt-4">
                                @csrf
                                @method('PUT')
                                <select name="category" class="border rounded-xl px-3 py-2" required>
                                    <option value="">เลือกหัวข้อ</option>
                                    <option value="ทฤษฎี" @selected(($hour['category'] ?? '') === 'ทฤษฎี')>ทฤษฎี</option>
                                    <option value="ปฏิบัติ" @selected(($hour['category'] ?? '') === 'ปฏิบัติ')>ปฏิบัติ</option>
                                </select>
                                <input type="number"
                                       name="hours"
                                       step="0.5"
                                       min="0"
                                       class="border rounded-xl px-3 py-2"
                                       value="{{ $hour['hours'] ?? '' }}"
                                       placeholder="จำนวนชั่วโมง"
                                       required>
                                <select name="unit" class="border rounded-xl px-3 py-2" required>
                                    <option value="">เลือกหน่วย</option>
                                    <option value="ชั่วโมง/สัปดาห์" @selected(($hour['unit'] ?? '') === 'ชั่วโมง/สัปดาห์')>ชั่วโมง/สัปดาห์</option>
                                    <option value="ชั่วโมง/ภาคเรียน" @selected(($hour['unit'] ?? '') === 'ชั่วโมง/ภาคเรียน')>ชั่วโมง/ภาคเรียน</option>
                                </select>
                                <div class="md:col-span-4 flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm">ยังไม่มีการบันทึกชั่วโมงสอน</p>
                @endforelse
            </div>

            <form id="hoursForm"
                  method="POST"
                  action="{{ route('teacher.courses.hours.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="category" class="border rounded-xl px-3 py-2" required>
                    <option value="">เลือกหัวข้อ</option>
                    <option value="ทฤษฎี">ทฤษฎี</option>
                    <option value="ปฏิบัติ">ปฏิบัติ</option>
                </select>
                <select name="hours" class="border rounded-xl px-3 py-2" required>
                    <option value="">ชั่วโมง</option>
                    <option value="1">1 ชั่วโมง</option>
                    <option value="2">2 ชั่วโมง</option>
                </select>
                <select name="unit" class="border rounded-xl px-3 py-2" required>
                    <option value="">เลือกหน่วย</option>
                    <option value="ชั่วโมง/สัปดาห์">ชั่วโมง/สัปดาห์</option>
                    <option value="ชั่วโมง/ภาคเรียน">ชั่วโมง/ภาคเรียน</option>
                </select>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">เพิ่มชั่วโมง</button>
                </div>
            </form>
        </section>

        {{-- เนื้อหา --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">เนื้อหาที่สอน + ระยะเวลา</h3>
                <button type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold"
                        data-toggle-target="lessonsForm"
                        data-label-show="เพิ่มหัวข้อ"
                        data-label-hide="ยกเลิก">
                    เพิ่มหัวข้อ
                </button>
            </div>

            <div class="space-y-4">
                @forelse ($lessons as $lesson)
                    @php $lessonId = $lesson['id'] ?? null; @endphp
                    <div class="border rounded-2xl p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $lesson['title'] ?? 'หัวข้อ' }}</p>
                                <p class="text-sm text-gray-600">
                                    ใช้เวลา {{ $lesson['hours'] ?? '-' }} ชั่วโมง — ช่วงเวลา {{ $lesson['period'] ?? '-' }}
                                </p>
                                @if (!empty($lesson['details']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if ($lessonId)
                                    <button type="button"
                                            class="text-blue-600 text-sm font-semibold hover:underline"
                                            data-toggle-target="lessons-edit-{{ $lessonId }}"
                                            data-label-show="แก้ไข"
                                            data-label-hide="ยกเลิก">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      onsubmit="return confirm('ยืนยันการลบหัวข้อนี้?')"
                                      action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lessonId ?? '']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if ($lessonId)
                            <form id="lessons-edit-{{ $lessonId }}"
                                  method="POST"
                                  action="{{ route('teacher.courses.lessons.update', ['course' => $course, 'lesson' => $lessonId]) }}"
                                  class="hidden space-y-4 pt-2 border-t md:pt-4">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input type="text"
                                           name="title"
                                           class="border rounded-xl px-3 py-2"
                                           value="{{ $lesson['title'] ?? '' }}"
                                           placeholder="หัวข้อบทเรียน"
                                           required>
                                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                                        <option value="">ชั่วโมง</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected(($lesson['hours'] ?? null) == $i)>{{ $i }} ชั่วโมง</option>
                                        @endfor
                                        @if (!empty($lesson['hours']) && $lesson['hours'] > 5)
                                            <option value="{{ $lesson['hours'] }}" selected>{{ $lesson['hours'] }} ชั่วโมง</option>
                                        @endif
                                    </select>
                                    @php
                                        $periodOptions = ['เดือน 1-2', 'เดือน 3-4'];
                                    @endphp
                                    <select name="period" class="border rounded-xl px-3 py-2" required>
                                        <option value="">ช่วงเวลา</option>
                                        @foreach ($periodOptions as $period)
                                            <option value="{{ $period }}" @selected(($lesson['period'] ?? '') === $period)>{{ $period }}</option>
                                        @endforeach
                                        @if (!empty($lesson['period']) && !in_array($lesson['period'], $periodOptions))
                                            <option value="{{ $lesson['period'] }}" selected>{{ $lesson['period'] }}</option>
                                        @endif
                                    </select>
                                </div>
                                <textarea name="details" rows="3" class="w-full border rounded-xl px-3 py-2" placeholder="รายละเอียด (ถ้ามี)">{{ $lesson['details'] ?? '' }}</textarea>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm">ยังไม่มีการบันทึกเนื้อหาที่สอน</p>
                @endforelse
            </div>

            <form id="lessonsForm"
                  method="POST"
                  action="{{ route('teacher.courses.lessons.store', $course) }}"
                  class="hidden mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="title" class="border rounded-xl px-3 py-2" placeholder="หัวข้อบทเรียน" required>
                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                        <option value="">ชั่วโมง</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }} ชั่วโมง</option>
                        @endfor
                    </select>
                    <select name="period" class="border rounded-xl px-3 py-2" required>
                        <option value="">ช่วงเวลา</option>
                        <option value="เดือน 1-2">เดือน 1-2</option>
                        <option value="เดือน 3-4">เดือน 3-4</option>
                    </select>
                </div>
                <textarea name="details" rows="3" class="w-full border rounded-xl px-3 py-2" placeholder="รายละเอียด (ถ้ามี)"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">เพิ่มหัวข้อ</button>
                </div>
            </form>
        </section>

        {{-- การบ้าน / ชิ้นงาน --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-xl font-semibold text-gray-900">การบ้าน / ชิ้นงาน</h3>
                <div class="text-right">
                    <button type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                            data-toggle-target="assignmentsForm"
                            data-label-show="เพิ่มการบ้าน"
                            data-label-hide="ยกเลิก"
                            @if($lessonTitles->isEmpty()) disabled title="โปรดเพิ่มหัวข้อบทเรียนก่อน" @endif>
                        เพิ่มการบ้าน
                    </button>
                    @if($lessonTitles->isEmpty())
                        <p class="text-xs text-gray-500 mt-1">ต้องเพิ่มหัวข้อบทเรียนก่อนจึงจะเพิ่มการบ้านได้</p>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($assignments as $assignment)
                    @php
                        $assignmentId = $assignment['id'] ?? null;
                        $assignmentDueDate = !empty($assignment['due_date'])
                            ? \Carbon\Carbon::parse($assignment['due_date'])->format('Y-m-d')
                            : '';
                    @endphp
                    <div class="border rounded-2xl p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $assignment['title'] ?? 'การบ้าน' }}</p>
                                <p class="text-sm text-gray-600">
                                    กำหนดส่ง:
                                    {{ $assignmentDueDate ? \Carbon\Carbon::parse($assignmentDueDate)->translatedFormat('j F Y') : '-' }}
                                    | คะแนนเต็ม: {{ $assignment['score'] ?? '-' }}
                                </p>
                                @if (!empty($assignment['notes']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if ($assignmentId)
                                    <button type="button"
                                            class="text-blue-600 text-sm font-semibold hover:underline"
                                            data-toggle-target="assignments-edit-{{ $assignmentId }}"
                                            data-label-show="แก้ไข"
                                            data-label-hide="ยกเลิก">
                                        แก้ไข
                                    </button>
                                @endif
                                <form method="POST"
                                      onsubmit="return confirm('ยืนยันการลบการบ้านนี้?')"
                                      action="{{ route('teacher.courses.assignments.destroy', ['course' => $course, 'assignment' => $assignmentId ?? '']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">ลบ</button>
                                </form>
                            </div>
                        </div>

                        @if ($assignmentId)
                            <form id="assignments-edit-{{ $assignmentId }}"
                                  method="POST"
                                  action="{{ route('teacher.courses.assignments.update', ['course' => $course, 'assignment' => $assignmentId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 border-t md:pt-4">
                                @csrf
                                @method('PUT')
                                <select name="title" class="border rounded-xl px-3 py-2 md:col-span-2" required>
                                    <option value="">เลือกจากหัวข้อบทเรียน</option>
                                    @foreach ($lessonTitles as $title)
                                        <option value="{{ $title }}" @selected(($assignment['title'] ?? '') === $title)>{{ $title }}</option>
                                    @endforeach
                                    @if (!empty($assignment['title']) && !$lessonTitles->contains($assignment['title']))
                                        <option value="{{ $assignment['title'] }}" selected>{{ $assignment['title'] }}</option>
                                    @endif
                                </select>
                                <input type="date" name="due_date" class="border rounded-xl px-3 py-2" value="{{ $assignmentDueDate }}">
                                <input type="number" step="0.5" min="0" name="score" class="border rounded-xl px-3 py-2" value="{{ $assignment['score'] ?? '' }}" placeholder="คะแนนเต็ม">
                                <textarea name="notes" rows="2" class="border rounded-xl px-3 py-2 md:col-span-4" placeholder="คำอธิบายงาน">{{ $assignment['notes'] ?? '' }}</textarea>
                                <div class="md:col-span-4 flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm">ยังไม่มีการบ้านหรือชิ้นงาน</p>
                @endforelse
            </div>

            <form id="assignmentsForm"
                  method="POST"
                  action="{{ route('teacher.courses.assignments.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="title"
                        class="border rounded-xl px-3 py-2"
                        required
                        @disabled($lessonTitles->isEmpty())>
                    <option value="">
                        {{ $lessonTitles->isEmpty()
                            ? 'ต้องเพิ่มหัวข้อบทเรียนก่อนจึงจะเลือกหัวข้อได้'
                            : 'เลือกจากหัวข้อบทเรียน'
                        }}
                    </option>
                    @foreach ($lessonTitles as $title)
                        <option value="{{ $title }}">{{ $title }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_date" class="border rounded-xl px-3 py-2">
                <input type="number" step="0.5" min="0" name="score" class="border rounded-xl px-3 py-2" placeholder="คะแนนเต็ม">
                <textarea name="notes" rows="2" class="border rounded-xl px-3 py-2 md:col-span-2" placeholder="คำอธิบายงาน / รายละเอียด"></textarea>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                            @disabled($lessonTitles->isEmpty())>
                        เพิ่มการบ้าน
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- ถ้ายังไม่ได้เลือกหลักสูตรเลย --}}
    @unless($course)
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">
                ยังไม่ได้เลือกหลักสูตรที่ต้องการดู
            </h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                กรุณาเลือกหลักสูตรจากด้านบน หรือสร้างหลักสูตรใหม่เพื่อเริ่มจัดการข้อมูลการสอน ชั่วโมงสอน เนื้อหา และการบ้านของนักเรียน
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                สร้างหลักสูตรใหม่
            </a>
        </div>
    @endunless

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const courseSelector = document.getElementById('courseSelector');
            if (courseSelector) {
                courseSelector.addEventListener('change', (event) => {
                    const targetUrl = event.target.value;
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                });
            }

            // toggle form ต่าง ๆ
            document.querySelectorAll('[data-toggle-target]').forEach((button) => {
                const targetId = button.getAttribute('data-toggle-target');
                const target = document.getElementById(targetId);

                if (!target) {
                    return;
                }

                const showLabel = button.getAttribute('data-label-show') || button.textContent.trim();
                const hideLabel = button.getAttribute('data-label-hide') || showLabel;

                const setState = (isVisible) => {
                    target.classList.toggle('hidden', !isVisible);
                    button.textContent = isVisible ? hideLabel : showLabel;
                    button.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
                    target.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
                };

                setState(!target.classList.contains('hidden'));

                button.addEventListener('click', () => {
                    if (button.disabled) {
                        return;
                    }
                    const shouldShow = target.classList.contains('hidden');
                    setState(shouldShow);
                });
            });
        });
    </script>
</div>
@endsection
