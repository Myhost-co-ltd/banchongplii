@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร')

@section('content')
@php
    $courseOptions = collect($courses ?? []);
    $currentTerm = $selectedTerm ?? request('term');
    $tz = config('app.timezone', 'Asia/Bangkok');

    $hoursByTerm = collect($hours ?? []);
    $lessonsByTerm = collect($lessons ?? []);
    $assignmentsByTerm = collect($assignments ?? []);
    $lessonTitles = $lessonsByTerm->pluck('title')->filter()->values();
@endphp

<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    {{-- HEADER --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">รายละเอียดหลักสูตร</p>
                <h1 class="text-3xl font-bold text-gray-900">จัดการหลักสูตร</h1>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('teacher.course-create') }}"
                       class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                        กลับไปหน้าสร้างหลักสูตร
                    </a>
                    @if($course)
                        <a href="{{ route('teacher.courses.edit', $course) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                            แก้ไขหลักสูตร
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-80 space-y-4">
                {{-- เลือกหลักสูตร --}}
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label for="courseSelector" class="block text-sm font-semibold text-gray-700 mb-2">
                            เลือกหลักสูตร
                        </label>
                        <select id="courseSelector"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($courseOptions as $courseOption)
                                <option value="{{ route('course.detail', ['course' => $courseOption->id, 'term' => $currentTerm]) }}"
                                        @selected(optional($course)->id === $courseOption->id)>
                                    {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="border border-dashed border-gray-200 rounded-2xl p-4 text-sm text-gray-500">
                        ยังไม่มีข้อมูลหลักสูตรที่สร้างไว้ในระบบ
                    </div>
                @endif

                {{-- เลือกภาคเรียน --}}
                @if($course)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            เลือกภาคเรียน
                        </label>
                        <form id="termForm" action="{{ route('course.detail', $course) }}" method="GET">
                            <select name="term"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="document.getElementById('termForm').submit()">
                                <option value="">-- เลือกภาคเรียน --</option>
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                            </select>
                        </form>
                        <p class="text-xs text-gray-400 mt-1">
                            * ค่าเริ่มต้นเป็นว่าง ต้องเลือกภาคเรียนก่อนจึงจะสามารถดู/เพิ่มข้อมูลในภาคเรียนนั้นได้
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @unless($course)
        {{-- ยังไม่ได้เลือกหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">ยังไม่ได้เลือกหลักสูตร</h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                กรุณาเลือกหลักสูตรที่ต้องการจัดการจากหน้าสร้างหลักสูตร หรือสร้างหลักสูตรใหม่ก่อน
                จึงจะสามารถดูรายละเอียดภาคเรียน ชั่วโมงสอน และงาน/คะแนนเก็บของหลักสูตรนั้นได้
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                เพิ่มหลักสูตรใหม่
            </a>
        </div>
    @else

        {{-- ข้อมูลสรุปหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $course->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ใช้กับห้องเรียน</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse($course->rooms ?? [] as $room)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">{{ $room }}</span>
                        @empty
                            <span class="text-gray-400 text-sm">ยังไม่ได้กำหนดห้องเรียนสำหรับหลักสูตรนี้</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ภาคเรียนที่กำลังดูข้อมูล</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if($currentTerm === '1')
                            ภาคเรียนที่ 1
                        @elseif($currentTerm === '2')
                            ภาคเรียนที่ 2
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">ปีการศึกษา</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $course->year ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">รายละเอียดเพิ่มเติมของหลักสูตร</p>
                    <p class="text-gray-700 mt-1 leading-relaxed">
                        {{ $course->description ?? 'ยังไม่ได้กรอกรายละเอียดเพิ่มเติมของหลักสูตร' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- แจ้งเตือนหากยังไม่เลือกภาคเรียน --}}
        @if(!$currentTerm)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-3xl p-6">
                <p class="font-semibold mb-1">ยังไม่ได้เลือกภาคเรียน</p>
                <p class="text-sm">
                    กรุณาเลือก <strong>ภาคเรียนที่ 1</strong> หรือ <strong>ภาคเรียนที่ 2</strong> ก่อน
                    เพื่อให้ระบบแสดงและให้จัดการข้อมูลต่าง ๆ ของภาคเรียนนั้น เช่น ชั่วโมงสอน บทเรียน และงาน/คะแนนเก็บ
                </p>
            </div>
        @else

            {{-- สรุปชั่วโมงสอนตามหมวดหมู่ --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">ชั่วโมงสอน (สรุปตามหมวดหมู่)</h3>
                    <p class="text-sm text-gray-500">
                        ตรวจสอบการใช้ชั่วโมงสอนแยกตามหมวดหมู่ ตามเกณฑ์ชั่วโมงสูงสุดของหลักสูตรในภาคเรียนนี้
                    </p>
                    <div class="text-sm mt-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                            รวมภาคเรียนนี้: {{ number_format($lessonUsedTotal ?? 0, 1) }} / {{ number_format($lessonAllowedTotal ?? 0, 1) }} ชั่วโมง
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                            เหลือให้จัดสรร: {{ number_format($lessonRemainingTotal ?? 0, 1) }} ชั่วโมง
                        </span>
                        @if(($lessonRemainingTotal ?? 0) <= 0)
                            <div class="text-red-600 text-xs mt-1">
                                ชั่วโมงรวมครบแล้ว หากต้องการเพิ่มให้แก้ไขจำนวนชั่วโมงสอนหรือปรับบทเรียนเดิม
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-sm mb-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                        ชั่วโมงสอนที่ยังเหลือให้จัดสรร: {{ number_format($lessonRemainingTotal ?? 0, 1) }} ชั่วโมง
                    </span>

                    @if(($lessonRemainingTotal ?? 0) <= 0)
                        <div class="text-red-600 text-xs mt-1">
                            ชั่วโมงสอนในภาคเรียนนี้เต็มจำนวนแล้ว หากต้องการเพิ่มชั่วโมงสอน
                            กรุณาปรับชั่วโมงในหมวดอื่นหรือแก้ไขโครงสร้างชั่วโมงของหลักสูตร
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    @forelse($hoursByTerm as $hour)
                        <div class="border border-gray-100 rounded-2xl p-4 space-y-2">
                            <p class="font-semibold text-gray-900">{{ $hour['category'] ?? '-' }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $hour['hours'] ?? 0 }} ชั่วโมง
                            </p>
                            @if(!empty($hour['note']))
                                <p class="text-sm text-gray-500">{{ $hour['note'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center">
                            ยังไม่มีการตั้งค่าจำนวนชั่วโมงสอนแยกตามหมวดหมู่สำหรับหลักสูตรนี้ในภาคเรียนที่เลือก
                        </p>
                        <div class="text-sm mt-2 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                                รวมภาคเรียนนี้: {{ number_format($lessonUsedTotal ?? 0, 1) }} / {{ number_format($lessonAllowedTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                                เหลือให้จัดสรร: {{ number_format($lessonRemainingTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            @if(($lessonRemainingTotal ?? 0) <= 0)
                                <div class="text-red-600 text-xs mt-1">
                                    ชั่วโมงรวมครบแล้ว หากต้องการเพิ่มให้แก้ไขชั่วโมงสอนหรือปรับบทเรียนเดิม
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- รายการบทเรียน + ชั่วโมงสอนรายบท --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">รายการบทเรียน + ชั่วโมงสอนรายบท</h3>
                        <p class="text-sm text-gray-500">
                            จัดการบทเรียนแต่ละหัวข้อ พร้อมกำหนดหมวดหมู่และจำนวนชั่วโมงที่ใช้ในแต่ละภาคเรียน
                        </p>
                        <div class="text-sm mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                                รวมภาคเรียนนี้: {{ number_format($lessonUsedTotal ?? 0, 1) }} / {{ number_format($lessonAllowedTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                                เหลือให้จัดสรร: {{ number_format($lessonRemainingTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            @if(($lessonRemainingTotal ?? 0) <= 0)
                                <div class="text-red-600 text-xs mt-1">
                                    ชั่วโมงรวมครบแล้ว หากต้องการเพิ่มให้แก้ไขชั่วโมงสอนหรือปรับบทเรียนเดิม
                                </div>
                            @endif
                        </div>
                    </div>
                    <button type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                            onclick="toggleForm('lessonForm')">
                        เพิ่มบทเรียน
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($lessonsByTerm as $lesson)
                        @php($lessonId = $lesson['id'] ?? null)
                        <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $lesson['title'] ?? '-' }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $lesson['category'] ?? '-' }} · {{ $lesson['hours'] ?? 0 }} ชั่วโมง
                                    </p>
                                    @if(!empty($lesson['created_at']))
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            เพิ่มเมื่อ: {{ \Illuminate\Support\Carbon::parse($lesson['created_at'])->timezone($tz)->locale('th')->isoFormat('D MMM YYYY HH:mm') }}
                                        </p>
                                    @endif
                                    @if(!empty($lesson['details']))
                                        <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                                    @endif
                                </div>
                                @if($lessonId)
                                    <div class="flex items-center gap-3 text-sm">
                                        <button type="button"
                                                class="text-blue-600 hover:underline"
                                                onclick="toggleEditForm('lesson-edit-{{ $lessonId }}')">
                                            แก้ไข
                                        </button>
                                        <form method="POST"
                                              action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lessonId]) }}"
                                              onsubmit="return confirm('ต้องการลบบทเรียนนี้ใช่หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline" type="submit">ลบ</button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            @if($lessonId)
                                <form id="lesson-edit-{{ $lessonId }}" method="POST"
                                      action="{{ route('teacher.courses.lessons.update', ['course' => $course, 'lesson' => $lessonId]) }}"
                                      class="hidden space-y-3 mt-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="term" value="{{ $currentTerm }}">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <select name="category" class="border rounded-xl px-3 py-2" required>
                                            @foreach(($lessonCapacity ?? []) as $cat => $summary)
                                                <option value="{{ $cat }}" @selected(($lesson['category'] ?? '') === $cat)>
                                                    {{ $cat }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="title"
                                               class="border rounded-xl px-3 py-2"
                                               value="{{ $lesson['title'] ?? '' }}" required>
                                        <input type="number" step="0.1" min="0.1" name="hours"
                                               class="border rounded-xl px-3 py-2"
                                               value="{{ $lesson['hours'] ?? '' }}" required>
                                    </div>
                                    <textarea name="details" rows="2"
                                              class="w-full border rounded-xl px-3 py-2"
                                              placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับบทเรียน (ถ้ามี)">{{ $lesson['details'] ?? '' }}</textarea>
                                    <div class="text-right">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center">
                            ยังไม่มีบทเรียนสำหรับหลักสูตรนี้ในภาคเรียนที่เลือก
                        </p>
                        <div class="text-sm mt-2 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                                รวมภาคเรียนนี้: {{ number_format($lessonUsedTotal ?? 0, 1) }} / {{ number_format($lessonAllowedTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                                เหลือให้จัดสรร: {{ number_format($lessonRemainingTotal ?? 0, 1) }} ชั่วโมง
                            </span>
                            @if(($lessonRemainingTotal ?? 0) <= 0)
                                <div class="text-red-600 text-xs mt-1">
                                    ชั่วโมงรวมครบแล้ว หากต้องการเพิ่มให้แก้ไขชั่วโมงสอนหรือปรับบทเรียนเดิม
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if(!empty($lessonCapacity))
                    <form id="lessonForm" method="POST"
                          action="{{ route('teacher.courses.lessons.store', $course) }}"
                          class="hidden mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="term" value="{{ $currentTerm }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <select name="category" id="lessonCategory"
                                    class="border rounded-xl px-3 py-2" required>
                                <option value="">เลือกหมวดหมู่ (เช่น เนื้อหาหลัก / ทบทวน / กิจกรรม)</option>
                                @foreach(($lessonCapacity ?? []) as $category => $summary)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="title"
                                   class="border rounded-xl px-3 py-2"
                                   placeholder="หัวข้อบทเรียน" required>
                            <input type="number" step="0.1" min="0.1" name="hours"
                                   id="lessonHours"
                                   class="border rounded-xl px-3 py-2"
                                   placeholder="ชั่วโมงที่ใช้จริง" required>
                        </div>
                        <div class="text-sm text-gray-600" id="lessonRemaining">
                            @foreach(($lessonCapacity ?? []) as $cat => $summary)
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-gray-100 mr-2"
                                      data-remaining="{{ $cat }}">
                                    {{ $cat }} เหลือได้อีก {{ number_format($summary['remaining'], 1) }} ชั่วโมง จากทั้งหมด {{ number_format($summary['allowed'], 1) }}
                                </span>
                            @endforeach
                        </div>
                        <textarea name="details" rows="3"
                                  class="w-full border rounded-xl px-3 py-2"
                                  placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับบทเรียน (ถ้ามี)"></textarea>
                        <div class="text-right">
                            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                                บันทึกบทเรียน
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-4 text-sm text-red-600">
                        ยังไม่ได้ตั้งค่าจำนวนชั่วโมงสูงสุดของแต่ละหมวดในหลักสูตรนี้
                        กรุณากลับไปแก้ไขข้อมูลหลักสูตรเพื่อกำหนดโครงสร้างชั่วโมงก่อน
                    </div>
                @endif
            </section>

            {{-- งาน / คะแนนเก็บ --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 mb-10">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">งาน / คะแนนเก็บ</h3>
                        <p class="text-sm text-gray-500">
                            กำหนดงาน คะแนนเต็ม และสัดส่วนคะแนนเก็บรวมของภาคเรียนนี้ (ไม่เกิน 70 คะแนน)
                        </p>
                        <div class="text-sm mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                                คะแนนเก็บรวมภาคเรียนนี้: {{ $assignmentTotal ?? 0 }} / 70
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                                คะแนนเก็บที่ยังเหลือให้กำหนด: {{ $assignmentRemaining ?? 70 }}
                            </span>
                            @if(($assignmentRemaining ?? 0) <= 0)
                                <div class="text-red-600 text-xs mt-1">
                                    คะแนนเก็บรวมครบ 70 แล้ว หากต้องการเพิ่มงานใหม่
                                    กรุณาลดคะแนนจากงานเดิมหรือปรับโครงสร้างคะแนนเก็บก่อน
                                </div>
                            @endif
                        </div>
                    </div>
                    <button type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                            onclick="toggleForm('assignmentForm')">
                        เพิ่มงาน / คะแนนเก็บ
                    </button>
                </div>

                @if(($assignmentRemaining ?? 70) <= 0)
                    <div class="mb-4 border border-red-200 bg-red-50 text-red-700 rounded-2xl p-3 text-sm">
                        คะแนนเก็บรวมครบ 70 แล้ว หากต้องการเพิ่มงานใหม่
                        กรุณาปรับลดคะแนนของงานเดิมก่อน
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse($assignmentsByTerm as $assignment)
                        @php($assignmentId = $assignment['id'] ?? null)
                        <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $assignment['title'] ?? '-' }}</p>
                                    <p class="text-sm text-gray-600">
                                        คะแนนเต็ม: {{ $assignment['score'] ?? '-' }}
                                        @if(!empty($assignment['due_date']))
                                            <span class="mx-2 text-gray-300">|</span>
                                            กำหนดส่ง:
                                            {{ \Illuminate\Support\Carbon::parse($assignment['due_date'])->timezone($tz)->locale('th')->isoFormat('D MMM YYYY') }}
                                        @endif
                                    </p>
                                    @if(!empty($assignment['notes']))
                                        <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                                    @endif>
                                    @if(!empty($assignment['created_at']))
                                        <p class="text-xs text-gray-500 mt-1">
                                            เพิ่มเมื่อ: {{ \Illuminate\Support\Carbon::parse($assignment['created_at'])->timezone($tz)->locale('th')->isoFormat('D MMM YYYY HH:mm') }}
                                        </p>
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
                                          onsubmit="return confirm('ต้องการลบงาน/คะแนนเก็บนี้ใช่หรือไม่?')">
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
                                    <input type="hidden" name="term" value="{{ $currentTerm }}">
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
                                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)">{{ $assignment['notes'] ?? '' }}</textarea>
                                    <div class="md:col-span-4 text-right">
                                        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">
                                            บันทึกการแก้ไข
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center">
                            ยังไม่มีการกำหนดงาน/คะแนนเก็บสำหรับหลักสูตรนี้ในภาคเรียนที่เลือก
                        </p>
                        <div class="text-sm mt-2 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700">
                                คะแนนเก็บรวมภาคเรียนนี้: {{ $assignmentTotal ?? 0 }} / 70
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-50 text-green-700 ml-2">
                                คะแนนเก็บที่ยังเหลือให้กำหนด: {{ $assignmentRemaining ?? 70 }}
                            </span>
                            @if(($assignmentRemaining ?? 0) <= 0)
                                <div class="text-red-600 text-xs mt-1">
                                    คะแนนเก็บรวมครบ 70 แล้ว หากต้องการเพิ่มงานใหม่
                                    กรุณาปรับลดคะแนนของงานเดิมก่อน
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>

                <form id="assignmentForm" method="POST"
                      action="{{ route('teacher.courses.assignments.store', $course) }}"
                      class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <input type="hidden" name="term" value="{{ $currentTerm }}">
                    <select name="title" class="border rounded-xl px-3 py-2"
                            required {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>
                        <option value="">เลือกบทเรียนที่งานนี้เชื่อมโยง</option>
                        @foreach($lessonTitles as $title)
                            <option value="{{ $title }}">{{ $title }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="due_date"
                           class="border rounded-xl px-3 py-2">
                    <input type="number" step="0.1" name="score"
                           class="border rounded-xl px-3 py-2"
                           placeholder="คะแนนเต็มของงาน (คะแนนเก็บรวมทุกงานไม่ควรเกิน 70)" required>
                    @if(($assignmentRemaining ?? 70) <= 0)
                        <p class="text-xs text-red-600 md:col-span-4">
                            คะแนนเก็บรวมครบ 70 แล้ว ไม่สามารถเพิ่มคะแนนเก็บใหม่ได้
                            กรุณาปรับคะแนนของงานเดิมก่อน
                        </p>
                    @endif
                    <textarea name="notes" rows="1"
                              class="border rounded-xl px-3 py-2"
                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                    <div class="md:col-span-4 text-right">
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-xl"
                                {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>
                            บันทึกงาน / คะแนนเก็บ
                        </button>
                    </div>
                </form>
            </section>
        @endif {{-- end if currentTerm --}}
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

        const capacity = @json($lessonCapacity ?? []);
        const categorySelect = document.getElementById('lessonCategory');
        const hoursInput = document.getElementById('lessonHours');
        const remainingDisplay = document.getElementById('lessonRemaining');

        const refreshRemaining = () => {
            if (!categorySelect || !hoursInput) return;
            const cat = categorySelect.value;
            const info = capacity[cat];
            if (info) {
                hoursInput.max = info.remaining;
                hoursInput.placeholder = `ชั่วโมงที่ใช้จริง (ไม่เกิน ${info.remaining.toFixed(1)} / ${info.allowed.toFixed(1)})`;
                hoursInput.disabled = info.remaining <= 0;
            } else {
                hoursInput.removeAttribute('max');
                hoursInput.placeholder = 'ชั่วโมงที่ใช้จริง';
                hoursInput.disabled = true;
            }
            if (remainingDisplay) {
                remainingDisplay.querySelectorAll('[data-remaining]').forEach(el => {
                    el.classList.toggle('font-semibold', el.dataset.remaining === cat);
                });
            }
        };

        if (categorySelect) {
            categorySelect.addEventListener('change', refreshRemaining);
            refreshRemaining();
        }

        // ข้อความแจ้งเตือนภาษาไทยสำหรับการกรอกชั่วโมง
        document.querySelectorAll('input[name="hours"]').forEach((input) => {
            input.addEventListener('input', () => input.setCustomValidity(''));
            input.addEventListener('invalid', () => {
                if (input.validity.rangeOverflow) {
                    const max = input.getAttribute('max');
                    input.setCustomValidity(max ? `กรุณากรอกชั่วโมงไม่เกิน ${max} ชั่วโมง` : 'กรุณากรอกจำนวนชั่วโมงให้ไม่เกินค่าที่กำหนด');
                } else if (input.validity.rangeUnderflow || input.validity.stepMismatch || input.validity.valueMissing) {
                    input.setCustomValidity('กรุณากรอกจำนวนชั่วโมงให้ถูกต้อง');
                }
            });
        });
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
