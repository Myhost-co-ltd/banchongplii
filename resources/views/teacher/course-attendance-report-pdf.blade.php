@php
    $fontTHSarabunRegular = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Regular.ttf'));
    $fontTHSarabunBold = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Bold.ttf'));
    $fontTHSarabunItalic = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Italic.ttf'));
    $fontTHSarabunBoldItalic = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-BoldItalic.ttf'));

    $tz = config('app.timezone', 'Asia/Bangkok');
    $studentsByRoom = collect($studentsByRoom ?? []);
    $assignedRooms = collect($assignedRooms ?? []);
    $monthDates = collect($monthDates ?? []);
    $attendanceGrid = $attendanceGrid ?? [];
    $studentStatusSummary = $studentStatusSummary ?? [];
    $statusSummary = array_merge([
        'present' => 0,
        'late' => 0,
        'leave' => 0,
        'absent' => 0,
    ], $statusSummary ?? []);

    $statusShortLabels = [
        'present' => 'ม',
        'late' => 'ส',
        'leave' => 'ล',
        'absent' => 'ข',
    ];

    $statusSummaryTemplate = [
        'present' => 0,
        'late' => 0,
        'leave' => 0,
        'absent' => 0,
    ];

    $gridDateCount = max(1, $monthDates->count());
    $gridColspan = 7 + $gridDateCount;
    $selectedRoomText = trim((string) ($selectedRoom ?? '')) !== ''
        ? trim((string) ($selectedRoom ?? ''))
        : ($assignedRooms->isNotEmpty() ? $assignedRooms->join(', ') : 'ทุกห้อง');
    $termLabel = match ((string) ($selectedTerm ?? '1')) {
        '2' => 'ภาคเรียนที่ 2',
        'summer' => 'ภาคฤดูร้อน',
        default => 'ภาคเรียนที่ 1',
    };
    $generatedAt = ($generatedAt ?? now($tz))->timezone($tz);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>รายงานเช็คชื่อรายเดือน - {{ $course->name ?? '-' }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontTHSarabunRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontTHSarabunBold }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: 400;
            src: url('{{ $fontTHSarabunItalic }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: italic;
            font-weight: 700;
            src: url('{{ $fontTHSarabunBoldItalic }}') format('truetype');
        }

        @page {
            margin: 26px 20px;
        }

        body {
            font-family: 'THSarabunNew', 'TH Sarabun New', 'Tahoma', 'DejaVu Sans', sans-serif;
            color: #111827;
            font-size: 13px;
            line-height: 1.35;
        }

        h1, h2, p {
            margin: 0;
        }

        .report-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .report-meta {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .report-meta p {
            margin-bottom: 2px;
        }

        .report-summary {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .report-summary span {
            margin-right: 12px;
        }

        .room-block {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .room-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            line-height: 1.2;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .text-left {
            text-align: left;
        }

        .w-no {
            width: 28px;
        }

        .w-code {
            width: 80px;
        }

        .w-name {
            width: 170px;
        }

        .w-total {
            width: 34px;
        }

        .status-cell {
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h1 class="report-title">รายงานเช็คชื่อรายเดือน (Grid)</h1>

    <div class="report-meta">
        <p>วิชา: {{ $course->name ?? '-' }} | ครูผู้สอน: {{ $teacher->name ?? '-' }}</p>
        <p>{{ $termLabel }} | เดือน: {{ $reportMonthLabel ?? '-' }} | ห้อง: {{ $selectedRoomText }}</p>
        <p>พิมพ์เมื่อ: {{ $generatedAt->addYears(543)->locale('th')->isoFormat('D MMMM YYYY HH:mm') }}</p>
    </div>

    <div class="report-summary">
        <span>นักเรียนทั้งหมด: {{ number_format((int) $studentsByRoom->flatten(1)->count()) }} คน</span>
        <span>มีข้อมูลบันทึก: {{ number_format((int) ($recordedCount ?? 0)) }}</span>
        <span>มา {{ number_format((int) ($statusSummary['present'] ?? 0)) }}</span>
        <span>สาย {{ number_format((int) ($statusSummary['late'] ?? 0)) }}</span>
        <span>ลา {{ number_format((int) ($statusSummary['leave'] ?? 0)) }}</span>
        <span>ขาด {{ number_format((int) ($statusSummary['absent'] ?? 0)) }}</span>
    </div>

    @forelse($studentsByRoom as $room => $students)
        <div class="room-block">
            <h2 class="room-title">ห้อง {{ $room ?: '-' }} ({{ number_format((int) collect($students)->count()) }} คน)</h2>

            <table>
                <thead>
                    <tr>
                        <th class="w-no" rowspan="2">ลำดับ</th>
                        <th class="w-code" rowspan="2">รหัส</th>
                        <th class="w-name" rowspan="2">ชื่อ - สกุล</th>
                        <th colspan="{{ $gridDateCount }}">วันที่ ({{ $reportMonthLabel ?? '-' }})</th>
                        <th colspan="4">สรุปทั้งเดือน</th>
                    </tr>
                    <tr>
                        @forelse($monthDates as $dateKey)
                            @php
                                $dateObj = \Illuminate\Support\Carbon::parse($dateKey, $tz);
                            @endphp
                            <th>{{ $dateObj->format('j') }}</th>
                        @empty
                            <th>-</th>
                        @endforelse
                        <th class="w-total">มา</th>
                        <th class="w-total">สาย</th>
                        <th class="w-total">ลา</th>
                        <th class="w-total">ขาด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $fullName = trim(($student->title ?? '') . ' ' . ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                            $dailyStatuses = $attendanceGrid[$student->id] ?? [];
                            $rowSummary = array_merge($statusSummaryTemplate, $studentStatusSummary[$student->id] ?? []);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->student_code ?? '-' }}</td>
                            <td class="text-left">{{ $fullName !== '' ? $fullName : '-' }}</td>
                            @forelse($monthDates as $dateKey)
                                @php
                                    $statusCode = trim((string) ($dailyStatuses[$dateKey] ?? ''));
                                    $statusLabel = $statusShortLabels[$statusCode] ?? '-';
                                @endphp
                                <td class="status-cell">{{ $statusLabel }}</td>
                            @empty
                                <td>-</td>
                            @endforelse
                            <td>{{ number_format((int) ($rowSummary['present'] ?? 0)) }}</td>
                            <td>{{ number_format((int) ($rowSummary['late'] ?? 0)) }}</td>
                            <td>{{ number_format((int) ($rowSummary['leave'] ?? 0)) }}</td>
                            <td>{{ number_format((int) ($rowSummary['absent'] ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $gridColspan }}">ยังไม่มีนักเรียนในห้องนี้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <p>ยังไม่พบนักเรียนในห้องที่ผูกกับหลักสูตรนี้</p>
    @endforelse
</body>
</html>
