@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร | แดชบอร์ดครู')

@section('content')
@php($courseOptions = collect($courses ?? []))
<div class="space-y-8 overflow-y-auto pr-2 pb-8">

    {{-- การ์ดหัวข้อหลักสูตร + ปุ่มเลือก --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <p class="text-sm text-slate-500 uppercase tracking-wide">รายละเอียดหลักสูตร</p>
        <h2 class="text-3xl font-bold text-gray-900 mt-1">รายละเอียดหลักสูตร</h2>
        <p class="text-gray-600 mt-1">
            ดูรายละเอียดของหลักสูตรที่ครูกำลังสอนและจัดการข้อมูลที่เกี่ยวข้องทั้งหมด
        </p>

        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('teacher.course-create') }}"
               class="px-4 py-2 bg-gray-200 rounded-xl text-gray-700 text-sm">
                กลับไปหน้าสร้างหลักสูตร
            </a>

            {{-- ปุ่มแก้ไขหลักสูตร เฉพาะตอนที่มี $course --}}
            @if ($course)
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                    แก้ไขข้อมูลหลักสูตร
                </a>
            @endif
        </div>

        {{-- เลือกหลักสูตร --}}
        <div class="mt-6 border-t border-gray-100 pt-6">
            @if ($courseOptions->isNotEmpty())
                <label for="courseSelector" class="block text-sm font-semibold text-gray-700">
                    เลือกหลักสูตรที่ต้องการดู
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
                    ยังไม่มีหลักสูตรที่สร้างไว้ กรุณาสร้างหลักสูตรก่อนเพื่อดูรายละเอียด
                </p>
            @endif
        </div>
    </div>

    {{-- ถ้ามี course ให้แสดงรายละเอียด --}}
    @if ($course)

        @php
            $teachingHours = $course->teaching_hours ?? [];
            $lessons       = $course->lessons ?? [];
            $assignments   = $course->assignments ?? [];
        @endphp

        {{-- ข้อมูลองค์รวมหลักสูตร --}}
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
                            <span class="text-gray-400 text-sm">ยังไม่ได้กำหนดห้องเรียน</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500">ภาคเรียน</p>
                    <p class="font-semibold text-gray-900">
                        {{ $course->term ? 'ภาคเรียนที่ '.$course->term : '-' }}
                    </p>
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

        {{-- ชั่วโมงที่สอน --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">ชั่วโมงที่สอน (ภาพรวม)</h3>
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="text-sm text-blue-600 hover:underline">
                    จัดการข้อมูล
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($teachingHours as $hour)
                    <div class="border border-gray-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $hour['category'] ?? '-' }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? 'ชั่วโมง' }}
                            </p>
                            @if (!empty($hour['note']))
                                <p class="text-sm text-gray-500 mt-1">{{ $hour['note'] }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">
                        ยังไม่มีการบันทึกจำนวนชั่วโมงที่สอน
                    </p>
                @endforelse
            </div>
        </section>

        {{-- เนื้อหาที่สอน --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">เนื้อหาที่สอน + ระยะเวลา</h3>
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="text-sm text-blue-600 hover:underline">
                    จัดการบทเรียน
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($lessons as $lesson)
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <p class="font-semibold text-gray-900">{{ $lesson['title'] ?? '-' }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $lesson['hours'] ?? 0 }} ชั่วโมง / {{ $lesson['period'] ?? '-' }}
                        </p>
                        @if (!empty($lesson['details']))
                            <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">
                        ยังไม่มีหัวข้อบทเรียนที่บันทึกไว้
                    </p>
                @endforelse
            </div>
        </section>

        {{-- การบ้าน / แบบฝึกหัด --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 mb-10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">แบบฝึกหัด / การบ้าน</h3>
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="text-sm text-blue-600 hover:underline">
                    จัดการการบ้าน
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($assignments as $assignment)
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <p class="font-semibold text-gray-900">{{ $assignment['title'] ?? '-' }}</p>
                        <p class="text-sm text-gray-600 mt-1">
                            คะแนนรวม: {{ $assignment['score'] ?? '-' }}
                            @if (!empty($assignment['due_date']))
                                <span class="mx-2">|</span>
                                กำหนดส่ง:
                                {{ \Carbon\Carbon::parse($assignment['due_date'])->locale('th')->isoFormat('D MMM YYYY') }}
                            @endif
                        </p>
                        @if (!empty($assignment['notes']))
                            <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">
                        ยังไม่มีการมอบหมายงานสำหรับหลักสูตรนี้
                    </p>
                @endforelse
            </div>
        </section>

    @else
        {{-- กรณีไม่มี course --}}
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">
                ยังไม่มีหลักสูตรที่จะแสดง
            </h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                กรุณาเลือกหลักสูตรจากรายการด้านบน หรือสร้างหลักสูตรใหม่เพื่อเริ่มจัดการข้อมูลการสอน
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                เพิ่มหลักสูตรใหม่
            </a>
        </div>


</div>

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
    });
</script>
@endsection
