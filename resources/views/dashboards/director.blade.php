@extends('layouts.layout-director')

@section('title', 'แดชบอร์ดผู้อำนวยการ')

@section('content')
<div class="space-y-10 overflow-y-auto pr-2">

    <!-- หัวข้อหลัก -->
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <h2 class="text-3xl font-bold text-gray-900" data-i18n-th="แดชบอร์ดผู้อำนวยการ" data-i18n-en="Director Dashboard">แดชบอร์ดผู้อำนวยการ</h2>
        <p class="text-gray-600 mt-2">
            <span data-i18n-th="ยินดีต้อนรับ" data-i18n-en="Welcome">ยินดีต้อนรับ</span>
            <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
        </p>
    </div>

    <!-- สถิติหลัก -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-200 border border-blue-300 rounded-2xl shadow">
            <h3 class="text-gray-600" data-i18n-th="จำนวนนักเรียนทั้งหมด" data-i18n-en="Total students">จำนวนนักเรียนทั้งหมด</h3>
            <p class="text-4xl font-bold text-blue-800 mt-1">{{ number_format($studentCount ?? 0) }}</p>
        </div>

        <div class="p-6 bg-gradient-to-br from-green-50 to-green-200 border border-green-300 rounded-2xl shadow">
            <h3 class="text-gray-600" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">จำนวนครูทั้งหมด</h3>
            <p class="text-4xl font-bold text-green-800 mt-1">{{ number_format($teacherCount ?? 0) }}</p>
        </div>

        <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-200 border border-purple-300 rounded-2xl shadow">
            <h3 class="text-gray-600" data-i18n-th="ห้องเรียนทั้งหมด" data-i18n-en="Total classrooms">ห้องเรียนทั้งหมด</h3>
            <p class="text-4xl font-bold text-purple-800 mt-1">{{ number_format($classCount ?? 0) }}</p>
        </div>

    </div>

    <!-- วิชาเอกของครู -->
    @php
        $majorsList = $allMajors ?? collect();
    @endphp
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">วิชาเอกของครู</h2>
                <p class="text-sm text-gray-500">เลือกวิชาเพื่อดูครูผู้รับผิดชอบ</p>
            </div>
            <div class="flex items-center gap-2">
                <label for="directorMajorFilter" class="text-sm text-gray-600 hidden md:block">เลือกวิชา:</label>
                <select id="directorMajorFilter" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">วิชาเอกทั้งหมด</option>
                    @foreach($majorsList as $major)
                        <option value="{{ $major }}">{{ $major }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">วิชาเอก</th>
                        <th class="py-3 px-4 text-center w-32">ดูรายละเอียด</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($majorsList as $major)
                        @php($teachersForMajor = ($teachersByMajor[$major] ?? collect()))
                        <tr class="hover:bg-blue-50" data-major-row="{{ trim($major) }}" data-has-teacher="{{ $teachersForMajor->isNotEmpty() ? '1' : '0' }}">
                            <td class="py-2 px-4 font-semibold text-gray-900">{{ $major }}</td>
                            <td class="py-2 px-4 text-center">
                                <button type="button"
                                        class="text-blue-600 hover:underline"
                                        data-major-toggle="{{ trim($major) }}">
                                    ดูรายชื่อครู
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-blue-50/40" data-major-detail="{{ trim($major) }}">
                            <td colspan="2" class="py-3 px-4">
                                @if($teachersForMajor->isEmpty())
                                    <div class="text-gray-500 text-sm">ยังไม่ระบุครูผู้รับผิดชอบสำหรับวิชา {{ $major }}</div>
                                @else
                                    <div class="text-sm text-gray-800 space-y-2">
                                        <div class="font-semibold text-gray-900">ครูผู้รับผิดชอบ ({{ $teachersForMajor->count() }} คน)</div>
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach($teachersForMajor as $teacher)
                                                <li>
                                                    <span class="font-medium">{{ $teacher->name }}</span>
                                                    <span class="text-gray-500">({{ $teacher->email ?? '-' }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-4 px-4 text-center text-gray-500">ยังไม่มีข้อมูลวิชาเอก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-3" id="directorMajorSummary">
            แสดงวิชาเอกทั้งหมด {{ $majorsList->count() }} วิชา
        </p>
    </div>

    <!-- หลักสูตรทั้งหมด -->
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100 mb-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">หลักสูตรที่รับผิดชอบ</h2>
                <p class="text-sm text-gray-500">ดูหลักสูตรทั้งหมดและครูผู้สอน</p>
            </div>
            <span class="text-sm text-gray-500">จำนวน {{ ($courses ?? collect())->count() }} หลักสูตร</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">ชื่อหลักสูตร</th>
                        <th class="py-3 px-4 text-left">ครูผู้สอน</th>
                        <th class="py-3 px-4 text-center">ห้อง</th>
                        <th class="py-3 px-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse(($courses ?? collect()) as $course)
                        @php($roomsText = collect($course->rooms ?? [])->filter()->join(', '))
                        <tr class="hover:bg-blue-50">
                            <td class="py-2 px-4 text-gray-900 font-medium">{{ $course->name }}</td>
                            <td class="py-2 px-4 text-gray-900">{{ $course->teacher->name ?? '-' }}</td>
                            <td class="py-2 px-4 text-center text-gray-700">{{ $roomsText !== '' ? $roomsText : '-' }}</td>
                            <td class="py-2 px-4 text-center">
                                <a href="{{ route('director.course-detail', $course) }}" class="text-blue-600 hover:underline">ดูรายละเอียด</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500">ยังไม่มีหลักสูตรในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const majorFilter = document.getElementById('directorMajorFilter');
        const majorRows = Array.from(document.querySelectorAll('[data-major-row]'));
        const detailRows = Array.from(document.querySelectorAll('[data-major-detail]'));
        const summary = document.getElementById('directorMajorSummary');

        const hideAllDetails = () => {
            detailRows.forEach(row => row.classList.add('hidden'));
            document.querySelectorAll('[data-major-toggle]').forEach(btn => btn.textContent = 'ดูรายชื่อครู');
        };

        const applyFilter = () => {
            const selected = (majorFilter?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            majorRows.forEach(row => {
                const rowMajor = (row.dataset.majorRow || '').toLowerCase();
                const match = !selected || rowMajor === selected;
                row.style.display = match ? '' : 'none';

                const detailRow = document.querySelector(`[data-major-detail="${row.dataset.majorRow}"]`);
                if (detailRow) {
                    detailRow.style.display = match ? detailRow.style.display : 'none';
                }
                if (match) visibleCount += 1;
            });

            if (summary) {
                summary.textContent = selected
                    ? `แสดงเฉพาะวิชา ${majorFilter.value} (${visibleCount} รายการ)`
                    : `แสดงวิชาเอกทั้งหมด ${majorRows.length} วิชา`;
            }
        };

        document.querySelectorAll('[data-major-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const major = btn.dataset.majorToggle;
                const detailRow = document.querySelector(`[data-major-detail="${major}"]`);
                if (!detailRow) return;

                const isHidden = detailRow.classList.contains('hidden');
                hideAllDetails();

                if (isHidden) {
                    detailRow.classList.remove('hidden');
                    btn.textContent = 'ซ่อนรายชื่อ';
                }
            });
        });

        if (majorFilter) {
            majorFilter.addEventListener('change', () => {
                hideAllDetails();
                applyFilter();
            });
        }

        applyFilter();
    });
</script>
@endsection
