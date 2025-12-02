@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร')

@section('content')
@php
    $courseOptions = collect($courses ?? []);
    $currentTerm = $selectedTerm ?? request('term');
    $tz = config('app.timezone', 'Asia/Bangkok');
    $todayDate = now($tz)->toDateString();

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
                <p class="text-sm text-slate-500 uppercase tracking-widest"
                   data-i18n-th="รายละเอียดหลักสูตร"
                   data-i18n-en="Course Details">
                    รายละเอียดหลักสูตร
                </p>
                <h1 class="text-3xl font-bold text-gray-900"
                    data-i18n-th="จัดการหลักสูตร"
                    data-i18n-en="Course Management">
                    จัดการหลักสูตร
                </h1>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('teacher.course-create') }}"
                       class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm"
                       data-i18n-th="กลับไปหน้าสร้างหลักสูตร"
                       data-i18n-en="Back to create course">
                        กลับไปหน้าสร้างหลักสูตร
                    </a>

                    @if($course)
                        <a href="{{ route('teacher.courses.edit', $course) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm"
                           data-i18n-th="แก้ไขหลักสูตร"
                           data-i18n-en="Edit course">
                            แก้ไขหลักสูตร
                        </a>
                        <a href="{{ route('teacher.courses.export', ['course' => $course->id, 'term' => $currentTerm]) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm"
                           data-i18n-th="ส่งออก PDF"
                           data-i18n-en="Export PDF">
                            Export PDF
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-80 space-y-4">

                {{-- เลือกหลักสูตร --}}
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label for="courseSelector" class="block text-sm font-semibold text-gray-700 mb-2"
                               data-i18n-th="เลือกหลักสูตร"
                               data-i18n-en="Select course">
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
                    <div class="border border-dashed border-gray-200 rounded-2xl p-4 text-sm text-gray-500"
                            data-i18n-th="ยังไม่มีหลักสูตรที่ถูกสร้างไว้ในระบบ"
                            data-i18n-en="No course has been created in the system yet">
                        ยังไม่มีหลักสูตรที่ถูกสร้างไว้ในระบบ
                    </div>
                @endif

                {{-- เลือกภาคเรียน --}}
                @if($course)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"
                               data-i18n-th="เลือกภาคเรียน"
                               data-i18n-en="Select term">
                            เลือกภาคเรียน
                        </label>

                        <form id="termForm" action="{{ route('course.detail', $course) }}" method="GET">
                            <select name="term"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                onchange="document.getElementById('termForm').submit()">

                                <option value="" data-i18n-th="-- เลือกภาคเรียน --" data-i18n-en="-- Select term --">-- เลือกภาคเรียน --</option>
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}
                                        data-i18n-th="ภาคเรียนที่ 1" data-i18n-en="Term 1">
                                    ภาคเรียนที่ 1
                                </option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}
                                        data-i18n-th="ภาคเรียนที่ 2" data-i18n-en="Term 2">
                                    ภาคเรียนที่ 2
                                </option>
                            </select>
                        </form>

                        <p class="text-xs text-gray-400 mt-1"
                           data-i18n-th="* โปรดเลือกภาคเรียนก่อนเพื่อดูข้อมูลในภาคเรียนนั้น"
                           data-i18n-en="* Please select a term to view data in that term">
                            * โปรดเลือกภาคเรียนก่อนเพื่อดูข้อมูลในภาคเรียนนั้น
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ถ้ายังไม่ได้เลือกหลักสูตร --}}
    @unless($course)
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2"
                data-i18n-th="ยังไม่ได้เลือกหลักสูตร"
                data-i18n-en="No course selected yet">
                ยังไม่ได้เลือกหลักสูตร
            </h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto"
               data-i18n-th="กรุณาเลือกหลักสูตรจากหน้าสร้างหลักสูตรก่อน จึงจะสามารถดูรายละเอียดภาคเรียน ชั่วโมงสอน รายบทเรียน และงาน/คะแนนเก็บได้"
               data-i18n-en="Please pick a course from the create course page first to view term details, teaching hours, lessons, and score structure.">
                กรุณาเลือกหลักสูตรจากหน้าสร้างหลักสูตรก่อน จึงจะสามารถดูรายละเอียดภาคเรียน ชั่วโมงสอน
                รายบทเรียน และงาน/คะแนนเก็บได้
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition"
               data-i18n-th="เพิ่มหลักสูตรใหม่"
               data-i18n-en="Create a course now">
                เพิ่มหลักสูตรใหม่
            </a>
        </div>

    @else

        {{-- ?? ข้อมูลหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-sm text-gray-500"
                       data-i18n-th="ชื่อหลักสูตร"
                       data-i18n-en="Course name">
                        ชื่อหลักสูตร
                    </p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $course->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500"
                       data-i18n-th="ภาคเรียนที่ดูข้อมูล"
                       data-i18n-en="Term in view">
                        ภาคเรียนที่ดูข้อมูล
                    </p>
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
                    <p class="text-sm text-gray-500"
                       data-i18n-th="ห้อง / ระดับชั้น"
                       data-i18n-en="Rooms / Grade">
                        ห้อง / ระดับชั้น
                    </p>
                    <div class="flex items-center gap-3 flex-wrap mt-1">
                        <div class="flex flex-wrap gap-2">
                            @forelse($course->rooms ?? [] as $room)
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">{{ $room }}</span>
                            @empty
                                <span class="text-gray-400 text-sm">ยังไม่มีการกำหนดห้องเรียน</span>
                            @endforelse
                        </div>
                        <span class="text-lg font-semibold text-gray-900">
                            {{ $course->grade ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500"
                       data-i18n-th="รายละเอียดหลักสูตร"
                       data-i18n-en="Course description">
                        รายละเอียดหลักสูตร
                    </p>
                    <p class="text-gray-700 mt-1 leading-relaxed">
                        {{ $course->description ?? 'ยังไม่มีรายละเอียดเพิ่มเติม' }}
                    </p>
                </div>

            </div>
        </div>

        {{-- ถ้ายังไม่ได้เลือกภาคเรียน --}}
        @if(!$currentTerm)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-3xl p-6">
                <p class="font-semibold mb-1">ยังไม่ได้เลือกภาคเรียน</p>
                <p class="text-sm">
                    กรุณาเลือก <strong>ภาคเรียนที่ 1</strong> หรือ <strong>ภาคเรียนที่ 2</strong>
                    เพื่อดูข้อมูลบทเรียน ชั่วโมงสอน และงาน/คะแนนเก็บ
                </p>
            </div>

        @else

            {{-- ?? ชั่วโมงสอนตามหมวดหมู่ --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
                <h3 class="text-xl font-semibold text-gray-900 mb-3"
                    data-i18n-th="ชั่วโมงสอน (ตามหมวดหมู่)"
                    data-i18n-en="Teaching hours (by category)">
                    ชั่วโมงสอน (ตามหมวดหมู่)
                </h3>
                <p class="text-sm text-gray-500"
                   data-i18n-th="ชั่วโมงสอนที่กำหนดไว้ในหลักสูตรสำหรับภาคเรียนนี้"
                   data-i18n-en="Planned teaching hours for this term">
                    ชั่วโมงสอนที่กำหนดไว้ในหลักสูตรสำหรับภาคเรียนนี้
                </p>

                <div class="space-y-4 mt-4">
                    @forelse($hoursByTerm as $hour)
                        <div class="border border-gray-100 rounded-2xl p-4 space-y-2">
                            <p class="font-semibold text-gray-900">{{ $hour['category'] }}</p>
                            <p class="text-sm text-gray-600">{{ $hour['hours'] }} ชั่วโมง</p>

                            @if(!empty($hour['note']))
                                <p class="text-sm text-gray-500">{{ $hour['note'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center"
                           data-i18n-th="ยังไม่มีการตั้งค่าชั่วโมงสอนสำหรับภาคเรียนนี้"
                           data-i18n-en="No teaching hours set for this term yet">
                            ยังไม่มีการตั้งค่าชั่วโมงสอนสำหรับภาคเรียนนี้
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- ?? รายการบทเรียน --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900"
                            data-i18n-th="รายการบทเรียน"
                            data-i18n-en="Lessons">
                            รายการบทเรียน
                        </h3>
                        <p class="text-sm text-gray-500"
                           data-i18n-th="เพิ่มบทเรียน พร้อมกำหนดหมวดหมู่และจำนวนชั่วโมง"
                           data-i18n-en="Add lessons with category and hours">
                            เพิ่มบทเรียน พร้อมกำหนดหมวดหมู่และจำนวนชั่วโมง
                        </p>
                    </div>
                    <button type="button"
                            onclick="toggleForm('lessonForm')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                            data-i18n-th="เพิ่มบทเรียน"
                            data-i18n-en="Add lesson">เพิ่มบทเรียน</button>
                </div>

                <div class="space-y-4">
                    @forelse($lessonsByTerm as $lesson)
                        @php($lessonId = $lesson['id'] ?? null)

                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $lesson['title'] }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $lesson['category'] }} : {{ $lesson['hours'] }} ชั่วโมง
                                    </p>

                                    @if(!empty($lesson['details']))
                                        <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                                    @endif

                                    @if(!empty($lesson['created_at']))
                                        <p class="text-xs text-gray-400 mt-1">
                                            เพิ่มเมื่อ :
                                            {{ \Illuminate\Support\Carbon::parse($lesson['created_at'])->timezone('Asia/Bangkok')->addYears(543)->locale('th')->isoFormat('D MMM YYYY HH:mm') }}
                                        </p>
                                    @endif
                                </div>

                                {{-- ปุ่มแก้ไข/ลบ --}}
                                <div class="flex gap-3 text-sm">
                                    <button class="text-blue-600 hover:underline"
                                            onclick="toggleEditForm('lesson-edit-{{ $lessonId }}')">แก้ไข</button>

                                    <form method="POST"
                                            action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lessonId]) }}"
                                            onsubmit="return confirm('ต้องการลบบทเรียนนี้หรือไม่?')">

                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">ลบ</button>
                                    </form>
                                </div>
                            </div>

                            {{-- ฟอร์มแก้ไขบทเรียน --}}
                            <form id="lesson-edit-{{ $lessonId }}"
                                    method="POST"
                                    action="{{ route('teacher.courses.lessons.update', ['course' => $course, 'lesson' => $lessonId]) }}"
                                    class="hidden mt-3 space-y-3">

                                @csrf
                                @method('PUT')

                                <input type="hidden" name="term" value="{{ $currentTerm }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <select name="category" class="border rounded-xl px-3 py-2">
                                        @foreach($lessonCapacity ?? [] as $cat => $info)
                                            <option value="{{ $cat }}" @selected($lesson['category'] === $cat)>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="text" name="title" class="border rounded-xl px-3 py-2"
                                            value="{{ $lesson['title'] }}" required>

                                    <input type="number" step="1" min="1" name="hours"
                                            class="border rounded-xl px-3 py-2"
                                            value="{{ $lesson['hours'] }}" required>
                                </div>

                                <textarea name="details"
                                            class="border rounded-xl px-3 py-2 w-full"
                                            rows="2">{{ $lesson['details'] }}</textarea>

                                <div class="text-right">
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                </div>

                            </form>
                        </div>

                    @empty
                        <p class="text-gray-500 text-sm text-center"
                           data-i18n-th="ยังไม่มีบทเรียนสำหรับหลักสูตรนี้"
                           data-i18n-en="No lessons for this course yet">
                            ยังไม่มีบทเรียนสำหรับหลักสูตรนี้
                        </p>
                    @endforelse
                </div>


                {{-- ฟอร์มเพิ่มบทเรียน --}}
                @if(!empty($lessonCapacity))
                    <form id="lessonForm"
                            method="POST"
                            action="{{ route('teacher.courses.lessons.store', $course) }}"
                            class="hidden mt-6 space-y-4">

                        @csrf
                        <input type="hidden" name="term" value="{{ $currentTerm }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <select name="category"
                                    id="lessonCategory"
                                    class="border rounded-xl px-3 py-2"
                                    required>
                                <option value="">เลือกหมวดหมู่</option>
                                @foreach($lessonCapacity as $cat => $info)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>

                            <input type="text" name="title"
                                    class="border rounded-xl px-3 py-2"
                                    placeholder="หัวข้อบทเรียน" required>

                            <input type="number"
                                    id="lessonHours"
                                    name="hours"
                                    step="1"
                                    min="1"
                                    class="border rounded-xl px-3 py-2"
                                    placeholder="จำนวนชั่วโมง" required>
                        </div>

                        <div class="text-sm text-gray-600" id="lessonRemaining">
                            @foreach($lessonCapacity as $cat => $info)
                                <span class="inline-flex items-center px-3 py-1 bg-gray-100 rounded-xl mr-2"
                                        data-remaining="{{ $cat }}">
                                    {{ $cat }} เหลือได้อีก {{ number_format($info['remaining'], 0) }} ชม.
                                </span>
                            @endforeach
                        </div>

                        <textarea name="details"
                                    rows="3"
                                    class="border rounded-xl px-3 py-2 w-full"
                                    placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>

                        <div class="text-right">
                            <button class="px-5 py-2 bg-blue-600 text-white rounded-xl">บันทึกบทเรียน</button>
                        </div>

                    </form>
                @endif

            </section>

            {{-- ?? งาน / คะแนนเก็บ --}}
            <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 mb-20">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900"
                            data-i18n-th="งาน / คะแนนเก็บ"
                            data-i18n-en="Assignments / Scores">
                            งาน / คะแนนเก็บ
                        </h3>
                        <p class="text-sm text-gray-500"
                           data-i18n-th="กำหนดงาน คะแนนเต็ม และสัดส่วนคะแนนเก็บรวมของภาคเรียนนี้ (ไม่เกิน 70 คะแนน)"
                           data-i18n-en="Set assignments, full marks, and score proportions for this term (max 70 points)">
                            กำหนดงาน คะแนนเต็ม และสัดส่วนคะแนนเก็บรวมของภาคเรียนนี้ (ไม่เกิน 70 คะแนน)
                        </p>

                        <div class="mt-2 text-sm">
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-xl">
                                รวม: {{ $assignmentTotal ?? 0 }} / 70
                            </span>
                            <span class="bg-green-50 text-green-700 px-2 py-1 rounded-xl ml-2">
                                เหลือให้กำหนด: {{ $assignmentRemaining ?? 70 }}
                            </span>
                        </div>

                    </div>

                    <button onclick="toggleForm('assignmentForm')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl"
                            data-i18n-th="เพิ่มงาน/คะแนนเก็บ"
                            data-i18n-en="Add assignment / score">
                        เพิ่มงาน/คะแนนเก็บ
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($assignmentsByTerm as $assignment)
                        @php($assignmentId = $assignment['id'])
                        <div class="border border-gray-100 rounded-2xl p-4">

                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $assignment['title'] }}</p>
                                    <p class="text-sm text-gray-600">
                                        คะแนนเต็ม: {{ $assignment['score'] }}

                                        @if($assignment['due_date'])
                                            <span class="mx-1 text-gray-400">|</span>
                                            ส่งภายใน:
                                            {{ \Illuminate\Support\Carbon::parse($assignment['due_date'])->timezone('Asia/Bangkok')->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}
                                        @endif
                                    </p>

                                    @if(!empty($assignment['notes']))
                                        <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                                    @endif

                                    @if(!empty($assignment['created_at']))
                                        <p class="text-xs text-gray-400 mt-1">
                                            เพิ่มเมื่อ
                                            {{ \Illuminate\Support\Carbon::parse($assignment['created_at'])->timezone('Asia/Bangkok')->addYears(543)->locale('th')->isoFormat('D MMM YYYY HH:mm') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-4 text-sm">
                                    <button class="text-blue-600 hover:underline"
                                            onclick="toggleEditForm('assignment-edit-{{ $assignmentId }}')">แก้ไข</button>

                                    <form method="POST"
                                            action="{{ route('teacher.courses.assignments.destroy', ['course' => $course, 'assignment' => $assignmentId]) }}"
                                            onsubmit="return confirm('ต้องการลบงานนี้หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">ลบ</button>
                                    </form>
                                </div>
                            </div>

                            {{-- ฟอร์มแก้ไขงาน --}}
                            <form id="assignment-edit-{{ $assignmentId }}"
                                    method="POST"
                                    action="{{ route('teacher.courses.assignments.update', ['course' => $course, 'assignment' => $assignmentId]) }}"
                                    class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">

                                @csrf
                                @method('PUT')

                                <input type="hidden" name="term" value="{{ $currentTerm }}">

                                <select name="title" class="border rounded-xl px-3 py-2">
                                    @foreach($lessonTitles as $title)
                                        <option value="{{ $title }}" @selected($assignment['title'] === $title)>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>

                                <textarea name="notes"
                                            class="border rounded-xl px-3 py-2 md:col-span-2"
                                            rows="1"
                                            placeholder="รายละเอียดงาน">{{ $assignment['notes'] }}</textarea>

                                <input type="date" name="due_date" class="border rounded-xl px-3 py-2"
                                        value="{{ $assignment['due_date'] }}"
                                        min="{{ $todayDate }}">

                                <input type="number" step="0.1" name="score"
                                        class="border rounded-xl px-3 py-2"
                                        value="{{ $assignment['score'] }}" required>

                                <div class="md:col-span-4 text-right">
                                    <button class="px-5 py-2 bg-blue-600 text-white rounded-xl">บันทึกการแก้ไข</button>
                                </div>
                            </form>

                        </div>
                    @empty
                        <p class="text-gray-500 text-center text-sm mt-4"
                           data-i18n-th="ยังไม่มีงานหรือคะแนนเก็บสำหรับภาคเรียนนี้"
                           data-i18n-en="No assignments or scores for this term yet">
                            ยังไม่มีงานหรือคะแนนเก็บสำหรับภาคเรียนนี้
                        </p>
                    @endforelse
                </div>

                {{-- ฟอร์มเพิ่มงาน --}}
                <form id="assignmentForm"
                        method="POST"
                        action="{{ route('teacher.courses.assignments.store', $course) }}"
                        class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">

                    @csrf
                    <input type="hidden" name="term" value="{{ $currentTerm }}">

                    <select name="title" class="border rounded-xl px-3 py-2" required>
                        <option value=""
                                data-i18n-th="เลือกบทเรียน"
                                data-i18n-en="Select lesson">เลือกบทเรียน</option>
                        @foreach($lessonTitles as $title)
                            <option value="{{ $title }}">{{ $title }}</option>
                        @endforeach
                    </select>

                    <textarea name="notes" class="border rounded-xl px-3 py-2 md:col-span-2" rows="1"
                                placeholder="รายละเอียดงาน"></textarea>

                    <input type="date" name="due_date" class="border rounded-xl px-3 py-2" min="{{ $todayDate }}">

                    <input type="number" step="0.1" name="score"
                            class="border rounded-xl px-3 py-2"
                            placeholder="คะแนนเต็ม" required>

                    <div class="md:col-span-4 text-right">
                        <button class="px-5 py-2 bg-blue-600 text-white rounded-xl">บันทึกงาน / คะแนนเก็บ</button>
                    </div>

                </form>

            </section>

        @endif {{-- END: if currentTerm --}}
    @endunless
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // เปลี่ยนหลักสูตร
        const courseSelector = document.getElementById('courseSelector');
        if (courseSelector) {
            courseSelector.addEventListener('change', e => {
                if (e.target.value) window.location.href = e.target.value;
            });
        }

        // คำนวณชั่วโมงที่เหลือ
        const capacity = @json($lessonCapacity ?? []);
        const categorySelect = document.getElementById('lessonCategory');
        const hoursInput = document.getElementById('lessonHours');
        const remainingDisplay = document.getElementById('lessonRemaining');

        const refreshRemaining = () => {
            if (!categorySelect || !hoursInput) return;
            const cat = categorySelect.value;
            const info = capacity[cat];

            if (info) {
                const maxRemaining = Math.max(0, Math.floor(Number(info.remaining ?? 0)));
                hoursInput.max = maxRemaining || '';
                hoursInput.placeholder = `จำนวนชั่วโมง (ไม่เกิน ${maxRemaining})`;
                hoursInput.disabled = maxRemaining <= 0;
            } else {
                hoursInput.placeholder = "จำนวนชั่วโมง";
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

    });

    function toggleForm(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden');
    }

    function toggleEditForm(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden');
    }
</script>

@endsection