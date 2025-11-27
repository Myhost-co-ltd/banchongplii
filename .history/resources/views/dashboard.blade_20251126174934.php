@extends('layouts.layout')

@section('title', 'à¹à¸”à¸Šà¸šà¸­à¸£à¹Œà¸”à¸„à¸£à¸¹')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <!-- Header -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
<h2 class="text-3xl font-bold text-gray-900" data-i18n-th="à¹à¸”à¸Šà¸šà¸­à¸£à¹Œà¸”à¸„à¸£à¸¹" data-i18n-en="Teacher Dashboard">à¹à¸”à¸Šà¸šà¸­à¸£à¹Œà¸”à¸„à¸£à¸¹</h2>
<p class="text-gray-600 mt-1">
    <span data-i18n-th="à¸¢à¸´à¸™à¸”à¸µà¸•à¹‰à¸­à¸™à¸£à¸±à¸š" data-i18n-en="Welcome">à¸¢à¸´à¸™à¸”à¸µà¸•à¹‰à¸­à¸™à¸£à¸±à¸š</span> <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1" data-i18n-th="à¸ˆà¸³à¸™à¸§à¸™à¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¹ƒà¸™à¸«à¹‰à¸­à¸‡" data-i18n-en="Students in class">à¸ˆà¸³à¸™à¸§à¸™à¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¹ƒà¸™à¸«à¹‰à¸­à¸‡</h3>
            <p class="text-4xl font-bold text-blue-700">{{ number_format($studentCount ?? 0) }}</p>
        </div>

        <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1" data-i18n-th="à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š" data-i18n-en="Courses in charge">à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š</h3>
            <p class="text-4xl font-bold text-green-700">{{ number_format($courseCount ?? 0) }}</p>
        </div>

        {{-- <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-2xl text-center shadow-sm">
            <h3 class="text-sm text-gray-600 mb-1">à¸¡à¸²à¹€à¸£à¸µà¸¢à¸™à¸§à¸±à¸™à¸™à¸µà¹‰</h3>
            <p class="text-4xl font-bold text-yellow-700">{{ number_format($attendanceToday ?? 0) }}</p>
        </div> --}}
    </div>

    <!-- Course List -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-800 mb-4" data-i18n-th="à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š" data-i18n-en="Courses in charge">à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š</h3>

        <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left" data-i18n-th="à¸Šà¸·à¹ˆà¸­à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£" data-i18n-en="Course Name">à¸Šà¸·à¹ˆà¸­à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£</th>
                    <th class="py-3 px-4 text-center" data-i18n-th="à¸«à¹‰à¸­à¸‡" data-i18n-en="Room">à¸«à¹‰à¸­à¸‡</th>
                    <th class="py-3 px-4 text-center" data-i18n-th="à¸ˆà¸±à¸”à¸à¸²à¸£" data-i18n-en="Actions">à¸ˆà¸±à¸”à¸à¸²à¸£</th>
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
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="text-yellow-600 hover:underline">à¹à¸à¹‰à¹„à¸‚</a>
                        <span class="mx-1 text-gray-300">|</span>
                        <form action="{{ route('teacher.courses.destroy', $course) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('à¸•à¹‰à¸­à¸‡à¸à¸²à¸£à¸¥à¸šà¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸™à¸µà¹‰à¸«à¸£à¸·à¸­à¹„à¸¡à¹ˆ?')">
                                à¸¥à¸š
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-4 px-4 text-center text-gray-500" data-i18n-th="à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¸¡à¸µà¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸„à¸¸à¸“à¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š" data-i18n-en="No courses assigned yet">
                        à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¸¡à¸µà¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£à¸—à¸µà¹ˆà¸„à¸¸à¸“à¸£à¸±à¸šà¸œà¸´à¸”à¸Šà¸­à¸š
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
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <div>
                    <span data-i18n-th="ห้อง:" data-i18n-en="Room:">ห้อง:</span>
                    @php
                        $roomTags = ($assignedRooms ?? collect())->filter();
                    @endphp
                    @if($roomTags->isNotEmpty())
                        <span class="font-semibold text-blue-700">{{ $roomTags->join(', ') }}</span>
                    @else
                        <span class="text-gray-400" data-i18n-th="ยังไม่มีห้องประจำ" data-i18n-en="No room assigned">ยังไม่มีห้องประจำ</span>
                    @endif
                </div>
                <a href="{{ route('teacher.homeroom.export') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-600 text-white shadow hover:bg-blue-700 text-sm">
                    <span data-i18n-th="Export PDF" data-i18n-en="Export PDF">Export PDF</span>
                </a>
            </div>
            </div>
        </div>

        @php
            $studentsByRoom = collect($students ?? [])->groupBy(fn($s) => $s->room ?? '-');
            $roomLoop = ($assignedRooms ?? collect())->isNotEmpty() ? $assignedRooms : $studentsByRoom->keys();
        @endphp


        @forelse($roomLoop as $room)
            <div class="mb-6">
                <h4 class="text-md font-semibold text-blue-700 mb-2">à¸«à¹‰à¸­à¸‡ {{ $room }}</h4>
                <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left" data-i18n-th="à¸£à¸«à¸±à¸ª" data-i18n-en="Code">à¸£à¸«à¸±à¸ª</th>
                            <th class="py-3 px-4 text-left" data-i18n-th="à¸Šà¸·à¹ˆà¸­" data-i18n-en="First Name">à¸Šà¸·à¹ˆà¸­</th>
                            <th class="py-3 px-4 text-left" data-i18n-th="à¸™à¸²à¸¡à¸ªà¸à¸¸à¸¥" data-i18n-en="Last Name">à¸™à¸²à¸¡à¸ªà¸à¸¸à¸¥</th>
                            <th class="py-3 px-4 text-center" data-i18n-th="à¸«à¹‰à¸­à¸‡" data-i18n-en="Room">à¸«à¹‰à¸­à¸‡</th>
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
                                <td colspan="4" class="py-3 px-4 text-center text-gray-400">à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¸¡à¸µà¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¹ƒà¸™à¸«à¹‰à¸­à¸‡à¸™à¸µà¹‰</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center text-gray-500 py-6">
                @if(($assignedRooms ?? collect())->isEmpty())
                    <span data-i18n-th="à¹‚à¸›à¸£à¸”à¸à¸³à¸«à¸™à¸”à¸«à¹‰à¸­à¸‡à¹ƒà¸™à¹‚à¸›à¸£à¹„à¸Ÿà¸¥à¹Œà¸„à¸£à¸¹ à¸«à¸£à¸·à¸­à¹€à¸žà¸´à¹ˆà¸¡à¸«à¹‰à¸­à¸‡à¹ƒà¸™à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£ à¹€à¸žà¸·à¹ˆà¸­à¹à¸ªà¸”à¸‡à¸£à¸²à¸¢à¸Šà¸·à¹ˆà¸­à¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™" data-i18n-en="Please set room in profile or courses to show students">à¹‚à¸›à¸£à¸”à¸à¸³à¸«à¸™à¸”à¸«à¹‰à¸­à¸‡à¹ƒà¸™à¹‚à¸›à¸£à¹„à¸Ÿà¸¥à¹Œà¸„à¸£à¸¹ à¸«à¸£à¸·à¸­à¹€à¸žà¸´à¹ˆà¸¡à¸«à¹‰à¸­à¸‡à¹ƒà¸™à¸«à¸¥à¸±à¸à¸ªà¸¹à¸•à¸£ à¹€à¸žà¸·à¹ˆà¸­à¹à¸ªà¸”à¸‡à¸£à¸²à¸¢à¸Šà¸·à¹ˆà¸­à¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™</span>
                @else
                    <span data-i18n-th="à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¸¡à¸µà¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¹ƒà¸™à¸«à¹‰à¸­à¸‡à¸‚à¸­à¸‡à¸„à¸¸à¸“" data-i18n-en="No students in your classroom yet">à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¸¡à¸µà¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¹ƒà¸™à¸«à¹‰à¸­à¸‡à¸‚à¸­à¸‡à¸„à¸¸à¸“</span>
                @endif
            </div>
        @endforelse
    </div>

   

    <!-- Notifications -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">à¸›à¸£à¸°à¸à¸²à¸¨à¸ˆà¸²à¸à¹‚à¸£à¸‡à¹€à¸£à¸µà¸¢à¸™</h3>

        <ul class="space-y-3 text-sm text-gray-700">
            <li>ðŸ“Œ à¸§à¸±à¸™à¸—à¸µà¹ˆ 15 à¸™à¸µà¹‰ à¸¡à¸µà¸›à¸£à¸°à¸Šà¸¸à¸¡à¸„à¸£à¸¹à¸—à¸±à¹‰à¸‡à¹‚à¸£à¸‡à¹€à¸£à¸µà¸¢à¸™</li>
            <li>ðŸ“Œ à¸™à¸±à¸à¹€à¸£à¸µà¸¢à¸™à¸•à¹‰à¸­à¸‡à¸ªà¹ˆà¸‡à¸‡à¸²à¸™à¸§à¸´à¸—à¸¢à¸²à¸¨à¸²à¸ªà¸•à¸£à¹Œà¸ à¸²à¸¢à¹ƒà¸™à¸§à¸±à¸™à¸—à¸µà¹ˆ 20</li>
            <li>ðŸ“Œ à¹€à¸•à¸£à¸µà¸¢à¸¡à¸ªà¸£à¸¸à¸›à¸à¸²à¸£à¸›à¸£à¸°à¹€à¸¡à¸´à¸™à¸œà¸¥à¸ªà¸´à¹‰à¸™à¸ à¸²à¸„à¹€à¸£à¸µà¸¢à¸™</li>
        </ul>
    </div>

</div>

{{-- à¹‚à¸«à¸¥à¸” Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scoreChartEl = document.getElementById('scoreChart');
        if (scoreChartEl) {
            const ctx = scoreChartEl.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['à¸„à¸“à¸´à¸•', 'à¸§à¸´à¸—à¸¢à¹Œ', 'à¸ à¸²à¸©à¸²à¹„à¸—à¸¢', 'à¸ªà¸±à¸‡à¸„à¸¡'],
                    datasets: [{
                        label: 'à¸„à¸°à¹à¸™à¸™à¹€à¸‰à¸¥à¸µà¹ˆà¸¢',
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
