@php
    $fontRegular = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelawUI.ttf'));
    $fontBold    = 'file:///' . str_replace('\\', '/', storage_path('fonts/LeelaUIb.ttf'));
    $printedAt   = now()->timezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i');
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'LeelawUI';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'LeelawUI';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }
        body { font-family: 'LeelawUI', DejaVu Sans, sans-serif; font-size: 13px; color: #1f2937; }
        h1 {  margin: 0 0 12px; font-size: 22px; text-align: center; }
        h2 { margin: 16px 0 8px; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; color: #111827; font-weight: 700; }
        .muted { color: #6b7280; font-size: 12px; }
        .header { text-align: left; margin-bottom: 12px; }
        .page { width: 700px; margin: 0 auto; }
        .meta { color: #374151; font-size: 14px; margin: 2px 0; }
        .meta strong { color: #111827; }
        .meta-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>รายชื่อนักเรียนที่รับผิดชอบ</h1>
            <div class="meta meta-row">
                @if(!empty($courseName))
                    <span><strong>หลักสูตร:</strong> {{ $courseName }}</span>
                @endif
                <span><strong>ครู:</strong> {{ $teacher->name ?? '-' }}</span>
                @php
                    $roomsList = collect($assignedRooms ?? [])->filter()->values();
                @endphp
                @if($roomsList->isNotEmpty())
                    <span><strong>ห้อง:</strong> {{ $roomsList->join(', ') }}</span>
                @endif
            </div>
        </div>

        @php
            $logoPath = public_path('images/school-logo.png');
            $logoExists = file_exists($logoPath);
        @endphp

        @if($logoExists)
            <div style="text-align:center; margin: 6px 0 12px 0;">
                <img src="{{ $logoPath }}" alt="ตราโรงเรียน" style="height:80px; object-fit:contain;">
            </div>
        @endif

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
                            <td>{{ $student->classroom ?? $student->room ?? '-' }}</td>
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
    </div>
</body>
</html>
