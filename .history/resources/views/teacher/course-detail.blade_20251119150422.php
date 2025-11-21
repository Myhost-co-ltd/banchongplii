@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร | แดชบอร์ดครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-6">

    {{-- กล่องหัวข้อด้านบน --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <p class="text-sm text-slate-500 uppercase tracking-wide">รายละเอียดหลักสูตร</p>
        <h2 class="text-3xl font-bold text-gray-900 mt-1">รายละเอียดหลักสูตร</h2>
        <p class="text-gray-600 mt-1">
            ดูรายละเอียดของหลักสูตรที่ครูกำลังสอนและจัดการข้อมูลที่เกี่ยวข้องทั้งหมด
        </p>
    </div>

    {{-- กล่องรายละเอียดหลักสูตร --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        {{-- แถวบน: ข้อความ "ข้อมูลหลักสูตร" + ปุ่มด้านขวา --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">ข้อมูลหลักสูตร</h1>

            <div class="flex gap-3">
                <a href="{{ route('teacher.courses.edit', $course) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl">
                    แก้ไขข้อมูลหลักสูตร
                </a>
            </div>
        </div>

        {{-- เนื้อหาหลักของข้อมูลหลักสูตร --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                <p class="font-semibold text-gray-800 text-lg">{{ $course->name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                <div class="flex flex-wrap gap-2 mt-1">
                    @forelse ($course->rooms ?? [] as $room)
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">
                            {{ $room }}
                        </span>
                    @empty
                        <span class="text-gray-400 text-sm">ยังไม่มีการระบุห้องเรียน</span>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500">ภาคเรียน</p>
                <p class="font-semibold text-gray-800">
                    {{ $course->term ? 'ภาคเรียนที่ '.$course->term : '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="font-semibold text-gray-800">{{ $course->year ?? '-' }}</p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                <p class="text-gray-700 mt-1 leading-relaxed">
                    {{ $course->description ?? '-' }}
                </p>
            </div>

        </div>
    </div>

    @php
        $teachingHours = $course->teaching_hours ?? [];
        $lessons       = $course->lessons ?? [];
        $assignments   = $course->assignments ?? [];
    @endphp

    {{-- ========================= --}}
    {{-- 1) ชั่วโมงที่สอน (ภาพรวม) --}}
    {{-- ========================= --}}
    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">ชั่วโมงที่สอน (ภาพรวม)</h3>
                <p class="text-gray-500 text-sm">
                    บันทึกชั่วโมงการสอนโดยรวมในหลักสูตรนี้
                </p>
            </div>

            {{-- ปุ่มเปิด/ปิดฟอร์ม มุมขวาบน --}}
            <button type="button"
                    onclick="toggleForm('hours-form')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มชั่วโมง
            </button>
        </div>

        <div class="space-y-4">
            @forelse ($teachingHours as $hour)
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border rounded-2xl p-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $hour['category'] ?? 'หัวข้อ' }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? 'ชั่วโมง' }}
                        </p>
                        @if (!empty($hour['note']))
                            <p class="text-sm text-gray-500 mt-1">{{ $hour['note'] }}</p>
                        @endif
                    </div>
                    <form method="POST"
                          action="{{ route('teacher.courses.hours.destroy', ['course' => $course, 'hour' => $hour['id'] ?? '']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">
                            ลบ
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-center text-gray-500 text-sm">
                    ยังไม่มีการบันทึกชั่วโมงสอนในหลักสูตรนี้
                </p>
            @endforelse
        </div>

        {{-- ฟอร์มเพิ่มชั่วโมง (ซ่อน/โชว์ด้วยปุ่มด้านบน) --}}
        <div id="hours-form" class="mt-6 hidden">
            <form method="POST"
                  action="{{ route('teacher.courses.hours.store', $course) }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <input type="text" name="category"
                       class="border rounded-xl px-3 py-2"
                       placeholder="หัวข้อ" required>
                <input type="number" step="0.1" min="0" name="hours"
                       class="border rounded-xl px-3 py-2"
                       placeholder="ชั่วโมง" required>
                <select name="unit" class="border rounded-xl px-3 py-2" required>
                    <option value="">เลือกหน่วย</option>
                    <option value="ชั่วโมง/สัปดาห์">ชั่วโมง/สัปดาห์</option>
                    <option value="ชั่วโมง/ภาคเรียน">ชั่วโมง/ภาคเรียน</option>
                </select>
                <input type="text" name="note"
                       class="border rounded-xl px-3 py-2"
                       placeholder="หมายเหตุ (ถ้ามี)">
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl">
                        ✔ บันทึกชั่วโมง
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- ================================= --}}
    {{-- 2) เนื้อหาที่สอน + ระยะเวลา --}}
    {{-- ================================= --}}
    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">เนื้อหาที่สอน + ระยะเวลา</h3>
            </div>

            {{-- ปุ่มเปิด/ปิดฟอร์ม มุมขวาบน --}}
            <button type="button"
                    onclick="toggleForm('lessons-form')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มหัวข้อ
            </button>
        </div>

        <div class="space-y-4">
            @forelse ($lessons as $lesson)
                <div class="border rounded-2xl p-4 space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $lesson['title'] ?? 'หัวข้อบทเรียน' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                ใช้เวลา {{ $lesson['hours'] ?? '-' }} ชั่วโมง —
                                ช่วงเวลา {{ $lesson['period'] ?? '-' }}
                            </p>
                            @if (!empty($lesson['details']))
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $lesson['details'] }}
                                </p>
                            @endif
                        </div>
                        <form method="POST"
                              action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lesson['id'] ?? '']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 text-sm font-semibold hover:underline">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 text-sm">
                    ยังไม่มีการบันทึกเนื้อหาที่สอน
                </p>
            @endforelse
        </div>

        {{-- ฟอร์มเพิ่มเนื้อหา --}}
        <div id="lessons-form" class="mt-6 hidden">
            <form method="POST"
                  action="{{ route('teacher.courses.lessons.store', $course) }}"
                  class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="title"
                           class="border rounded-xl px-3 py-2"
                           placeholder="หัวข้อบทเรียน" required>
                    <input type="number" step="0.1" min="0" name="hours"
                           class="border rounded-xl px-3 py-2"
                           placeholder="ชั่วโมง" required>
                    <input type="text" name="period"
                           class="border rounded-xl px-3 py-2"
                           placeholder="ช่วงเวลา" required>
                </div>
                <textarea name="details" rows="3"
                          class="w-full border rounded-xl px-3 py-2"
                          placeholder="รายละเอียด (ถ้ามี)"></textarea>
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl">
                        ✔ บันทึกหัวข้อ
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- ============================ --}}
    {{-- 3) การบ้าน / ชิ้นงาน --}}
    {{-- ============================ --}}
    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">การบ้าน / ชิ้นงาน</h3>

            {{-- ปุ่มเปิด/ปิดฟอร์ม มุมขวาบน --}}
            <button type="button"
                    onclick="toggleForm('assignments-form')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มการบ้าน
            </button>
        </div>

        <div class="space-y-4">
            @forelse ($assignments as $assignment)
                <div class="border rounded-2xl p-4 space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $assignment['title'] ?? 'การบ้าน' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                กำหนดส่ง:
                                {{ !empty($assignment['due_date'])
                                    ? \Carbon\Carbon::parse($assignment['due_date'])->translatedFormat('j F Y')
                                    : '-' }}
                                | คะแนนเต็ม: {{ $assignment['score'] ?? '-' }}
                            </p>
                            @if (!empty($assignment['notes']))
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $assignment['notes'] }}
                                </p>
                            @endif
                        </div>
                        <form method="POST"
                              action="{{ route('teacher.courses.assignments.destroy', ['course' => $course, 'assignment' => $assignment['id'] ?? '']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 text-sm font-semibold hover:underline">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 text-sm">
                    ยังไม่มีการบ้านหรือชิ้นงาน
                </p>
            @endforelse
        </div>

        {{-- ฟอร์มเพิ่มการบ้าน --}}
        <div id="assignments-form" class="mt-6 hidden">
            <form method="POST"
                  action="{{ route('teacher.courses.assignments.store', $course) }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <input type="text" name="title"
                       class="border rounded-xl px-3 py-2"
                       placeholder="ชื่อการบ้าน" required>
                <input type="date" name="due_date"
                       class="border rounded-xl px-3 py-2">
                <input type="number" step="0.5" min="0" name="score"
                       class="border rounded-xl px-3 py-2"
                       placeholder="คะแนนเต็ม">
                <input type="text" name="notes"
                       class="border rounded-xl px-3 py-2 md:col-span-2"
                       placeholder="หมายเหตุ (ถ้ามี)">
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl">
                        ✔ บันทึกการบ้าน
                    </button>
                </div>
            </form>
        </div>
    </section>

</div>

{{-- สคริปต์เล็ก ๆ สำหรับ toggle ฟอร์ม --}}
<script>
    function toggleForm(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('hidden');
        // เลื่อนหน้าไปให้เห็นฟอร์มเวลากดเปิด
        if (!el.classList.contains('hidden')) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>
@endsection
