@php
    $termLabel = $selectedTerm === '2' ? 'ภาคเรียนที่ 2' : 'ภาคเรียนที่ 1';
    $fontRegular = str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf'));
    $fontBold = str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf'));
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดหลักสูตร - {{ $course->name }}</title>
    <style>
        @font-face {
            font-family: 'LeelawUI';
            font-style: normal;
            font-weight: 400;
            src: url('file:///{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'LeelawUI';
            font-style: normal;
            font-weight: 700;
            src: url('file:///{{ $fontBold }}') format('truetype');
        }
        * { font-family: 'LeelawUI', 'Tahoma', 'DejaVu Sans', sans-serif !important; }
        body { color: #1f2937; font-size: 13px; margin: 0; padding: 18px; }
        h1, h2, h3 { margin: 0; }
        .section { margin-bottom: 18px; }
        .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #e0e7ff; color: #1d4ed8; font-size: 12px; margin-right: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #1d4ed8; color: #fff; }
        .muted { color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <h1>รายละเอียดหลักสูตร</h1>
    <p class="muted">ผู้สอน: {{ $teacher->name ?? '-' }} | พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }}</p>

    <div class="section">
        <h2>{{ $course->name }}</h2>
        <p>ระดับชั้น: {{ $course->grade ?? '-' }} | ปีการศึกษา: {{ $course->year ?? '-' }} | ภาคเรียน: {{ $termLabel }}</p>
        <p>ห้องเรียน:
            @forelse($course->rooms ?? [] as $room)
                <span class="pill">{{ $room }}</span>
            @empty
                <span class="muted">-</span>
            @endforelse
        </p>
        <p>คำอธิบายรายวิชา: {{ $course->description ?? '-' }}</p>
    </div>

    <div class="section">
        <h3>ชั่วโมงสอน (แผน vs ใช้จริง)</h3>
        <table>
            <thead>
                <tr>
                    <th>หมวด</th>
                    <th>ชั่วโมงตามแผน</th>
                    <th>ชั่วโมงใช้ไป</th>
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
                    <tr><td colspan="4" class="muted">ยังไม่มีข้อมูลชั่วโมงสอนสำหรับภาคเรียนนี้</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>หัวข้อบทเรียน (ภาคเรียนนี้)</h3>
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
                    <tr><td colspan="3" class="muted">ยังไม่มีบทเรียนในภาคเรียนนี้</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>งาน / การบ้าน (ภาคเรียนนี้)</h3>
        <table>
            <thead>
                <tr>
                    <th>ชื่องาน</th>
                    <th>คะแนนเต็ม</th>
                    <th>กำหนดส่ง</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment['title'] ?? '-' }}</td>
                        <td>{{ $assignment['score'] ?? '-' }}</td>
                        <td>{{ $assignment['due_date'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">ยังไม่มีงาน/การบ้านในภาคเรียนนี้</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="muted">รวมคะแนนเต็ม: {{ $assignmentTotal }} | คะแนนที่เหลือให้บันทึก: {{ $assignmentRemaining }}</p>
    </div>
</body>
</html>
