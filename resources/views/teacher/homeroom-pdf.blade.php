@php
    $fontTHSarabunRegular     = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Regular.ttf'));
    $fontTHSarabunBold        = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Bold.ttf'));
    $fontTHSarabunItalic      = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Italic.ttf'));
    $fontTHSarabunBoldItalic  = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-BoldItalic.ttf'));
    $fontRegular              = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Regular.ttf'));
    $fontBold                 = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Bold.ttf'));
    $fontNotoRegular          = 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Regular.ttf'));
    $fontNotoBold             = 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Bold.ttf'));

    $roomsList   = ($rooms ?? collect())->filter();
    $printedAt   = ($generatedAt ?? now())->timezone('Asia/Bangkok');
    $printedAtTh = $printedAt->copy()->addYears(543)->format('d/m/Y H:i');
    $logoFile    = public_path('images/school-logo.png');
    $logoPath    = file_exists($logoFile) ? ('file:///' . str_replace('\\', '/', $logoFile)) : null;

    $normalizeRoom = function ($item) {
        if (is_string($item) && str_starts_with(trim($item), '[')) {
            $decoded = json_decode($item, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $item = $decoded;
            }
        } elseif (is_object($item)) {
            $item = (array) $item;
        }

        if (is_array($item)) {
            if (array_key_exists('room', $item)) {
                $item = $item['room'];
            } elseif (array_key_exists('name', $item)) {
                $item = $item['name'];
            } else {
                // Pull a value from common array shapes like [0, 1, 2] or nested arrays
                $item = $item[2] ?? $item[1] ?? $item[0] ?? null;
            }
        }

        if ($item === null) {
            return null;
        }

        // Clean leading/trailing whitespace and stray punctuation so values don't render as "." in the PDF
        $val = trim((string) $item);
        $val = trim($val, " .,\t\n\r\0\x0B");
        return $val === '' ? null : $val;
    };

    $roomsList = collect($roomsList)->map($normalizeRoom)->filter();
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-weight: 400;
            font-style: normal;
            src: url('{{ $fontTHSarabunRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-weight: 700;
            font-style: normal;
            src: url('{{ $fontTHSarabunBold }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-weight: 400;
            font-style: italic;
            src: url('{{ $fontTHSarabunItalic }}') format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-weight: 700;
            font-style: italic;
            src: url('{{ $fontTHSarabunBoldItalic }}') format('truetype');
        }
        @font-face {
            font-family: 'Sarabun';
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'Sarabun';
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-weight: 400;
            src: url('{{ $fontNotoRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-weight: 700;
            src: url('{{ $fontNotoBold }}') format('truetype');
        }

        body {
            font-family: 'THSarabunNew', 'TH Sarabun New', 'Sarabun', 'Noto Sans Thai', 'NotoSansThai', 'LeelawUI', 'Tahoma', 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #111827;
            line-height: 1.6;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        p { line-height: 1.6; letter-spacing: 0; }

        @page { margin: 80px 36px 60px; }

        header {
            position: fixed;
            top: -60px; left: 0; right: 0;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        h1, h2, h3 { margin: 0 0 6px 0; line-height: 1.65; letter-spacing: 0; }
        .section { margin-bottom: 18px; }
        .muted { color: #000; }
        .pill {
            display: inline-block;
            /* background: #e5e7eb; */
            color: #000;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 18px;
            margin-right: 4px;
            margin-bottom: 4px;
            line-height: 1.8;
        }
        .wrap { max-width: 720px; margin: 0 auto; }
        table {
            width: 92%;
            border-collapse: collapse;
            margin: 4px auto 0 auto;
            table-layout: fixed;
        }
        th {
            background: #e5e7eb;
            color: #000;
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            text-align: center;
            font-size: 16px;
            line-height: 1.8;
            letter-spacing: 0;
            vertical-align: middle;
        }
        td {
            padding: 6px 6px;
            border: 1px solid #e5e7eb;
            line-height: 1.5;
            letter-spacing: 0;
            vertical-align: middle;
            font-size: 16px;
            text-align: center;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .muted-inline { color: #6b7280; }
    </style>
</head>
<body>
<div class="wrap">
<header>สรุปหลักสูตรและนักเรียนที่รับผิดชอบ</header>
@if($logoPath)
    <div style="text-align:center; margin: 0 0 8px 0;">
        <img src="{{ $logoPath }}" alt="โลโก้โรงเรียน" style="height:70px; object-fit:contain;">
    </div>
@endif

<p style="font-size:15px; font-weight:700; margin:0 0 4px 0;">
    ครูผู้รับผิดชอบ: {{ $teacher->name ?? '-' }}
</p>

<div class="section">
    <h2 style="font-size:15px;">หลักสูตรที่รับผิดชอบ</h2>
    <table>
        <thead>
        <tr>
            <th style="width:50%;">ชื่อหลักสูตร</th>
            <th style="width:25%;">ห้อง</th>
            <th style="width:25%;">ชั้น</th>
        </tr>
        </thead>
        <tbody>
        @forelse($courses ?? [] as $course)
            @php
                $courseRoomsList = collect($course->rooms ?? [])->map($normalizeRoom)->filter();

                $courseRooms = $courseRoomsList->join(', ');

                $courseGradeRaw = $course->grade ?? null;
                if (is_array($courseGradeRaw)) {
                    $courseGrade = collect($courseGradeRaw)
                        ->map($normalizeRoom)
                        ->filter()
                        ->unique()
                        ->join(', ');
                } else {
                    $courseGrade = $courseGradeRaw;
                }

                // If grade is empty, infer it from the room prefix before "/"
                if (trim((string) $courseGrade) === '' && $courseRoomsList->isNotEmpty()) {
                    $courseGrade = $courseRoomsList
                        ->map(function ($room) {
                            if (! is_string($room)) {
                                return null;
                            }
                            return str_contains($room, '/')
                                ? trim(strtok($room, '/'))
                                : null;
                        })
                        ->filter()
                        ->unique()
                        ->join(', ');
                }
            @endphp
            <tr>
                <td>{{ trim((string) ($course->name ?? '')) !== '' ? $course->name : '-' }}</td>
                <td>{{ $courseRooms !== '' ? $courseRooms : '-' }}</td>
                <td>{{ trim((string) $courseGrade) !== '' ? $courseGrade : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">ยังไม่มีหลักสูตรที่รับผิดชอบ</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h2 style="font-size:18px;">ห้องที่รับผิดชอบ</h2>
    <p>
        @forelse($roomsList as $room)
            <span class="pill">{{ $room }}</span>
        @empty
            <span class="muted">ไม่มีข้อมูลห้องที่รับผิดชอบ</span>
        @endforelse
    </p>
</div>

<div class="section">
    <h2 style="font-size:18px; margin-bottom:6px;">ชื่อนักเรียนตามห้อง</h2>
    @forelse($roomsList as $room)
        @php $students = ($studentsByRoom ?? collect())->get($room, collect()); @endphp
        <h3 style="margin-top:10px;">ห้อง {{ $room }}</h3>
        @if($students->isEmpty())
            <p class="muted" style="margin-top:4px;">ยังไม่มีนักเรียนในห้องนี้</p>
        @else
            <table>
                <thead>
                <tr>
                    <th style="width:18%;">รหัส</th>
                    <th style="width:32%;">ชื่อ</th>
                    <th style="width:32%;">นามสกุล</th>
                    <th style="width:18%;">ห้อง</th>
                </tr>
                </thead>
                <tbody>
                @foreach($students as $student)
                    @php
                        $roomDisplay = $normalizeRoom($student->room_normalized ?? $student->room ?? $room ?? null);
                    @endphp
                    <tr>
                        <td>{{ trim((string) ($student->student_code ?? '')) !== '' ? $student->student_code : '-' }}</td>
                        <td class="text-left">{{ trim((string) ($student->first_name ?? '')) !== '' ? $student->first_name : '-' }}</td>
                        <td class="text-left">{{ trim((string) ($student->last_name ?? '')) !== '' ? $student->last_name : '-' }}</td>
                        <td>{{ $roomDisplay ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <p class="muted">ไม่มีข้อมูลนักเรียนตามห้อง</p>
    @endforelse
</div>

</div>
</body>
</html>
