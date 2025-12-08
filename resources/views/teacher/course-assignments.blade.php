@extends('layouts.layout')

@section('title', 'งาน / คะแนนเก็บ | ระบบครู')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $courseOptions = collect($courses ?? []);
    $assignmentList = collect($assignments ?? []);
    $totalAssignments = $assignmentList->count();
    $assignmentTotalScore = $assignmentTotal ?? 0;
    $assignmentCapScore = $assignmentCap ?? 70;
    $assignmentRemainingScore = $assignmentRemaining ?? max(0, $assignmentCapScore - $assignmentTotalScore);
    $currentTerm = $selectedTerm ?? request('term');
    $studentsByRoom = collect($studentsByRoom ?? []);
    $assignedRooms = collect($assignedRooms ?? []);
    $studentsFlat = $studentsByRoom->flatten(1)->values();
    $studentTotal = $studentsFlat->count();
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    {{-- Header --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">งาน / คะแนนเก็บ</p>
                <h1 class="text-3xl font-bold text-gray-900">ติดตามงานของนักเรียน</h1>
                <p class="text-gray-600">เลือกหลักสูตรและภาคเรียนเพื่อดูงาน/คะแนนเก็บทั้งหมดของภาคเรียนนี้</p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('teacher.course-create') }}"
                       class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                        กลับไปหน้าสร้างหลักสูตร
                    </a>
                    @if($course ?? false)
                        <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                            ไปจัดการงาน/คะแนนเก็บ
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-80 space-y-4">
                {{-- Course selector --}}
                @if($courseOptions->isNotEmpty())
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">เลือกหลักสูตร</label>
                        <select id="assignmentCourseSelector"
                                class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($courseOptions as $courseOption)
                                <option value="{{ route('teacher.assignments', ['course' => $courseOption->id, 'term' => $currentTerm]) }}"
                                        @selected(optional($course)->id === $courseOption->id)>
                                    {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Term selector --}}
                @if($course ?? false)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">เลือกภาคเรียน</label>
                        <form id="assignmentTermForm" action="{{ route('teacher.assignments', $course) }}" method="GET">
                            <select name="term"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                    onchange="document.getElementById('assignmentTermForm').submit()">
                                <option value="">-- เลือกภาคเรียน --</option>
                                <option value="1" {{ $currentTerm === '1' ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                                <option value="2" {{ $currentTerm === '2' ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                            </select>
                        </form>
                        <p class="text-xs text-gray-400 mt-1">* เลือกภาคเรียนเพื่อดูงานในเทอมนั้น</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- If no course --}}
    @unless($course ?? false)
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">ยังไม่มีหลักสูตร</h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                กรุณาเพิ่มหลักสูตรก่อน แล้วกลับมายังหน้านี้เพื่อดูงานและคะแนนเก็บของนักเรียน
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                เพิ่มหลักสูตรใหม่
            </a>
        </div>
    @else

        {{-- Course snapshot --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100">
                    <p class="text-sm text-blue-700">หลักสูตร</p>
                    <p class="text-lg font-semibold text-blue-900">{{ $course->name }}</p>
                    <p class="text-xs text-blue-700/70 mt-1">ระดับชั้น: {{ $course->grade ?? '-' }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-green-50 border border-green-100">
                    <p class="text-sm text-green-700">งานทั้งหมด</p>
                    <p class="text-3xl font-bold text-green-800">{{ number_format($totalAssignments) }}</p>
                    <p class="text-xs text-green-700/70 mt-1">ภาคเรียนที่ {{ $currentTerm ?: '-' }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                    <p class="text-sm text-amber-700">คะแนนรวม / เพดาน</p>
                    <p class="text-lg font-semibold text-amber-800">
                        {{ number_format($assignmentTotalScore, 2) }} / {{ number_format($assignmentCapScore, 2) }}
                    </p>
                    <p class="text-xs text-amber-700/80 mt-1">เหลือได้กำหนด: {{ number_format($assignmentRemainingScore, 2) }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-purple-50 border border-purple-100 md:col-span-3 lg:col-span-1">
                    <p class="text-sm text-purple-700">จำนวนนักเรียนในห้อง</p>
                    <p class="text-3xl font-bold text-purple-800">{{ number_format($studentTotal) }}</p>
                    <p class="text-xs text-purple-700/80 mt-1">
                        ห้อง: {{ $assignedRooms->isNotEmpty() ? $assignedRooms->join(', ') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Students by room --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">นักเรียนในห้อง</h3>
                    <p class="text-sm text-gray-500">รายชื่อนักเรียนตามห้องเรียนของหลักสูตรนี้</p>
                </div>
            </div>

            @if($studentsByRoom->isEmpty())
                <div class="border border-dashed border-gray-200 rounded-2xl p-6 text-center text-gray-500">
                    ยังไม่มีข้อมูลนักเรียนสำหรับห้องเรียนที่ระบุในหลักสูตรนี้
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($studentsByRoom as $room => $students)
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-lg font-semibold text-gray-900">{{ $room ?: '-' }}</p>
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                    {{ number_format(collect($students)->count()) }} คน
                                </span>
                            </div>
                            <ul class="text-sm text-gray-700 space-y-1">
                                @forelse($students as $student)
                                    <li class="flex items-center justify-between gap-3">
                                        <span class="font-medium">{{ $student->student_code ?? '-' }}</span>
                                        <span class="text-gray-600">{{ trim(($student->title ?? '').' '.($student->first_name ?? '').' '.($student->last_name ?? '')) }}</span>
                                    </li>
                                @empty
                                    <li class="text-gray-400 text-sm">ยังไม่มีนักเรียนในห้องนี้</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Assignment list --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">งาน / คะแนนเก็บ</h3>
                    <p class="text-sm text-gray-500">รายการงานทั้งหมดในภาคเรียนนี้</p>
                </div>
                <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}#assignments"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm shadow hover:bg-blue-700">
                    เพิ่มงาน/คะแนนเก็บ
                </a>
            </div>

            @if($assignmentList->isEmpty())
                <div class="border border-dashed border-gray-200 rounded-2xl p-6 text-center text-gray-500">
                    ยังไม่มีงานหรือคะแนนเก็บในภาคเรียนนี้
                </div>
            @else
                <div class="space-y-4">
                    @foreach($assignmentList as $assignment)
                        @php
                            $dueDate = $assignment['due_date'] ?? null;
                            $score = $assignment['score'] ?? null;
                            $isOverdue = $dueDate && now($tz)->toDateString() > $dueDate;
                        @endphp
                        <div class="border border-gray-100 rounded-2xl p-4 hover:border-blue-200 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-lg font-semibold text-gray-900">{{ $assignment['title'] ?? '-' }}</p>
                                    <p class="text-sm text-gray-600">
                                        คะแนนเต็ม: {{ $score !== null ? number_format($score, 2) : '-' }}
                                        @if($dueDate)
                                            • ส่งภายใน: <span class="{{ $isOverdue ? 'text-red-600' : 'text-gray-800' }}">{{ \Carbon\Carbon::parse($dueDate, $tz)->locale('th')->isoFormat('D MMM YYYY') }}</span>
                                        @endif
                                    </p>
                                    @if(!empty($assignment['notes']))
                                        <p class="text-sm text-gray-500">{{ $assignment['notes'] }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('course.detail', ['course' => $course->id, 'term' => $currentTerm]) }}#assignments"
                                   class="text-sm text-blue-600 hover:underline">
                                    แก้ไข / ลบ
                                </a>
                            </div>
                            <div class="mt-3">
                                <button type="button"
                                        class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-800 hover:bg-blue-50 hover:text-blue-700 border border-gray-200"
                                        data-assignment-status="{{ json_encode([
                                            'title' => $assignment['title'] ?? '-',
                                            'id' => $assignment['id'] ?? null,
                                            'score' => $assignment['score'] ?? null,
                                            'due_date' => $assignment['due_date'] ?? null,
                                        ]) }}">
                                    ดูสถานะการส่ง
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endunless
</div>

{{-- Modal: Assignment status by student --}}
<div id="assignmentStatusModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-50 items-center justify-center px-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 id="assignmentStatusTitle" class="text-lg font-semibold text-gray-900">สถานะการส่ง</h3>
                <p id="assignmentStatusSubtitle" class="text-sm text-gray-500"></p>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700" data-close-assignment-modal>&times;</button>
        </div>
        <div id="assignmentStatusBody" class="p-6 max-h-[65vh] overflow-y-auto space-y-3"></div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200" data-close-assignment-modal>ปิด</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const courseSelector = document.getElementById('assignmentCourseSelector');
    courseSelector?.addEventListener('change', (e) => {
        const url = e.target.value;
        if (url) window.location.href = url;
    });

    // Assignment status modal
    const students = @json($studentsFlat->map(function($s){
        return [
            'code' => $s->student_code ?? '',
            'name' => trim(($s->title ?? '').' '.($s->first_name ?? '').' '.($s->last_name ?? '')),
            'room' => $s->classroom ?? $s->room ?? '',
        ];
    }));

    const modal = document.getElementById('assignmentStatusModal');
    const modalTitle = document.getElementById('assignmentStatusTitle');
    const modalSubtitle = document.getElementById('assignmentStatusSubtitle');
    const modalBody = document.getElementById('assignmentStatusBody');

    const renderStatus = (assignment) => {
        if (!modalBody) return;
        modalBody.innerHTML = '';

        if (!students.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-gray-500 text-center';
            empty.textContent = 'ยังไม่มีข้อมูลนักเรียนในห้อง';
            modalBody.appendChild(empty);
            return;
        }

        students.forEach((s) => {
            const row = document.createElement('div');
            row.className = 'border border-gray-100 rounded-xl px-4 py-3 flex items-center justify-between gap-3';

            const info = document.createElement('div');
            info.innerHTML = `
                <p class="text-sm font-semibold text-gray-900">${s.code || '-'}</p>
                <p class="text-sm text-gray-600">${s.name || '-'}</p>
                <p class="text-xs text-gray-400">${s.room || '-'}</p>
            `;

            const status = document.createElement('span');
            status.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-700';
            status.textContent = 'ยังไม่ทราบการส่ง';

            row.appendChild(info);
            row.appendChild(status);
            modalBody.appendChild(row);
        });
    };

    const openModal = (assignment) => {
        if (!modal) return;
        modalTitle.textContent = assignment?.title || 'สถานะการส่ง';
        modalSubtitle.textContent = `นักเรียนทั้งหมด ${students.length} คน`;
        renderStatus(assignment);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    document.querySelectorAll('[data-assignment-status]').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = btn.dataset.assignmentStatus ? JSON.parse(btn.dataset.assignmentStatus) : {};
            openModal(data);
        });
    });

    document.querySelectorAll('[data-close-assignment-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
        });
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});
</script>
@endpush
@endsection
