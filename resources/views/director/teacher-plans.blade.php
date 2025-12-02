@extends('layouts.layout-director')

@section('title', 'แผนการสอนของครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <div>
        <h2 class="text-3xl font-bold text-gray-900"
            data-i18n-th="แผนการสอนของครู" data-i18n-en="Teacher Plans">
            แผนการสอนของครู
        </h2>
        <p class="text-gray-600 mt-1"
           data-i18n-th="เลือกหลักสูตรก่อน แล้วเลือกชั้น เพื่อดูว่าใครเป็นผู้สอน"
           data-i18n-en="Pick a course, then a grade to see who teaches it">
            เลือกหลักสูตรก่อน แล้วเลือกชั้น เพื่อดูว่าใครเป็นผู้สอน
        </p>
    </div>

    {{-- SUMMARY BOXES แบบการ์ดใหญ่ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        {{-- จำนวนหลักสูตร --}}
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-blue-700"
               data-i18n-th="จำนวนหลักสูตรทั้งหมด" data-i18n-en="Total courses">
                จำนวนหลักสูตรทั้งหมด
            </p>
            <p class="mt-4 text-4xl font-bold text-blue-900">
                {{ $courses->count() }}
            </p>
        </div>

        {{-- จำนวนครูผู้สอน --}}
        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border border-emerald-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-emerald-700"
               data-i18n-th="จำนวนครูผู้สอนทั้งหมด" data-i18n-en="Total teachers">
                จำนวนครูผู้สอนทั้งหมด
            </p>
            <p class="mt-4 text-4xl font-bold text-emerald-900">
                {{ $teacherCount }}
            </p>
        </div>

        {{-- ครูที่ครบชั่วโมง/งาน --}}
        <div class="bg-gradient-to-r from-sky-50 to-sky-100 border border-sky-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-sky-700"
               data-i18n-th="ครูที่ครบชั่วโมง/งาน" data-i18n-en="Teachers complete">
                ครูที่ครบชั่วโมง/งาน
            </p>
            <p class="mt-4 text-4xl font-bold text-sky-900">
                {{ $completeTeacherCount }}
            </p>
        </div>

        {{-- ครูที่ยังไม่ครบ --}}
        <div class="bg-gradient-to-r from-amber-50 to-amber-100 border border-amber-100 rounded-3xl shadow-md px-8 py-6">
            <p class="text-sm font-medium text-amber-700"
               data-i18n-th="ครูที่ยังไม่ครบ" data-i18n-en="Teachers incomplete">
                ครูที่ยังไม่ครบ
            </p>
            <p class="mt-4 text-4xl font-bold text-amber-900">
                {{ $incompleteTeacherCount }}
            </p>
        </div>
    </div>

    {{-- HEADER + FILTERS --}}
    <div class="space-y-4">

        <form id="teacherPlanFilter" method="GET"
            class="flex flex-col lg:flex-row lg:items-center lg:justify-start gap-3">

            {{-- LEFT: หลักสูตร + ชั้น (คงไว้) --}}
            <div class="flex flex-wrap items-center gap-3">

                {{-- หลักสูตร --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="course" class="text-sm text-gray-600 whitespace-nowrap"
                           data-i18n-th="หลักสูตร" data-i18n-en="Course">หลักสูตร</label>
                    <select id="course"
                            name="course"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="" data-i18n-th="เลือกหลักสูตร" data-i18n-en="Select course">เลือกหลักสูตร</option>
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
                    <label for="grade" class="text-sm text-gray-600 whitespace-nowrap"
                           data-i18n-th="ชั้น" data-i18n-en="Grade">ชั้น</label>
                    <select id="grade"
                            name="grade"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            {{ empty($gradeOptions) ? 'disabled' : '' }}>
                        @if (empty($gradeOptions))
                            <option value="" data-i18n-th="เลือกหลักสูตรก่อน" data-i18n-en="Select a course first">เลือกหลักสูตรก่อน</option>
                        @else
                            <option value="" data-i18n-th="ทุกชั้น" data-i18n-en="All grades">ทุกชั้น</option>
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

            {{-- ค้นหาหลักสูตร / ครู --}}
            <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                <label for="search" class="text-sm text-gray-600 whitespace-nowrap"
                       data-i18n-th="ค้นหา" data-i18n-en="Search">ค้นหา</label>
                <input id="search"
                       type="text"
                       class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="ชื่อหลักสูตรหรือครู"
                       data-i18n-placeholder-th="ชื่อหลักสูตรหรือครู"
                       data-i18n-placeholder-en="Course or teacher name">
            </div>
            
            {{-- เพิ่มปุ่ม Submit เพื่อให้ Dropdown ยังทำงานได้หากไม่มี JS --}}
            <button type="submit" hidden></button> 

        </form>
    </div>

    {{-- LIST COURSES --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse ($courses as $course)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm course-card"
                 data-course="{{ mb_strtolower($course->name ?? '') }}"
                 data-teacher="{{ mb_strtolower(optional($course->teacher)->name ?? '') }}">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    {{-- ซ้าย: ข้อมูลหลักสูตร --}}
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500"
                               data-i18n-th="หลักสูตร" data-i18n-en="Course">หลักสูตร</p>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $course->name }}</h3>
                                @php
                                    $hasHours = !empty($course->teaching_hours);
                                    $hasAssignments = !empty($course->assignments);
                                    $isComplete = $hasHours && $hasAssignments;
                                    $isMissingBoth = ! $hasHours && ! $hasAssignments;
                                @endphp
                                @if($isComplete)
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200"
                                          title="เพิ่มเนื้อหาและสั่งงานครบแล้ว"
                                          data-i18n-th="✅ ครบชั่วโมง/งาน" data-i18n-en="✅ Complete hours/tasks">
                                        ✅ ครบชั่วโมง/งาน
                                    </span>
                                @elseif($isMissingBoth)
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-rose-100 text-rose-700 border border-rose-200"
                                          title="ยังไม่ได้เพิ่มชั่วโมงสอนและงาน"
                                          data-i18n-th="⭕ ยังไม่ครบ" data-i18n-en="⭕ Not complete">
                                        ⭕ ยังไม่ครบ
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-200"
                                          title="ยังไม่ครบทั้งชั่วโมงสอนและงาน"
                                          data-i18n-th="⏳ รอตรวจสอบ" data-i18n-en="⏳ Pending">
                                        ⏳ รอตรวจสอบ
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-600">
                            <span data-i18n-th="ระดับ" data-i18n-en="Grade">ระดับ</span> {{ $course->grade ?? '-' }}
                            @if (! empty($course->term))
                                | <span data-i18n-th="เทอม" data-i18n-en="Term">เทอม</span> {{ $course->term }}
                            @endif
                            @if (! empty($course->year))
                                | <span data-i18n-th="ปี" data-i18n-en="Year">ปี</span> {{ $course->year }}
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
                            <p class="text-xs uppercase tracking-wide text-gray-500"
                               data-i18n-th="ครูผู้สอน" data-i18n-en="Teacher">ครูผู้สอน</p>
                            <p class="font-semibold text-gray-900">
                                {{ optional($course->teacher)->name ?? 'ยังไม่มีครูผู้สอน' }}
                            </p>
                            {{-- @if (! empty(optional($course->teacher)->email))
                                <p class="text-sm text-gray-600">
                                    {{ $course->teacher->email }}
                                </p>
                            @endif --}}
                            @if ($isComplete)
                                <p class="text-xs text-emerald-600"
                                   data-i18n-th="ครูคนนี้เพิ่มเนื้อหาครบชั่วโมงและสั่งงานครบแล้ว"
                                   data-i18n-en="This teacher added content and tasks completely">
                                    ครูคนนี้เพิ่มเนื้อหาครบชั่วโมงและสั่งงานครบแล้ว
                                </p>
                            @endif
                        </div>

                        {{-- ปุ่มดูรายละเอียดหลักสูตรของครูที่สร้าง --}}
                        <a href="{{ route('director.course-detail', $course) }}"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm rounded-xl bg-blue-600 text-white hover:bg-blue-500"
                            data-i18n-th="รายละเอียด" data-i18n-en="Details">
                            รายละเอียด
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                <span data-i18n-th="ไม่พบหลักสูตรสำหรับเงื่อนไขที่เลือก"
                      data-i18n-en="No courses match the selected filters">
                    ไม่พบหลักสูตรสำหรับเงื่อนไขที่เลือก
                </span>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('teacherPlanFilter');
        const courseSelect = document.getElementById('course');
        const gradeSelect = document.getElementById('grade');
        const searchInput = document.getElementById('search');
        const cards = Array.from(document.querySelectorAll('.course-card'));

        // 1. จัดการ Event สำหรับ Dropdown (Submit ทันที)
        if (courseSelect && form) {
            courseSelect.addEventListener('change', () => form.submit());
        }
        if (gradeSelect && form) {
            gradeSelect.addEventListener('change', () => form.submit());
        }

        // 2. ค้นหาทันทีจากบางตัวอักษร (ไม่มีปุ่ม)
        if (searchInput) {
            const filterCards = () => {
                const query = searchInput.value.trim().toLowerCase();
                cards.forEach(card => {
                    if (!query) {
                        card.classList.remove('hidden');
                        return;
                    }
                    const courseName = card.dataset.course || '';
                    const teacherName = card.dataset.teacher || '';
                    const match = courseName.includes(query) || teacherName.includes(query);
                    card.classList.toggle('hidden', !match);
                });
            };
            searchInput.addEventListener('input', filterCards);
        }
    });
</script>
@endsection
