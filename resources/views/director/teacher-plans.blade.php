@extends('layouts.layout-director')

@section('title', 'แผนการสอนของครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <div>
        <h2 class="text-3xl font-bold text-gray-900">แผนการสอนของครู</h2>
        <p class="text-gray-600 mt-1">
            เลือกหลักสูตรก่อน แล้วเลือกชั้น เพื่อดูว่าใครเป็นผู้สอน
        </p>
    </div>

    {{-- SUMMARY BOXES แบบการ์ดใหญ่ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- จำนวนหลักสูตร --}}
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-blue-700">จำนวนหลักสูตรทั้งหมด</p>
            <p class="mt-4 text-4xl font-bold text-blue-900">
                {{ $courses->count() }}
            </p>
        </div>

        {{-- จำนวนครูผู้สอน --}}
        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border border-emerald-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-emerald-700">จำนวนครูผู้สอนทั้งหมด</p>
            <p class="mt-4 text-4xl font-bold text-emerald-900">
                {{ $teacherCount }}
            </p>
        </div>
    </div>

    {{-- HEADER + FILTERS --}}
    <div class="space-y-4">

        <form id="teacherPlanFilter" method="GET"
              class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

            {{-- LEFT: หลักสูตร + ชั้น --}}
            <div class="flex flex-wrap items-center gap-3">

                {{-- หลักสูตร --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="course" class="text-sm text-gray-600 whitespace-nowrap">หลักสูตร</label>
                    <select id="course"
                            name="course"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            onchange="this.form.submit()">
                        <option value="">เลือกหลักสูตร</option>
                        @foreach ($courseOptions as $course)
                            <option value="{{ $course }}"
                                    {{ $course === $selectedCourse ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ชั้น --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="grade" class="text-sm text-gray-600 whitespace-nowrap">ชั้น</label>
                    <select id="grade"
                            name="grade"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            {{ empty($gradeOptions) ? 'disabled' : '' }}
                            onchange="this.form.submit()">
                        @if (empty($gradeOptions))
                            <option value="">เลือกหลักสูตรก่อน</option>
                        @else
                            <option value="">ทุกชั้น</option>
                            @foreach ($gradeOptions as $grade)
                                <option value="{{ $grade }}"
                                        {{ $grade === $selectedGrade ? 'selected' : '' }}>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- RIGHT: ค้นหา --}}
            <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                <label for="q" class="text-sm text-gray-600 whitespace-nowrap">ค้นหา</label>
                <input id="q"
                       name="q"
                       value="{{ $search }}"
                       placeholder="ชื่อหลักสูตร / ชื่อครู / ชั้น"
                       class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm w-56" />
                <button type="submit"
                        class="text-xs px-3 py-1 rounded-xl bg-blue-600 text-white hover:bg-blue-500">
                    ค้นหา
                </button>
                @if ($search !== '')
                    <button type="button"
                            class="text-xs text-gray-500 hover:text-gray-700"
                            onclick="document.getElementById('q').value=''; document.getElementById('teacherPlanFilter').submit();">
                        ล้าง
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- LIST COURSES --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse ($courses as $course)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    {{-- ซ้าย: ข้อมูลหลักสูตร --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">หลักสูตร</p>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $course->name }}</h3>
                                @php
                                    $hasHours = !empty($course->teaching_hours);
                                    $hasAssignments = !empty($course->assignments);
                                    $isComplete = $hasHours && $hasAssignments;
                                @endphp
                                @if($isComplete)
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200" title="เพิ่มเนื้อหาและสั่งงานครบแล้ว">
                                        ✅ ครบชั่วโมง/งาน
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-200" title="ยังไม่ครบทั้งชั่วโมงสอนและงาน">
                                        ⏳ รอตรวจสอบ
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-600">
                            ระดับ {{ $course->grade ?? '-' }}
                            @if (! empty($course->term))
                                | เทอม {{ $course->term }}
                            @endif
                            @if (! empty($course->year))
                                | ปี {{ $course->year }}
                            @endif
                        </p>

                        @if (! empty($course->rooms))
                            <div class="flex flex-wrap gap-2">
                                @foreach ($course->rooms as $room)
                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                        {{ $room }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if (! empty($course->description))
                            <p class="text-sm text-gray-700 leading-relaxed">
                                {{ $course->description }}
                            </p>
                        @endif
                    </div>

                    {{-- ขวา: ครูผู้สอน + ปุ่มดูรายละเอียด --}}
                    <div class="text-left sm:text-right space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">ครูผู้สอน</p>
                            <p class="font-semibold text-gray-900">
                                {{ optional($course->teacher)->name ?? 'ยังไม่มีครูผู้สอน' }}
                            </p>
                            @if (! empty(optional($course->teacher)->email))
                                <p class="text-sm text-gray-600">
                                    {{ $course->teacher->email }}
                                </p>
                            @endif
                            @if ($isComplete)
                                <p class="text-xs text-emerald-600">ครูคนนี้เพิ่มเนื้อหาครบชั่วโมงและสั่งงานครบแล้ว</p>
                            @endif
                        </div>

                        {{-- ปุ่มดูรายละเอียดหลักสูตรของครูที่สร้าง --}}
                        <a href="{{ route('director.course-detail', $course) }}"
                           class="inline-flex items-center justify-center px-4 py-2 text-sm rounded-xl bg-blue-600 text-white hover:bg-blue-500">
                            รายละเอียด
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                ไม่พบหลักสูตรสำหรับเงื่อนไขที่เลือก
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('teacherPlanFilter');
        const searchInput = document.getElementById('q');
        let debounceTimer;

        if (searchInput && form) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), 300);
            });
        }
    });
</script>
@endsection
