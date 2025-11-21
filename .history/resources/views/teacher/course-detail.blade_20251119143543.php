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

    {{-- ========================= --}}
    {{-- 1) ชั่วโมงที่สอน (ภาพรวม) --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">ชั่วโมงที่สอน (ภาพรวม)</h2>

            {{-- ปุ่มจัดการ/เพิ่มชั่วโมงสอน (ถ้ามีหน้าแก้ไขแยก สามารถใส่ route ได้) --}}
            <a href="#"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มชั่วโมง
            </a>
        </div>

        <div class="space-y-3">
            {{-- ตัวอย่าง loop ถ้าเฮียมี relation เช่น $course->teachingLoads --}}
            @forelse (($course->teachingLoads ?? []) as $load)
                <div class="flex items-center justify-between bg-slate-50 rounded-2xl px-4 py-3 text-sm">
                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ $load->type ?? 'ประเภทการสอน' }}
                        </p>
                        <p class="text-gray-500">
                            {{ $load->note ?? '' }}
                        </p>
                    </div>
                    <p class="text-gray-700">
                        {{ $load->hours_per_week ?? '0' }} ชั่วโมง/สัปดาห์
                    </p>
                </div>
            @empty
                <p class="text-gray-400 text-sm">
                    ยังไม่มีการบันทึกชั่วโมงสอนในหลักสูตรนี้
                </p>
            @endforelse
        </div>
    </div>

    {{-- ================================= --}}
    {{-- 2) เนื้อหาที่สอน + ระยะเวลา --}}
    {{-- ================================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">เนื้อหาที่สอน + ระยะเวลา</h2>

            <a href="#"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มหัวข้อ
            </a>
        </div>

        <div class="space-y-4">
            {{-- ตัวอย่าง loop ถ้าเฮียมี relation เช่น $course->topics --}}
            @forelse (($course->topics ?? []) as $topic)
                <div class="bg-slate-50 rounded-2xl px-5 py-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-semibold text-gray-800">
                                บทที่ {{ $topic->order ?? '-' }} : {{ $topic->title ?? 'ชื่อหัวข้อ' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                ใช้เวลา {{ $topic->hours ?? '0' }} ชั่วโมง —
                                ช่วงเวลา: {{ $topic->time_range ?? '-' }}
                            </p>
                        </div>

                        {{-- ปุ่มลบ/จัดการเพิ่มเติมถ้าต้องการ --}}
                        <button type="button" class="text-red-500 text-sm">
                            ลบ
                        </button>
                    </div>

                    @if (!empty($topic->details))
                        <div class="mt-2 text-sm text-gray-700 space-y-1">
                            @foreach(explode("\n", $topic->details) as $line)
                                <p>– {{ $line }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-gray-400 text-sm">
                    ยังไม่มีการบันทึกหัวข้อเนื้อหาในหลักสูตรนี้
                </p>
            @endforelse
        </div>
    </div>

    {{-- ============================ --}}
    {{-- 3) การบ้าน / ชิ้นงาน --}}
    {{-- ============================ --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">การบ้าน / ชิ้นงาน</h2>

            <a href="#"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                เพิ่มการบ้าน
            </a>
        </div>

        <div class="space-y-4">
            {{-- ตัวอย่าง loop ถ้าเฮียมี relation เช่น $course->assignments --}}
            @forelse (($course->assignments ?? []) as $hw)
                <div class="bg-slate-50 rounded-2xl px-5 py-4 text-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $hw->title ?? 'ชื่อการบ้าน / ชิ้นงาน' }}
                            </p>
                            <p class="text-gray-600">
                                กำหนดส่ง: {{ $hw->due_date ?? '-' }}  
                                @if(!empty($hw->full_score))
                                    — คะแนนเต็ม: {{ $hw->full_score }} คะแนน
                                @endif
                            </p>
                            @if (!empty($hw->description))
                                <p class="text-gray-700 mt-1">
                                    {{ $hw->description }}
                                </p>
                            @endif
                        </div>

                        <button type="button" class="text-red-500">
                            ลบ
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm">
                    ยังไม่มีการบันทึกการบ้านหรือชิ้นงานสำหรับหลักสูตรนี้
                </p>
            @endforelse
        </div>
    </div>

</div>
@endsection
