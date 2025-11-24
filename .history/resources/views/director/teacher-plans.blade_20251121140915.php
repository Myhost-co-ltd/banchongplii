@extends('layouts.layout-director')

@section('title', 'แผนการสอนของครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    {{-- INFO STAT --}}
    <div class="flex flex-wrap gap-4">
        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-800 px-4 py-2 rounded-2xl border border-blue-100">
            <span class="text-sm text-blue-600">จำนวนหลักสูตร</span>
            <span class="text-2xl font-semibold">{{ $courses->count() }}</span>
        </div>
        <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-800 px-4 py-2 rounded-2xl border border-indigo-100">
            <span class="text-sm text-indigo-600">จำนวนครูผู้สอน</span>
            <span class="text-2xl font-semibold">{{ $teacherCount }}</span>
        </div>
    </div>

    {{-- FILTER AREA --}}
    <div class="flex flex-col gap-3">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">แผนการสอนของครู</h2>
            <p class="text-gray-600 mt-1">เลือกหลักสูตรก่อน แล้วเลือกชั้น เพื่อดูว่าใครเป็นผู้สอน</p>
        </div>

        <form method="GET" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

            <div class="flex flex-wrap items-center gap-3">

                {{-- เลือกหลักสูตร --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="course" class="text-sm text-gray-600 whitespace-nowrap">หลักสูตร</label>
                    <select id="course" name="course"
                        class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                        onchange="this.form.submit()">
                        <option value="">เลือกหลักสูตร</option>
                        @foreach ($courseOptions as $course)
                            <option value="{{ $course }}" {{ $course === $selectedCourse ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- เลือกชั้น --}}
                <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="grade" class="text-sm text-gray-600 whitespace-nowrap">ชั้น</label>
                    <select id="grade" name="grade"
                        class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                        {{ empty($gradeOptions) ? 'disabled' : '' }}
                        onchange="this.form.submit()">
                        @if (empty($gradeOptions))
                            <option value="">เลือกหลักสูตรก่อน</option>
                        @else
                            <option value="">ทุกชั้น</option>
                            @foreach ($gradeOptions as $grade)
                                <option value="{{ $grade }}" {{ $grade === $selectedGrade ? 'selected' : '' }}>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- ค้นหา --}}
            <div class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                <label for="q" class="text-sm text-gray-600 whitespace-nowrap">ค้นหา</label>

                <input id="q" name="q" value="{{ $search }}"
                    placeholder="ชื่อหลักสูตร / ชื่อครู / ชั้น"
                    class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm w-56"
                    oninput="handleSearchInput(this)" />

                @if ($search !== '')
                    <button type="button"
                            class="text-xs text-gray-500 hover:text-gray-700"
                            onclick="document.getElementById('q').value=''; this.form.submit();">
                        ล้าง
                    </button>
                @endif
            </div>

        </form>
    </div>

    {{-- RESULTS --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse ($courses as $course)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">หลักสูตร</p>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $course->name }}</h3>
                        </div>

                        <p class="text-sm text-gray-600">
                            ระดับ {{ $course->grade ?? '-' }}
                            @if ($course->term) | เทอม {{ $course->term }} @endif
                            @if ($course->year) | ปี {{ $course->year }} @endif
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

                        @if ($course->description)
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $course->description }}</p>
                        @endif
                    </div>

                    <div class="text-left sm:text-right space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-500">ครูผู้สอน</p>
                        <p class="font-semibold text-gray-900">
                            {{ optional($course->teacher)->name ?? 'ยังไม่มีครูผู้สอน' }}
                        </p>
                        @if (optional($course->teacher)->email)
                            <p class="text-sm text-gray-600">
                                {{ $course->teacher->email }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                ไม่พบหลักสูตรสำหรับชั้นเรียนนี้
            </div>
        @endforelse
    </div>
</div>

{{-- AUTO-SEARCH SCRIPT --}}
<script>
    let searchTimer = null;

    function handleSearchInput(input) {
        if (searchTimer) clearTimeout(searchTimer);

        // Auto search 400ms หลังหยุดพิมพ์
        searchTimer = setTimeout(function () {
            input.form.submit();
        }, 400);
    }
</script>

@endsection
