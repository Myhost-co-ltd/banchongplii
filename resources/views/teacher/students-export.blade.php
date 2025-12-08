@php
    $fontTHSarabunRegular    = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Regular.ttf'));
    $fontTHSarabunBold       = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Bold.ttf'));
    $fontTHSarabunItalic     = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-Italic.ttf'));
    $fontTHSarabunBoldItalic = 'file:///' . str_replace('\\', '/', storage_path('fonts/THSarabunNew-BoldItalic.ttf'));
    $fontRegular             = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Regular.ttf'));
    $fontBold                = 'file:///' . str_replace('\\', '/', storage_path('fonts/Sarabun-Bold.ttf'));
    $fontNotoRegular         = 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Regular.ttf'));
    $fontNotoBold            = 'file:///' . str_replace('\\', '/', storage_path('fonts/NotoSansThai-Bold.ttf'));
    $printedAt               = now()->timezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i');

    $logoPathFile = public_path('images/school-logo.png');
    $logoPath     = file_exists($logoPathFile) ? ('file:///' . str_replace('\\', '/', $logoPathFile)) : null;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
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
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontNotoRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontNotoBold }}') format('truetype');
        }
        body {
            font-family: 'THSarabunNew', 'TH Sarabun New', 'Sarabun', 'Noto Sans Thai', 'NotoSansThai', 'LeelawUI', DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #000;
            line-height: 1.6;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        h1 {  margin: 0 0 12px; font-size: 22px; text-align: center; line-height: 1.7; letter-spacing: 0; }
        h2 { margin: 16px 0 8px; font-size: 15px; font-weight: 700; line-height: 1.7; letter-spacing: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 8px; text-align: left; line-height: 1.7; letter-spacing: 0; vertical-align: middle; }
        th { background: #e5e7eb; color: #000; font-weight: 700; line-height: 1.8; letter-spacing: 0; text-align: center; border: 1px solid #d1d5db; }
        .center-text { text-align: center; }
        .muted { color: #000; font-size: 12px; }
        .header { text-align: left; margin-bottom: 12px; }
        .page { width: 700px; margin: 0 auto; }
        .meta { color: #000; font-size: 14px; margin: 2px 0; }
        .meta strong { color: #000; }
        .meta-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>รายชื่อนักเรียนตามห้อง</h1>
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
            <p class="muted" style="margin-top: 4px;">พิมพ์เมื่อ: {{ $printedAt }}</p>
        </div>

        @if($logoPath)
            <div style="text-align:center; margin: 6px 0 12px 0;">
                <img src="{{ $logoPath }}" alt="โลโก้โรงเรียน" style="height:80px; object-fit:contain;">
            </div>
        @endif

        @foreach(($studentsByRoom ?? collect()) as $room => $list)
            <h2>ห้องเรียน: {{ $room }}</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">รหัส</th>
                        <th style="width: 15%;">คำนำหน้า</th>
                        <th style="width: 25%;">ชื่อ</th>
                        <th style="width: 25%;">นามสกุล</th>
                        <th style="width: 10%;">ห้องเรียน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $student)
                        <tr>
                            <td class="center-text">{{ $student->student_code }}</td>
                            <td class="center-text">{{ $student->title ?? '-' }}</td>
                            <td>{{ $student->first_name }}</td>
                            <td>{{ $student->last_name }}</td>
                            <td class="center-text">{{ $student->room_normalized ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted" style="text-align:center;">ยังไม่มีนักเรียนในห้องนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach

        @if(($studentsByRoom ?? collect())->isEmpty())
            <p class="muted">ไม่มีข้อมูลนักเรียนตามห้อง</p>
        @endif
    </div>
</body>
</html>
