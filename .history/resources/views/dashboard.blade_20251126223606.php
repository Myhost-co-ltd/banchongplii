@extends('layouts.layout')

@section('title', 'แดชบอร์ดครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <!-- Header -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
<h2 class="text-3xl font-bold text-gray-900" data-i18n-th="แดชบอร์ดครู" data-i18n-en="Teacher Dashboard">แดชบอร์ดครู</h2>
<p class="text-gray-600 mt-1">
    <span data-i18n-th="ยินดีต้อนรับ" data-i18n-en="Welcome">ยินดีต้อนรับ</span> <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1" data-i18n-th="จำนวนนักเรียนในห้อง" data-i18n-en="Students in class">จำนวนนักเรียนในห้อง</h3>
            <p class="text-4xl font-bold text-blue-700">{{ number_format($studentCount ?? 0) }}</p>
        </div>

        <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1" data-i18n-th="หลักสูตรที่รับผิดชอบ" data-i18n-en="Courses in charge">หลักสูตรที่รับผิดชอบ</h3>
            <p class="text-4xl font-bold text-green-700">{{ number_format($courseCount ?? 0) }}</p>
        </div>

        {{-- <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1">มาเรียนวันนี้</h3>
            <p class="text-4xl font-bold text-yellow-700">{{ number_format($attendanceToday ?? 0) }}</p>
        </div> --}}
    </div>

    <!-- Course List -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-4" data-i18n-th="หลักสูตรที่รับผิดชอบ" data-i18n-en="Courses in charge">หลักสูตรที่รับผิดชอบ</h3>

        <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left" data-i18n-th="ชื่อหลักสูตร" data-i18n-en="Course Name">ชื่อหลักสูตร</th>
                    <th class="py-3 px-4 text-center" data-i18n-th="ห้อง" data-i18n-en="Room">ห้อง</th>
                    <th class="py-3 px-4 text-center" data-i18n-th="จัดการ" data-i18n-en="Actions">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($courses ?? [] as $course)
                @php
                    $roomsText = collect($course->rooms ?? [])->filter()->join(', ');
                @endphp
                <tr class="hover:bg-blue-50">
                    <td class="py-2 px-4">{{ $course->name }}</td>
                    <td class="py-2 px-4 text-center">{{ $roomsText !== '' ? $roomsText : '-' }}</td>
                    <td class="py-2 px-4 text-center">
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="text-yellow-600 hover:underline">แก้ไข</a>
                        <span class="mx-1 text-gray-300">|</span>
                        <form action="{{ route('teacher.courses.destroy', $course) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบหลักสูตรนี้หรือไม่?')">
                                ลบ
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-4 px-4 text-center text-gray-500" data-i18n-th="ยังไม่มีหลักสูตรที่คุณรับผิดชอบ" data-i18n-en="No courses assigned yet">
                        ยังไม่มีหลักสูตรที่คุณรับผิดชอบ
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Student list for homeroom -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-800" data-i18n-th="ห้องที่รับผิดชอบ" data-i18n-en="Homeroom students">ห้องที่รับผิดชอบ</h3>
            <div class="text-sm text-gray-500">
                <span data-i18n-th="ห้อง:" data-i18n-en="Room:">ห้อง:</span>
                @php
                    $roomTags = ($assignedRooms ?? collect())->filter();
                @endphp
                @if($roomTags->isNotEmpty())
                    <span class="font-semibold text-blue-700">{{ $roomTags->join(', ') }}</span>
                @else
                    <span class="text-gray-400" data-i18n-th="ยังไม่กำหนดห้อง" data-i18n-en="No room assigned">ยังไม่กำหนดห้อง</span>
                @endif
            </div>
        </div>

        @php
            $studentsByRoom = collect($students ?? [])->groupBy(fn($s) => $s->room ?? '-');
            $roomLoop = ($assignedRooms ?? collect())->isNotEmpty() ? $assignedRooms : $studentsByRoom->keys();
        @endphp


        @forelse($roomLoop as $room)
            <div class="mb-6">
                <h4 class="text-md font-semibold text-blue-700 mb-2">ห้อ {{ $room }}</h4>
                <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left" data-i18n-th="รหัส" data-i18n-en="Code">รหัส</th>
                            <th class="py-3 px-4 text-left" data-i18n-th="ชื่อ" data-i18n-en="First Name">ชื่อ</th>
                            <th class="py-3 px-4 text-left" data-i18n-th="นามสกุล" data-i18n-en="Last Name">นามสกุล</th>
                            <th class="py-3 px-4 text-center" data-i18n-th="ห้อง" data-i18n-en="Room">ห้อง</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($studentsByRoom->get($room, collect()) as $student)
                            <tr class="hover:bg-blue-50">
                                <td class="py-2 px-4 font-semibold text-blue-700">{{ $student->student_code }}</td>
                                <td class="py-2 px-4">{{ $student->first_name }}</td>
                                <td class="py-2 px-4">{{ $student->last_name }}</td>
                                <td class="py-2 px-4 text-center">{{ $student->room ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-center text-gray-400">ยังไม่มีนักเรียนในห้องนี้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                @if(($assignedRooms ?? collect())->isEmpty())
                    <span data-i18n-th="โปรดกำหนดห้องในโปรไฟล์ครู หรือเพิ่มห้องในหลักสูตร เพื่อแสดงรายชื่อนักเรียน" data-i18n-en="Please set room in profile or courses to show students">โปรดกำหนดห้องในโปรไฟล์ครู หรือเพิ่มห้องในหลักสูตร เพื่อแสดงรายชื่อนักเรียน</span>
                @else
                    <span data-i18n-th="ยังไม่มีนักเรียนในห้องของคุณ" data-i18n-en="No students in your classroom yet">ยังไม่มีนักเรียนในห้องของคุณ</span>
                @endif
            </div>
        @endforelse
    </div>

   

    <!-- Notifications -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">ประกาศจากโรงเรียน</h3>

        <ul class="space-y-3 text-sm text-gray-700">
            <li>📌 วันที่ 15 นี้ มีประชุมครูทั้งโรงเรียน</li>
            <li>📌 นักเรียนต้องส่งงานวิทยาศาสตร์ภายในวันที่ 20</li>
            <li>📌 เตรียมสรุปการประเมินผลสิ้นภาคเรียน</li>
        </ul>
    </div>

</div>

{{-- โหลด Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scoreChartEl = document.getElementById('scoreChart');
        if (scoreChartEl) {
            const ctx = scoreChartEl.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['คณิต', 'วิทย์', 'ภาษาไทย', 'สังคม'],
                    datasets: [{
                        label: 'คะแนนเฉลี่ย',
                        data: [78, 82, 75, 88],
                        backgroundColor: ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa'],
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    });
</script>

@endsection