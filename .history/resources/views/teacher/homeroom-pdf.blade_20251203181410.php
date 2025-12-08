@php
    $roomsList = ($rooms ?? collect())->filter();
    $printedAt = ($generatedAt ?? now())->timezone('Asia/Bangkok');
    $printedAtTh = $printedAt->copy()->addYears(543)->format('d/m/Y H:i');
    $logoFile = public_path('images/school-logo.png');
    $logoPath = file_exists($logoFile) ? ('file:///' . str_replace('\\', '/', $logoFile)) : null;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Prefer Noto Sans Thai for reliable Thai rendering in PDFs */
        @font-face {
            font-family: 'NotoSansThai';
            font-weight: 400;
            src: url('{{ 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Regular.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'NotoSansThai';
            font-weight: 700;
            src: url('{{ 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Bold.ttf')) }}') format('truetype');
        }
        /* keep LeelawUI as fallback */
        @font-face {
            font-family: 'LeelawUI';
            font-weight: 400;
            src: url('{{ 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'LeelawUI';
            font-weight: 700;
            src: url('{{ 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf')) }}') format('truetype');
        }

        body {
            font-family: 'NotoSansThai', 'LeelawUI', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.6;
        }

        @page { margin: 80px 36px 60px; }

        header {
            position: fixed;
            top: -60px; left: 0; right: 0;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
        }

        h1, h2, h3 { margin: 0 0 6px 0; }
        .section { margin-bottom: 18px; }
        .muted { color: #6b7280; }
        .pill {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th {
            background: #2563eb;
            color: #fff;
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
            text-align: center;
        }
        td { padding: 6px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
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
            <th style="width:50%;">ห้อง / ปี-ระดับ</th>
        </tr>
        </thead>
        <tbody>
        @forelse($courses ?? [] as $course)
            @php $courseRooms = collect($course->rooms ?? [])->filter()->join(', '); @endphp
            <tr>
                <td>{{ $course->name }}</td>
                <td>
                    {{ $courseRooms !== '' ? $courseRooms : '-' }}
                    @if(!empty($course->grade))
                        / {{ $course->grade }}
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">ยังไม่มีข้อมูลหลักสูตร</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h2 style="font-size:15px;">ห้องที่รับผิดชอบ</h2>
    <p>
        @forelse($roomsList as $room)
            <span class="pill">{{ $room }}</span>
        @empty
            <span class="muted">ยังไม่มีห้องที่รับผิดชอบ</span>
        @endforelse
    </p>
</div>

<div class="section">
    <h2 style="font-size:15px; margin-bottom:6px;">ชื่อนักเรียนตามห้อง</h2>
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
                    <tr>
                        <td>{{ $student->student_code }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->room ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <p class="muted">ยังไม่มีข้อมูลนักเรียนหรือห้องสำหรับรายการนี้</p>
    @endforelse
</div>

</body>
</html>
