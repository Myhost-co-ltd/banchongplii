@php
    $termLabel = $selectedTerm === '2' ? 'ภาคเรียนที่ 2' : 'ภาคเรียนที่ 1';
    $fontTHSarabunRegular    = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Regular.ttf'));
    $fontTHSarabunBold       = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Bold.ttf'));
    $fontTHSarabunItalic     = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Italic.ttf'));
    $fontTHSarabunBoldItalic = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-BoldItalic.ttf'));
    $fontRegular             = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf'));
    $fontBold                = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf'));
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>รายงานรายละเอียดรายวิชา - {{ $course->name }}</title>
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

        body {
            font-family: 'THSarabunNew', 'TH Sarabun New', 'LeelawUI', 'Sarabun', 'Tahoma', 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #111827;
            line-height: 1.6;
        }

@page {
    margin: 60px 40px 60px;
}

        .section { margin-bottom: 22px; }
.muted { font-size: 12px; color: #000; }

.pill {
            display: inline-block;
            color: black;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            margin-right: 4px;
}

.wrap { max-width: 720px; margin: 0 auto; }
table {
    width: 92%;
            border-collapse: collapse;
            margin: 6px auto 0 auto;
        }

        th {
            background: #e5e7eb;
            color: #000;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            text-align: center;
            font-size: 15px;
            line-height: 1.6;
        }

        td {
            padding: 5px 6px;
            border: 1px solid #e5e7eb;
            text-align: center;
            font-size: 15px;
            line-height: 1.35;
        }

        h1,h2,h3 { margin: 0 0 6px 0; }

        table, tr, td, th { page-break-inside: avoid; }
        .section { page-break-inside: avoid; }
    </style>
</head>

<body>
<div class="wrap">
<h1 style="font-size:18px; font-weight:700; text-align:center; margin:0 0 10px 0;">รายงานรายละเอียดรายวิชา</h1>

<p style="font-size:15px; font-weight:700; margin:0 0 6px 0;">
    ผู้สอน: {{ $teacher->name ?? '-' }}
</p>

<div class="section">
    <h2 style="font-size:17px; font-weight:700;">{{ $course->name }}</h2>

    {{-- ✅ บรรทัดที่ 1: ข้อมูลหลัก --}}
    <p style="margin:6px 0 6px 0;">
        <span>ระดับชั้น: **{{ $course->grade ?? '-' }}**</span>
        <span style="margin-left: 20px;">ปีการศึกษา: **{{ $course->year ?? '-' }}**</span>
        <span style="margin-left: 20px;">ภาคเรียน: **{{ $termLabel }}**</span>
    </p>

    {{-- ✅ บรรทัดที่ 2: ห้องเรียน (แยกออกมา) --}}
    <p style="margin:0;">
        <span style="font-weight:700; vertical-align:top; margin-right: 10px;">ห้องเรียน:</span>
        @forelse($course->rooms ?? [] as $room)
            <span class="pill" style="vertical-align:middle; ">{{ $room }}</span>
        @empty
            <span class="muted">-</span>
        @endforelse
    </p>
</div>

<div class="section">
    <table>
        <thead>
            <tr>
                <th>หมวด</th>
                <th>ชั่วโมงเป้าหมาย</th>
                <th>ชั่วโมงที่ใช้</th>
                <th>เหลือ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessonCapacity as $category => $meta)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $meta['allowed'] ?? '-' }}</td>
                    <td>{{ $meta['used'] ?? '-' }}</td>
                    <td>{{ $meta['remaining'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">ยังไม่มีข้อมูลชั่วโมงสอน</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h3>แผนการสอน</h3>
    <table>
        <thead>
            <tr>
                <th>หัวข้อ</th>
                <th>หมวด</th>
                <th>ชั่วโมง</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessons as $lesson)
                <tr>
                    <td>{{ $lesson['title'] ?? '-' }}</td>
                    <td>{{ $lesson['category'] ?? '-' }}</td>
                    <td>{{ $lesson['hours'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">ยังไม่มีแผนการสอน</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h3>งาน / แบบฝึกหัด </h3>

    <table>
        <thead>
            <tr>
                <th>ชื่อรายการ</th>
                <th>คะแนน</th>
                <th>กำหนดส่ง</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment['title'] ?? '-' }}</td>
                    <td>{{ $assignment['score'] ?? '-' }}</td>
                    <td>
                        @if(!empty($assignment['due_date']))
                            {{ \Illuminate\Support\Carbon::parse($assignment['due_date'])->timezone('Asia/Bangkok')->addYears(543)->locale('th')->isoFormat('D MMM YYYY') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">ยังไม่มีงานหรือแบบฝึกหัด</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="muted">
        คะแนนรวม: {{ $assignmentTotal }} |
        คะแนนที่เหลือ: {{ $assignmentRemaining }}
    </p>
</div>
</div>

</body>
</html>
