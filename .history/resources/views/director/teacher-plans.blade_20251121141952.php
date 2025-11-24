@extends('layouts.layout-director')

@section('title', 'แผนการสอนของครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
    {{-- หัวข้อหน้า --}}
    <div class="flex flex-col gap-3">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">แผนการสอนของครู</h2>
            <p class="text-gray-600 mt-1">
                เลือกหลักสูตรก่อน แล้วเลือกชั้น เพื่อดูว่าใครเป็นผู้สอน
            </p>
        </div>

        {{-- สรุปจำนวน --}}
        <div class="flex flex-wrap gap-4">
            <div
                class="inline-flex items-center gap-2 bg-blue-50 text-blue-800 px-4 py-2 rounded-2xl border border-blue-100">
                <span class="text-sm text-blue-600">จำนวนหลักสูตร</span>
                <span class="text-2xl font-semibold">{{ $courses->count() }}</span>
            </div>
            <div
                class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-800 px-4 py-2 rounded-2xl border border-indigo-100">
                <span class="text-sm text-indigo-600">จำนวนครูผู้สอน</span>
                <span class="text-2xl font-semibold">{{ $teacherCount }}</span>
            </div>
        </div>

        {{-- ฟิลเตอร์หลักสูตร / ชั้น / ค้นหา --}}
        @php
            /** @var \Illuminate\Support\Collection $courseOptions */
            /** @var \Illuminate\Support\Collection $gradeOptions */
            $search = $search ?? request('q', '');
        @endphp

        <form method="GET"
              id="filterForm"
              class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

            {{-- ซ้าย: เลือกหลักสูตร / ชั้น --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- เลือกหลักสูตร --}}
                <div
                    class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="course" class="text-sm text-gray-600 whitespace-nowrap">หลักสูตร</label>
                    <select id="course" name="course"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="">เลือกหลักสูตร</option>
                        @foreach ($courseOptions as $c)
                            <option value="{{ $c }}"
                                {{ $c === $selectedCourse ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- เลือกชั้น --}}
                <div
                    class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                    <label for="grade" class="text-sm text-gray-600 whitespace-nowrap">ชั้น</label>
                    <select id="grade" name="grade"
                            class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                            {{ empty($gradeOptions) ? 'disabled' : '' }}
                            onchange="document.getElementById('filterForm').submit()">
                        @if (empty($gradeOptions))
                            <option value="">เลือกหลักสูตรก่อน</option>
                        @else
                            <option value="">ทุกชั้น</option>
                            @foreach ($gradeOptions as $g)
                                <option value="{{ $g }}"
                                    {{ $g === $selectedGrade ? 'selected' : '' }}>
                                    {{ $g }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- ขวา: กล่องค้นหา (ไม่รีหน้า, live filter) --}}
            <div
                class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm rounded-2xl px-4 py-2.5">
                <label for="q" class="text-sm text-gray-600 whitespace-nowrap">ค้นหา</label>

                <input id="q"
                       name="q"
                       value="{{ $search }}"
                       placeholder="ชื่อหลักสูตร / ชื่อครู / ชั้น"
                       class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm w-56" />

                {{-- ปุ่มค้นหาเปลี่ยนเป็น type="button" ไม่ submit --}}
                <button type="button"
                        id="searchTrigger"
                        class="px-3 py-1.5 text-xs rounded-xl bg-blue-600 text-white">
                    ค้นหา
                </button>

                @if ($search !== '')
                    <button type="button"
                            id="searchClear"
                            class="text-xs text-gray-500 hover:text-gray-700">
                        ล้าง
                    </button>
                @else
                    <button type="button"
                            id="searchClear"
                            class="text-xs text-gray-400 hover:text-gray-600">
                        ล้าง
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- รายการหลักสูตร --}}
    <div class="grid grid-cols-1 gap-4" id="courseList">
        @forelse ($courses as $course)
            @php
                $searchText = trim(
                    ($course->name ?? '') . ' ' .
                    ($course->grade ?? '') . ' ' .
                    (optional($course->teacher)->name ?? '')
                );
            @endphp

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
                 data-course-card="1"
                 data-search="{{ $searchText }}">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">หลักสูตร</p>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $course->name }}
                            </h3>
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
                                    <span
                                        class="px-3 py-1 text-xs bg-blue-50 text-blue-700 rounded-full border border-blue-100">
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

                    <div class="text-left sm:text-right space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-500">ครูผู้สอน</p>
                        <p class="font-semibold text-gray-900">
                            {{ optional($course->teacher)->name ?? 'ยังไม่มีครูผู้สอน' }}
                        </p>
                        @if (! empty(optional($course->teacher)->email))
                            <p class="text-sm text-gray-600">
                                {{ $course->teacher->email }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div
                class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                ไม่พบหลักสูตรสำหรับเงื่อนไขนี้
            </div>
        @endforelse
    </div>
</div>

{{-- JS: live filter ช่องค้นหา ไม่รีหน้า --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('q');
        const clearBtn = document.getElementById('searchClear');
        const triggerBtn = document.getElementById('searchTrigger');
        const cards = Array.from(document.querySelectorAll('[data-course-card]'));

        function filterCards() {
            const keyword = (searchInput.value || '').toLowerCase().trim();

            cards.forEach(card => {
                const text = (card.dataset.search || '').toLowerCase();
                const match = keyword === '' || text.includes(keyword);
                card.style.display = match ? '' : 'none';
            });
        }

        // กรองทุกครั้งที่พิมพ์ (ไม่ submit form)
        searchInput.addEventListener('input', filterCards);

        // กันไม่ให้กด Enter แล้วรีหน้า
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // ปุ่ม "ค้นหา" แค่เรียก filterCards เฉย ๆ
        triggerBtn.addEventListener('click', filterCards);

        // ปุ่ม "ล้าง"
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            filterCards();
        });

        // กรองตามค่าที่มาจากเซิร์ฟเวอร์ครั้งแรก (ถ้ามี q เดิม)
        filterCards();
    });
</script>
@endsection
