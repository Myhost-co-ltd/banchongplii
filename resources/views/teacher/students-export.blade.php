@php
    $fontRegular = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf'));
    $fontBold    = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf'));
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'leelawadee';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'leelawadee';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }
        body { font-family: 'leelawadee', DejaVu Sans, sans-serif; font-size: 13px; color: #1f2937; }
        h1 { margin: 0 0 12px; font-size: 18px; }
        h2 { margin: 16px 0 8px; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #1d4ed8; color: #fff; }
        .muted { color: #6b7280; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>รายชื่อนักเรียนที่รับผิดชอบ</h1>
        <span class="muted">ครู: {{ $teacher->name ?? '-' }} | พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    @foreach(($studentsByRoom ?? collect()) as $room => $list)
        <h2>ห้อง {{ $room }}</h2>
        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>ห้อง</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $student)
                    <tr>
                        <td>{{ $student->student_code }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->room ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted" style="text-align:center;">ยังไม่มีนักเรียนในห้องนี้</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    @if(($studentsByRoom ?? collect())->isEmpty())
        <p class="muted">ยังไม่มีนักเรียนในความรับผิดชอบ</p>
    @endif
</body>
</html>
