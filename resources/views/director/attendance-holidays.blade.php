@extends('layouts.layout-director')

@section('title', 'กำหนดวันหยุด')

@php
    $tz = config('app.timezone', 'Asia/Bangkok');
    $selectedMonth = $selectedMonth ?? now($tz)->format('Y-m');
    $holidayPreview = collect($holidayPreview ?? []);
    $holidayLookup = $holidayPreview
        ->filter(fn ($holidayItem) => filled($holidayItem->holiday_date ?? null))
        ->keyBy(function ($holidayItem) {
            $holidayDate = $holidayItem->holiday_date;

            if ($holidayDate instanceof \Illuminate\Support\Carbon) {
                return $holidayDate->toDateString();
            }

            return \Illuminate\Support\Carbon::parse($holidayDate)->toDateString();
        });

    $monthBase = \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedMonth, $tz)->startOfMonth();
    $previewMonthLabel = $monthBase->copy()->locale('th')->isoFormat('MMMM YYYY');
    $calendarStart = $monthBase->copy()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
    $calendarEnd = $monthBase->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SATURDAY);
    $calendarDays = collect();
    $cursor = $calendarStart->copy();

    while ($cursor->lte($calendarEnd)) {
        $calendarDays->push($cursor->copy());
        $cursor->addDay();
    }

    $calendarWeeks = $calendarDays->chunk(7);

    $selectionStart = filled($startDate ?? null) && str_starts_with((string) $startDate, $selectedMonth) ? $startDate : null;
    $selectionEnd = filled($endDate ?? null) && str_starts_with((string) $endDate, $selectedMonth) ? $endDate : $selectionStart;
    $rangeLabel = $selectionStart
        ? \Illuminate\Support\Carbon::parse($selectionStart)->addYears(543)->locale('th')->isoFormat('D MMM YYYY')
        : 'ยังไม่ได้เลือกช่วงวันที่';

    if ($selectionStart && $selectionEnd && $selectionStart !== $selectionEnd) {
        $rangeLabel .= ' - ' . \Illuminate\Support\Carbon::parse($selectionEnd)->addYears(543)->locale('th')->isoFormat('D MMM YYYY');
    }

    $weekdayLabels = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
@endphp

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
    <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow">
        <h2 class="text-3xl font-bold text-gray-900">กำหนดวันหยุด</h2>
        <p class="mt-2 text-gray-600">
            เลือกช่วงวันจากปฏิทินเดือนเดียวได้เลย โดยคลิกวันเริ่มต้นและวันสิ้นสุด ระบบจะบันทึกเฉพาะวันจันทร์-ศุกร์ให้อัตโนมัติ
        </p>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6 rounded-3xl border border-gray-100 bg-white p-8 shadow">
        <form id="holidayMonthlyPreviewForm" action="{{ route('director.attendance-holidays') }}" method="GET" class="grid grid-cols-1 items-end gap-4 md:grid-cols-3">
            <div>
                <label for="preview_month" class="mb-1 block text-sm font-semibold text-gray-700">เลือกเดือนที่ต้องการดู</label>
                <input
                    id="preview_month"
                    type="month"
                    name="month"
                    value="{{ $selectedMonth }}"
                    class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div>
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-slate-800 px-5 py-2.5 text-white shadow transition hover:bg-slate-900"
                >
                    แสดงรายการทั้งเดือน
                </button>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                กำลังแสดงข้อมูลเดือน <span class="font-semibold text-slate-900">{{ $previewMonthLabel }}</span>
            </div>
        </form>

        <form method="POST" action="{{ route('director.attendance-holidays.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="action" value="holiday">
            <input type="hidden" name="start_date" id="selected_start_date" value="{{ $selectionStart }}">
            <input type="hidden" name="end_date" id="selected_end_date" value="{{ $selectionEnd }}">

            <div id="holidayWeekendWarning" class="hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                วันเสาร์-อาทิตย์เป็นวันหยุดอัตโนมัติ กรุณาเลือกเฉพาะวันจันทร์-ศุกร์
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                ช่วงวันที่ที่เลือก: <span id="holidaySelectedRangeText" class="font-semibold">{{ $rangeLabel }}</span>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                <div class="grid border-b border-slate-200 bg-slate-100 text-center text-sm font-semibold text-slate-700" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                    @foreach($weekdayLabels as $weekdayLabel)
                        <div class="px-2 py-3">{{ $weekdayLabel }}</div>
                    @endforeach
                </div>

                <div class="divide-y divide-slate-200">
                    @foreach($calendarWeeks as $week)
                        <div class="grid" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                            @foreach($week as $day)
                                @php
                                    $isCurrentMonth = $day->format('Y-m') === $selectedMonth;
                                    $isWeekend = $day->isWeekend();
                                    $dayValue = $day->toDateString();
                                    $calendarHoliday = $isCurrentMonth ? $holidayLookup->get($dayValue) : null;
                                    $calendarHolidayName = trim((string) ($calendarHoliday->holiday_name ?? ''));
                                    $calendarHolidayLabel = $calendarHolidayName !== '' ? $calendarHolidayName : 'วันหยุด';
                                    $isWeekendHoliday = in_array($calendarHolidayName, ['วันเสาร์', 'วันอาทิตย์'], true);
                                    $cellClasses = $isCurrentMonth ? 'bg-white hover:bg-slate-50 text-slate-900' : 'bg-slate-50 text-slate-300';

                                    if ($isWeekend && $isCurrentMonth) {
                                        $cellClasses .= ' bg-amber-50/70 text-amber-700';
                                    }

                                    if ($calendarHoliday && $isCurrentMonth && ! $isWeekendHoliday) {
                                        $cellClasses .= ' bg-sky-50/80 ring-1 ring-inset ring-sky-100';
                                    }
                                @endphp
                                <button
                                    type="button"
                                    class="holiday-calendar-day relative min-h-24 w-full border-r border-slate-200 px-3 py-3 text-left align-top transition last:border-r-0 {{ $cellClasses }}"
                                    data-date="{{ $dayValue }}"
                                    data-current-month="{{ $isCurrentMonth ? 'true' : 'false' }}"
                                    data-weekend="{{ $isWeekend ? 'true' : 'false' }}"
                                    @disabled(! $isCurrentMonth)
                                >
                                    <span class="block text-sm font-semibold">{{ $day->day }}</span>

                                    @if($isCurrentMonth && $calendarHoliday)
                                        <span class="mt-2 inline-flex max-w-full rounded-full px-2 py-1 text-[11px] font-medium {{ $isWeekendHoliday ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }}">
                                            <span class="truncate">{{ $calendarHolidayLabel }}</span>
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-sm text-gray-500">
                เลือกวันแรกและวันสุดท้ายจากตารางเดียวกัน ถ้าคลิกวันใหม่อีกครั้ง ระบบจะเริ่มเลือกช่วงใหม่ให้
            </p>

            <div>
                <label for="holiday_name" class="mb-1 block text-sm font-semibold text-gray-700">ชื่อวันหยุด</label>
                <input
                    id="holiday_name"
                    type="text"
                    name="holiday_name"
                    value="{{ old('holiday_name') }}"
                    maxlength="255"
                    class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-amber-500"
                    placeholder="เช่น วันหยุดราชการ / โรงเรียนหยุด / ลากิจ"
                >
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button
                    type="submit"
                    class="rounded-2xl bg-amber-500 px-5 py-2.5 text-white shadow transition hover:bg-amber-600"
                >
                    บันทึกวันหยุด
                </button>
            </div>
        </form>

        <div>
            <h3 class="mb-3 text-lg font-semibold text-gray-900">วันหยุดในเดือน {{ $previewMonthLabel }}</h3>

            @if($holidayPreview->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-500">
                    ยังไม่มีการกำหนดวันหยุดในเดือนนี้
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="w-52 border border-slate-200 px-4 py-3 text-left text-sm font-semibold">วันที่</th>
                                    <th class="w-64 border border-slate-200 px-4 py-3 text-left text-sm font-semibold">วันหยุด</th>
                                    <th class="border border-slate-200 px-4 py-3 text-left text-sm font-semibold">รายละเอียด</th>
                                    <th class="w-40 border border-slate-200 px-4 py-3 text-center text-sm font-semibold">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($holidayPreview as $holidayItem)
                                    @php
                                        $holidayNameLabel = trim((string) ($holidayItem->holiday_name ?? '')) !== '' ? $holidayItem->holiday_name : 'ไม่ได้ระบุชื่อวันหยุด';
                                        $holidayNoteLabel = trim((string) ($holidayItem->note ?? '')) !== '' ? $holidayItem->note : '-';
                                        $isWeeklyHoliday = in_array(trim((string) ($holidayItem->holiday_name ?? '')), ['วันเสาร์', 'วันอาทิตย์'], true);
                                        $holidayDateValue = $holidayItem->holiday_date instanceof \Illuminate\Support\Carbon
                                            ? $holidayItem->holiday_date->toDateString()
                                            : $holidayItem->holiday_date;
                                        $holidayEditKey = 'holiday-edit-' . str_replace('-', '', $holidayDateValue);
                                    @endphp
                                    <tr class="odd:bg-white even:bg-slate-50/70">
                                        <td class="border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">
                                            {{ \Illuminate\Support\Carbon::parse($holidayDateValue)->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}
                                        </td>
                                        <td class="border border-slate-200 px-4 py-3 text-sm text-slate-800">
                                            @if($isWeeklyHoliday)
                                                {{ $holidayNameLabel }}
                                            @else
                                                <div data-holiday-display="{{ $holidayEditKey }}">
                                                    {{ $holidayNameLabel }}
                                                </div>
                                                <div class="hidden" data-holiday-editor="{{ $holidayEditKey }}">
                                                    <input
                                                        type="text"
                                                        name="holiday_name"
                                                        form="{{ $holidayEditKey }}"
                                                        value="{{ trim((string) ($holidayItem->holiday_name ?? '')) }}"
                                                        maxlength="255"
                                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500"
                                                        placeholder="ระบุชื่อวันหยุด"
                                                    >
                                                </div>
                                            @endif
                                        </td>
                                        <td class="border border-slate-200 px-4 py-3 text-sm text-slate-600">
                                            {{ $holidayNoteLabel }}
                                        </td>
                                        <td class="border border-slate-200 px-4 py-3 text-center">
                                            @if($isWeeklyHoliday)
                                                <span class="text-xs font-medium text-slate-400">{{ $holidayNameLabel }}</span>
                                            @else
                                                <button
                                                    type="button"
                                                    class="w-full min-w-[120px] rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-center text-sky-700 transition hover:bg-sky-100"
                                                    data-holiday-open="{{ $holidayEditKey }}"
                                                >
                                                    แก้ไข
                                                </button>

                                                <form id="{{ $holidayEditKey }}" method="POST" action="{{ route('director.attendance-holidays.store') }}" class="hidden mt-2 space-y-2" data-holiday-actions="{{ $holidayEditKey }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="holiday">
                                                    <input type="hidden" name="start_date" value="{{ $holidayDateValue }}">
                                                    <input type="hidden" name="end_date" value="{{ $holidayDateValue }}">
                                                    <button
                                                        type="submit"
                                                        class="w-full min-w-[120px] rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-center text-amber-700 transition hover:bg-amber-100"
                                                    >
                                                        บันทึกชื่อ
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="w-full min-w-[120px] rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-slate-600 transition hover:bg-slate-50"
                                                        data-holiday-cancel="{{ $holidayEditKey }}"
                                                    >
                                                        ยกเลิก
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('director.attendance-holidays.store') }}" class="mt-2" data-holiday-delete-form>
                                                    @csrf
                                                    <input type="hidden" name="action" value="clear_holiday">
                                                    <input type="hidden" name="holiday_date" value="{{ $holidayDateValue }}">
                                                    <button
                                                        type="submit"
                                                        class="w-full min-w-[120px] rounded-2xl border border-red-200 bg-white px-4 py-2.5 text-center text-red-600 transition hover:bg-red-50"
                                                    >
                                                        ลบ
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const weekendWarning = document.getElementById('holidayWeekendWarning');
    const previewMonthInput = document.getElementById('preview_month');
    const holidayMonthlyPreviewForm = document.getElementById('holidayMonthlyPreviewForm');
    const selectedStartInput = document.getElementById('selected_start_date');
    const selectedEndInput = document.getElementById('selected_end_date');
    const selectedRangeText = document.getElementById('holidaySelectedRangeText');
    const dayButtons = document.querySelectorAll('.holiday-calendar-day[data-current-month="true"]');
    const holidayEditOpenButtons = document.querySelectorAll('[data-holiday-open]');
    const holidayEditCancelButtons = document.querySelectorAll('[data-holiday-cancel]');
    const holidayDeleteForms = document.querySelectorAll('[data-holiday-delete-form]');

    let startDate = selectedStartInput?.value || '';
    let endDate = selectedEndInput?.value || startDate;

    const parseLocalDate = (value) => {
        const parts = value.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) {
            return null;
        }

        return new Date(parts[0], parts[1] - 1, parts[2]);
    };

    const formatThaiDate = (value) => {
        const date = parseLocalDate(value);
        if (!date) {
            return 'ยังไม่ได้เลือกช่วงวันที่';
        }

        const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear() + 543}`;
    };

    const isWeekend = (value) => {
        const date = parseLocalDate(value);
        if (!date) {
            return false;
        }

        const day = date.getDay();
        return day === 0 || day === 6;
    };

    const showWeekendWarning = () => {
        if (!weekendWarning) {
            return;
        }

        weekendWarning.classList.remove('hidden');
        window.clearTimeout(showWeekendWarning.timeoutId);
        showWeekendWarning.timeoutId = window.setTimeout(() => {
            weekendWarning.classList.add('hidden');
        }, 3500);
    };

    const compareDates = (left, right) => {
        if (left === right) {
            return 0;
        }

        return left < right ? -1 : 1;
    };

    const updateRangeLabel = () => {
        if (!selectedRangeText) {
            return;
        }

        if (!startDate) {
            selectedRangeText.textContent = 'ยังไม่ได้เลือกช่วงวันที่';
            return;
        }

        if (!endDate || startDate === endDate) {
            selectedRangeText.textContent = formatThaiDate(startDate);
            return;
        }

        selectedRangeText.textContent = `${formatThaiDate(startDate)} - ${formatThaiDate(endDate)}`;
    };

    const paintSelection = () => {
        dayButtons.forEach((button) => {
            const date = button.dataset.date || '';
            button.classList.remove('bg-blue-600', 'text-white', 'ring-2', 'ring-blue-200', 'bg-blue-50', 'text-blue-800');

            if (!startDate || !date || button.dataset.weekend === 'true') {
                return;
            }

            const rangeEnd = endDate || startDate;
            if (compareDates(date, startDate) >= 0 && compareDates(date, rangeEnd) <= 0) {
                if (date === startDate || date === rangeEnd) {
                    button.classList.add('bg-blue-600', 'text-white', 'ring-2', 'ring-blue-200');
                } else {
                    button.classList.add('bg-blue-50', 'text-blue-800');
                }
            }
        });
    };

    const syncHiddenInputs = () => {
        if (selectedStartInput) {
            selectedStartInput.value = startDate;
        }

        if (selectedEndInput) {
            selectedEndInput.value = endDate || startDate;
        }
    };

    const setHolidayEditMode = (key, editing) => {
        const display = document.querySelector(`[data-holiday-display="${key}"]`);
        const editor = document.querySelector(`[data-holiday-editor="${key}"]`);
        const actions = document.querySelector(`[data-holiday-actions="${key}"]`);
        const openButton = document.querySelector(`[data-holiday-open="${key}"]`);

        if (display) {
            display.classList.toggle('hidden', editing);
        }

        if (editor) {
            editor.classList.toggle('hidden', !editing);
        }

        if (actions) {
            actions.classList.toggle('hidden', !editing);
        }

        if (openButton) {
            openButton.classList.toggle('hidden', editing);
        }

        if (editing) {
            const input = editor?.querySelector('input[name="holiday_name"]');
            input?.focus();
            input?.select();
        }
    };

    dayButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const date = button.dataset.date || '';

            if (!date) {
                return;
            }

            if (button.dataset.weekend === 'true' || isWeekend(date)) {
                showWeekendWarning();
                return;
            }

            if (!startDate || (startDate && endDate)) {
                startDate = date;
                endDate = '';
            } else if (compareDates(date, startDate) < 0) {
                endDate = startDate;
                startDate = date;
            } else {
                endDate = date;
            }

            syncHiddenInputs();
            updateRangeLabel();
            paintSelection();
        });
    });

    holidayEditOpenButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setHolidayEditMode(button.dataset.holidayOpen, true);
        });
    });

    holidayEditCancelButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setHolidayEditMode(button.dataset.holidayCancel, false);
        });
    });

    holidayDeleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const confirmed = window.confirm('ยืนยันการลบวันหยุดนี้หรือไม่');

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    if (previewMonthInput && holidayMonthlyPreviewForm) {
        previewMonthInput.addEventListener('change', () => {
            if (!previewMonthInput.value) {
                return;
            }

            holidayMonthlyPreviewForm.submit();
        });
    }

    syncHiddenInputs();
    updateRangeLabel();
    paintSelection();
});
</script>
@endpush
