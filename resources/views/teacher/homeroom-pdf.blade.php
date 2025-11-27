@php
    $fontRegular = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf'));
    $fontBold = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf'));
    $roomsList = ($rooms ?? collect())->filter();
    $printedAt = ($generatedAt ?? now())->timezone('Asia/Bangkok');
    $printedAtTh = $printedAt->copy()->addYears(543)->format('d/m/Y H:i');
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'LeelawUI';
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'LeelawUI';
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }

        body {
            font-family: 'LeelawUI', 'Tahoma', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.5;
        }

        @page { margin: 80px 36px 60px; }

        header {
            position: fixed;
            top: -60px; left: 0; right: 0;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
        }

        footer {
            position: fixed;
            bottom: -40px; left: 0; right: 0;
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        h1, h2, h3 { margin: 0 0 6px 0; }
        .section { margin-bottom: 18px; }
        .muted { color: #6b7280; }
        .pill {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            margin-right: 4px;
            margin-bottom: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th {
            background: #1d4ed8;
            color: #fff;
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
        }
        td { padding: 6px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<header>สรุปหลักสูตรและนักเรียนที่รับผิดชอบ</header>
<footer>หน้า {PAGE_NUM} / {PAGE_COUNT}</footer>

<p style="font-size:15px; font-weight:700; margin:0 0 4px 0;">
    ครูผู้รับผิดชอบ: {{ $teacher->name ?? '-' }}
</p>
<p class="muted" style="margin-bottom:10px;">
    จัดทำเมื่อ {{ $printedAtTh }}
</p>

<div class="section">
    <h2 style="font-size:15px;">หลักสูตรที่รับผิดชอบ</h2>
    <table>
        <thead>
        <tr>
            <th style="width:45%;">ชื่อหลักสูตร</th>
            <th style="width:35%;">ห้อง</th>
            <th style="width:20%;">ปี/ระดับ</th>
        </tr>
        </thead>
        <tbody>
        @forelse($courses ?? [] as $course)
            @php $courseRooms = collect($course->rooms ?? [])->filter()->join(', '); @endphp
            <tr>
                <td>{{ $course->name }}</td>
                <td>{{ $courseRooms !== '' ? $courseRooms : '-' }}</td>
                <td>{{ $course->grade ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">ยังไม่มีหลักสูตรที่รับผิดชอบ</td></tr>
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
            <span class="muted">ยังไม่กำหนดห้อง</span>
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
        <p class="muted">ยังไม่ระบุห้องหรือไม่มีนักเรียนให้แสดง</p>
    @endforelse
</div>

</body>
</html>
