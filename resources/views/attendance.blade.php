@extends('layouts.layout')

@section('title', 'บันทึกเวลาเรียน | โรงเรียนบ้านช่องพลี')

@section('content')
@php
    $contextTeacherName = trim((string) request('teacher_name', ''));
    $contextCourseName = trim((string) request('course_name', ''));

    $NUM_WEEKS = 5;
    $NUM_HOURS = $NUM_WEEKS * 6;
    $months = ['พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม'];

    $students = [
        ['no' => 1, 'id' => 2997, 'name' => 'นายเจนวิทย์ บุตรหมัน'],
        ['no' => 2, 'id' => 3006, 'name' => 'นายปภาวิน สายนุ้ย'],
        ['no' => 3, 'id' => 3366, 'name' => 'นายนัฐคินว์ จงรักษ์'],
        ['no' => 4, 'id' => 4474, 'name' => 'นายอนุชิต โล่เสื้อ'],
        ['no' => 5, 'id' => 2706, 'name' => 'น.ส.ชนากานต์ ป้องปิด'],
    ];
@endphp

<div id="attendancePage" class="h-full flex flex-col gap-5 overflow-hidden">
    <section class="relative overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-cyan-50 p-5 md:p-6">
        <div class="pointer-events-none absolute -top-14 -right-14 h-40 w-40 rounded-full bg-blue-200/30 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-14 -left-12 h-36 w-36 rounded-full bg-cyan-200/30 blur-2xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700/80">Attendance</p>
                <h2 class="text-3xl font-bold text-slate-900">บันทึกเวลาเรียน</h2>
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span class="rounded-full bg-white/80 px-3 py-1 ring-1 ring-slate-200">นักเรียน {{ number_format(count($students)) }} คน</span>
                    <span class="rounded-full bg-white/80 px-3 py-1 ring-1 ring-slate-200">ชั่วโมงรวม {{ number_format($NUM_HOURS) }} ช่อง</span>
                </div>
                @if ($contextTeacherName !== '' || $contextCourseName !== '')
                    <p class="text-sm text-slate-700">
                        ครู: <span class="font-semibold">{{ $contextTeacherName !== '' ? $contextTeacherName : '-' }}</span>
                        <span class="mx-2 text-slate-300">|</span>
                        วิชา: <span class="font-semibold">{{ $contextCourseName !== '' ? $contextCourseName : '-' }}</span>
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <button id="exportExcel" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-500">
                    ส่งออก Excel
                </button>
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-600">
                    บันทึกทั้งหมด
                </button>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-2 text-xs md:text-sm">
        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">ม = มาเรียน</span>
        <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-700">ข = ขาด</span>
        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">ล = ลา</span>
        <span class="rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-violet-700">ป = ป่วย</span>
    </div>

    <section class="attendance-table-shell min-h-0 flex-1 rounded-3xl border border-slate-200 bg-white p-2 md:p-3">
        <div class="attendance-table-scroll h-full overflow-auto rounded-2xl">
            <table id="attendanceTable" class="w-max min-w-full border-collapse text-sm text-center">
                <thead class="sticky top-0 z-20 shadow-sm">
                    <tr class="text-white">
                        <th rowspan="4" class="sticky-col-1 bg-blue-800 p-2">เลขที่</th>
                        <th rowspan="4" class="sticky-col-2 bg-blue-800 p-2">เลขประจำตัว</th>
                        <th rowspan="4" class="sticky-col-3 bg-blue-800 p-2 w-64">ชื่อ - สกุล</th>

                        @for ($w = 1; $w <= $NUM_WEEKS; $w++)
                            <th colspan="6" class="bg-blue-700 p-1.5">สัปดาห์ที่ {{ $w }}</th>
                        @endfor

                        <th colspan="6" rowspan="3" class="bg-sky-900 p-1.5">สรุปผล</th>
                    </tr>

                    <tr class="text-white">
                        @for ($w = 1; $w <= $NUM_WEEKS; $w++)
                            <th colspan="6" class="bg-blue-600 p-1.5">
                                {{ $months[($w - 1) % count($months)] }}
                            </th>
                        @endfor
                    </tr>

                    <tr class="text-white text-xs">
                        @for ($i = 1; $i <= $NUM_HOURS; $i++)
                            <th class="bg-sky-500 p-1">#</th>
                        @endfor
                        <th class="bg-sky-700 p-1">มา</th>
                        <th class="bg-sky-700 p-1">ขาด</th>
                        <th class="bg-sky-700 p-1">ลา</th>
                        <th class="bg-sky-700 p-1">ป่วย</th>
                        <th class="bg-sky-700 p-1">%มาเรียน</th>
                        <th class="bg-sky-700 p-1">สถานะ</th>
                    </tr>
                </thead>

                <tbody class="text-slate-700">
                    @foreach ($students as $s)
                        <tr class="transition hover:bg-blue-50/60">
                            <td class="sticky-col-1 bg-white p-2 font-semibold">{{ $s['no'] }}</td>
                            <td class="sticky-col-2 bg-white p-2">{{ $s['id'] }}</td>
                            <td class="sticky-col-3 bg-white p-2 text-left">{{ $s['name'] }}</td>

                            @for ($i = 1; $i <= $NUM_HOURS; $i++)
                                <td class="p-1">
                                    <input type="text" maxlength="1" placeholder="-"
                                           class="attendance-input" />
                                </td>
                            @endfor

                            <td class="font-semibold text-emerald-600">34</td>
                            <td class="font-semibold text-rose-600">6</td>
                            <td class="font-semibold text-amber-600">-</td>
                            <td class="font-semibold text-violet-600">-</td>
                            <td class="font-semibold text-blue-700">85%</td>
                            <td>
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    ปกติ
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    const exportBtn = document.getElementById('exportExcel');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const wb = XLSX.utils.table_to_book(document.getElementById('attendanceTable'));
            XLSX.writeFile(wb, 'บันทึกเวลาเรียน.xlsx');
        });
    }

    document.querySelectorAll('.attendance-input').forEach((input) => {
        input.addEventListener('input', () => {
            const hasValue = input.value.trim() !== '';
            input.classList.toggle('attendance-input--filled', hasValue);
        });
    });
</script>

<style>
    #attendancePage .attendance-table-shell {
        box-shadow: 0 12px 30px -20px rgba(15, 23, 42, 0.35);
    }

    #attendanceTable th,
    #attendanceTable td {
        border: 1px solid #d9e2ef;
        white-space: nowrap;
    }

    #attendanceTable .sticky-col-1,
    #attendanceTable .sticky-col-2,
    #attendanceTable .sticky-col-3 {
        position: sticky;
        z-index: 10;
    }

    #attendanceTable .sticky-col-1 {
        left: 0;
        min-width: 56px;
    }

    #attendanceTable .sticky-col-2 {
        left: 56px;
        min-width: 132px;
    }

    #attendanceTable .sticky-col-3 {
        left: 188px;
        min-width: 260px;
        max-width: 260px;
    }

    #attendancePage .attendance-input {
        width: 2rem;
        height: 1.5rem;
        border: 1px solid #c7d2e4;
        border-radius: 0.45rem;
        text-align: center;
        font-size: 0.75rem;
        color: #0f172a;
        background: #f8fafc;
        transition: all 0.15s ease;
    }

    #attendancePage .attendance-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        background: #ffffff;
    }

    #attendancePage .attendance-input--filled {
        background: #eff6ff;
        border-color: #60a5fa;
        font-weight: 700;
    }

    #attendancePage .attendance-table-scroll::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }

    #attendancePage .attendance-table-scroll::-webkit-scrollbar-thumb {
        background-color: #94a3b8;
        border-radius: 999px;
    }

    #attendancePage .attendance-table-scroll::-webkit-scrollbar-track {
        background-color: #e2e8f0;
        border-radius: 999px;
    }

    @media (max-width: 768px) {
        #attendanceTable .sticky-col-1 {
            min-width: 48px;
        }

        #attendanceTable .sticky-col-2 {
            left: 48px;
            min-width: 108px;
        }

        #attendanceTable .sticky-col-3 {
            left: 156px;
            min-width: 220px;
            max-width: 220px;
        }
    }
</style>
@endsection
