@php
    $termLabel = $selectedTerm === '2' ? 'ภาคเรียนที่ 2' : 'ภาคเรียนที่ 1';
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>รายละเอียดหลักสูตร - {{ $course->name }}</title>

    <style>
        {{-- โหลดฟอนต์ Sarabun แบบตรงจาก storage --}}
        @php
            $fontRegular = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Regular.ttf'));
            $fontBold    = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Bold.ttf'));
        @endphp

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

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 13px;
            color: #111827;
            line-height: 1.45;
        }

        @page {
            margin: 90px 40px 70px;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0; right: 0;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        footer {
            position: fixed;
            bottom: -45px;
            left: 0; right: 0;
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        .section { margin-bottom: 22px; }
        .muted { font-size: 12px; color: #6b7280; }

        .pill {
            display: inline-block;
            background: #e0e7ff;
            color: #1d4ed8;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            margin-right: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            background: #1d4ed8;
            color: #fff;
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-weight: 700;
        }

        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
        }

        h1,h2,h3 { margin: 0 0 6px 0; }
    </style>
</head>

<body>

<header>รายละเอียดหลักสูตร</header>
<footer>หน้าที่ {PAGE_NUM} / {PAGE_COUNT}</footer>

{{-- หัวเรื่อง --}}
<h1 style="font-size:20px; margin-bottom:6px;">รายละเอียดหลักสูตร</h1>
<p class="muted">
    พิมพ์โดย: {{ $teacher->name ?? '-' }} |
    วันที่ {{ now()->format('d/m/Y H:i') }}
</p>

{{-- ข้อมูลหลักสูตร --}}
<div class="section">
    <h2 style="font-size:17px; font-weight:700;">{{ $course->name }}</h2>

    <p>
        ระดับชั้น: {{ $course->grade ?? '-' }} |
        ปีการศึกษา: {{ $course->year ?? '-' }} |
        ภาคเรียน: {{ $termLabel }}
    </p>

    <p>
        ห้องเรียน:
        @forelse($course->rooms ?? [] as $room)
            <span class="pill">{{ $room }}</span>
        @empty
            <span class="muted">-</span>
        @endforelse
    </p>

    <p>รายละเอียด: {{ $course->description ?? '-' }}</p>
</div>

{{-- ตาราง ชม.สอน --}}
<div class="section">
    <h3>ชั่วโมงสอน (เป้าหมาย vs ใช้แล้ว)</h3>
    <table>
        <thead>
            <tr>
                <th>หมวด</th>
                <th>ชั่วโมงที่กำหนด</th>
                <th>ชั่วโมงที่ใช้</th>
                <th>คงเหลือ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessonCapacity as $category => $meta)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $meta['allowed'] }}</td>
                    <td>{{ $meta['used'] }}</td>
                    <td>{{ $meta['remaining'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">ยังไม่มีข้อมูลชั่วโมงสอน</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ตารางบทเรียน --}}
<div class="section">
    <h3>บทเรียน (ภาคเรียนนี้)</h3>
    <table>
        <thead>
            <tr>
                <th>เรื่อง</th>
                <th>หมวด</th>
                <th>ชั่วโมง</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessons as $lesson)
                <tr>
                    <td>{{ $lesson['title'] }}</td>
                    <td>{{ $lesson['category'] }}</td>
                    <td>{{ $lesson['hours'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">ยังไม่มีบทเรียน</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ตารางงาน/คะแนน --}}
<div class="section">
    <h3>การบ้าน / งาน (ภาคเรียนนี้)</h3>

    <table>
        <thead>
            <tr>
                <th>ชื่องาน</th>
                <th>คะแนน</th>
                <th>กำหนดส่ง</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment['title'] }}</td>
                    <td>{{ $assignment['score'] }}</td>
                    <td>{{ $assignment['due_date'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">ยังไม่มีการบ้าน/งาน</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="muted">
        คะแนนรวม: {{ $assignmentTotal }} |
        เหลือคงต้องเพิ่ม: {{ $assignmentRemaining }}
    </p>
</div>

</body>
</html>
