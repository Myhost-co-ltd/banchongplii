@extends('layouts.layout')

@section('title', 'แดชบอร์ดครู')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <!-- Header -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <h2 class="text-3xl font-bold text-gray-900">แดชบอร์ดครู</h2>
        <p class="text-gray-600 mt-1">
            ยินดีต้อนรับ <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- Stats -->
    @php
        $studentCount = 40;
        $courseCount  = 4;
        $attendanceToday = 38;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1">จำนวนนักเรียนในห้อง</h3>
            <p class="text-4xl font-bold text-blue-700">{{ $studentCount }}</p>
        </div>

        <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1">หลักสูตรที่สร้างแล้ว</h3>
            <p class="text-4xl font-bold text-green-700">{{ $courseCount }}</p>
        </div>

        <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1">มาเรียนวันนี้</h3>
            <p class="text-4xl font-bold text-yellow-700">{{ $attendanceToday }}</p>
        </div>

    </div>

    <!-- Course List -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">หลักสูตรที่รับผิดชอบ</h3>

        @php
            $courses = [
                ['name' => 'คณิตศาสตร์พื้นฐาน ป.1', 'room'=>'ป1/1'],
                ['name' => 'วิทยาศาสตร์ ป.1', 'room'=>'ป1/1'],
                ['name' => 'ภาษาไทย ป.1', 'room'=>'ป1/1'],
                ['name' => 'สังคมศึกษา ป.1', 'room'=>'ป1/1'],
            ];
        @endphp

        <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">ชื่อหลักสูตร</th>
                    <th class="py-3 px-4 text-center">ห้อง</th>
                    <th class="py-3 px-4 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($courses as $c)
                <tr class="hover:bg-blue-50">
                    <td class="py-2 px-4">{{ $c['name'] }}</td>
                    <td class="py-2 px-4 text-center">{{ $c['room'] }}</td>
                    <td class="py-2 px-4 text-center">
                        <button class="text-yellow-600 hover:underline">แก้ไข</button> |
                        <button class="text-red-600 hover:underline">ลบ</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
    const ctx = document.getElementById('scoreChart').getContext('2d');

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
</script>

@endsection
