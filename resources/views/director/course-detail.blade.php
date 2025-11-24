@extends('layouts.layout-director')

@section('title', 'รายละเอียดหลักสูตร')

@php
    $rooms = collect($course->rooms ?? []);
    $hours = collect($course->teaching_hours ?? []);
    $lessons = collect($course->lessons ?? []);
    $assignments = collect($course->assignments ?? []);

    $hoursByTerm = $hours->groupBy(fn ($item) => $item['term'] ?? $course->term ?? '-');
    $lessonsByTerm = $lessons->groupBy(fn ($item) => $item['term'] ?? $course->term ?? '-');
    $assignmentsByTerm = $assignments->groupBy(fn ($item) => $item['term'] ?? $course->term ?? '-');
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500 uppercase tracking-wide">รายละเอียดหลักสูตร</p>
            <h1 class="text-3xl font-bold text-gray-900">{{ $course->name }}</h1>
            <p class="text-gray-600 mt-2">
                ระดับ {{ $course->grade ?? '-' }}
                @if ($course->term)
                    | ภาคเรียน {{ $course->term }}
                @endif
                @if ($course->year)
                    | ปีการศึกษา {{ $course->year }}
                @endif
            </p>
        </div>
        <a href="{{ route('director.teacher-plans') }}"
           class="text-sm text-blue-600 hover:underline">
            ← กลับไปหน้าแผนการสอนของครู
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50">
            <p class="text-xs text-blue-700">ครูผู้สอน</p>
            <p class="text-lg font-semibold text-blue-900">{{ optional($course->teacher)->name ?? '-' }}</p>
            @if (! empty(optional($course->teacher)->email))
                <p class="text-sm text-blue-800 mt-1">{{ $course->teacher->email }}</p>
            @endif
        </div>
        <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50">
            <p class="text-xs text-emerald-700">ห้องเรียนในหลักสูตร</p>
            <p class="text-2xl font-bold text-emerald-900">{{ $roomsCount }}</p>
        </div>
        <div class="p-4 rounded-2xl border border-indigo-100 bg-indigo-50">
            <p class="text-xs text-indigo-700">ชั่วโมงสอนที่บันทึก</p>
            <p class="text-2xl font-bold text-indigo-900">{{ $hours->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl border border-amber-100 bg-amber-50">
            <p class="text-xs text-amber-700">บทเรียน / งาน</p>
            <p class="text-2xl font-bold text-amber-900">{{ $lessons->count() }} / {{ $assignments->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">ข้อมูลหลักสูตร</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500">ภาคเรียน</p>
                <p class="text-lg font-semibold text-gray-900">{{ $course->term ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="text-lg font-semibold text-gray-900">{{ $course->year ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">ชั้น</p>
                <p class="text-lg font-semibold text-gray-900">{{ $course->grade ?? '-' }}</p>
            </div>
        </div>
        <div>
            <p class="text-sm text-gray-500">ห้องเรียน</p>
            <div class="flex flex-wrap gap-2 mt-1">
                @forelse ($rooms as $room)
                    <span class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                        {{ $room }}
                    </span>
                @empty
                    <span class="text-gray-400 text-sm">ยังไม่มีข้อมูลห้องเรียน</span>
                @endforelse
            </div>
        </div>
        <div>
            <p class="text-sm text-gray-500">คำอธิบายหลักสูตร</p>
            <p class="text-gray-700 mt-1 leading-relaxed">{{ $course->description ?? '-' }}</p>
        </div>
    </div>

    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">ชั่วโมงสอน</h3>
            <p class="text-sm text-gray-500">แสดงเฉพาะข้อมูลที่ครูบันทึก</p>
        </div>
        <div class="space-y-6">
            @forelse ($hoursByTerm as $term => $items)
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-700">ภาคเรียน {{ $term }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($items as $item)
                            <div class="border border-gray-100 rounded-2xl p-4 space-y-1">
                                <p class="font-semibold text-gray-900">{{ $item['category'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $item['hours'] ?? 0 }} {{ $item['unit'] ?? 'ชั่วโมง' }}
                                </p>
                                @if (! empty($item['note'] ?? null))
                                    <p class="text-sm text-gray-500">{{ $item['note'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">ยังไม่บันทึกชั่วโมงสอน</p>
            @endforelse
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">บทเรียน</h3>
            <p class="text-sm text-gray-500">เนื้อหาที่ครูสร้างไว้ในหลักสูตรนี้</p>
        </div>
        <div class="space-y-6">
            @forelse ($lessonsByTerm as $term => $items)
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-700">ภาคเรียน {{ $term }}</p>
                    <div class="space-y-3">
                        @foreach ($items as $item)
                            <div class="border border-gray-100 rounded-2xl p-4 space-y-1">
                                <p class="font-semibold text-gray-900">{{ $item['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $item['hours'] ?? 0 }} ชั่วโมง · {{ $item['period'] ?? '-' }}
                                </p>
                                @if (! empty($item['details'] ?? null))
                                    <p class="text-sm text-gray-500">{{ $item['details'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">ยังไม่พบบทเรียน</p>
            @endforelse
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 space-y-4 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">งาน / การบ้าน</h3>
            <p class="text-sm text-gray-500">ข้อมูลที่ครูสร้างไว้เท่านั้น</p>
        </div>
        <div class="space-y-6">
            @forelse ($assignmentsByTerm as $term => $items)
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-700">ภาคเรียน {{ $term }}</p>
                    <div class="space-y-3">
                        @foreach ($items as $item)
                            <div class="border border-gray-100 rounded-2xl p-4 space-y-1">
                                <p class="font-semibold text-gray-900">{{ $item['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    คะแนนรวม: {{ $item['score'] ?? '-' }}
                                    @if (! empty($item['due_date'] ?? null))
                                        <span class="mx-2 text-gray-300">|</span>
                                        กำหนดส่ง:
                                        {{ \Illuminate\Support\Carbon::parse($item['due_date'])->locale('th')->isoFormat('D MMM YYYY') }}
                                    @endif
                                </p>
                                @if (! empty($item['notes'] ?? null))
                                    <p class="text-sm text-gray-500">{{ $item['notes'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">ยังไม่พบงานหรือการบ้าน</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
