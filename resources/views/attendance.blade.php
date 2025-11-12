<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>บันทึกเวลาเรียน | โรงเรียนบ้านช่องพลี</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <style>
    /* ✅ scrollbar สวยแบบ modern */
    ::-webkit-scrollbar {
      height: 10px;
      width: 10px;
    }
    ::-webkit-scrollbar-thumb {
      background-color: #9ca3af;
      border-radius: 10px;
    }
    ::-webkit-scrollbar-track {
      background-color: #f3f4f6;
    }
  </style>
</head>

<body class="h-screen flex overflow-hidden bg-gray-100 font-sans">

  <!-- ✅ Sidebar -->
  <aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
    <div>
      <h1 class="text-lg font-bold mb-8 leading-tight">โรงเรียนบ้านช่องพลี</h1>
      <nav class="space-y-2">
        <a href="/dashboard" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">ข้อมูลนักเรียน</a>
        <a href="/attendance" class="block py-2.5 px-4 rounded-xl bg-gray-700 font-medium">บันทึกเวลาเรียน</a>
        <a href="#" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">ประเมินผลการเรียน</a>
        <a href="#" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">สรุปผลสัมฤทธิ์</a>
        <a href="/assignments" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">กำหนดชิ้นงาน</a>
        <a href="#" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">แผนภูมิสรุป</a>
        <a href="#" class="block py-2.5 px-4 rounded-xl hover:bg-gray-700 transition">โครงสร้างรายวิชา</a>
      </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-8">
      @csrf
      <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
        ออกจากระบบ
      </button>
    </form>
  </aside>

  <!-- ✅ Main -->
  <main class="flex-1 flex flex-col p-10 bg-gray-50 overflow-hidden h-screen">
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 flex flex-col flex-1 overflow-hidden">

      <!-- 🔹 ส่วนหัว -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">บันทึกเวลาเรียน</h2>
      </div>

      <!-- ✅ Scroll เฉพาะตาราง -->
      <div class="flex-1 border rounded-2xl overflow-hidden">
        <div class="w-full h-[600px] overflow-x-auto overflow-y-auto relative">
          <div class="min-w-max">
            <table id="attendanceTable" class="border-collapse text-sm text-center">
              <thead class="sticky top-0 z-20 shadow-md">
                <!-- แถว 1: สัปดาห์ -->
                <tr class="bg-blue-800 text-white">
                  <th rowspan="4" class="sticky left-0 z-30 bg-blue-800 border p-2">เลขที่</th>
                  <th rowspan="4" class="sticky left-[60px] z-30 bg-blue-800 border p-2">เลขประจำตัว</th>
                  <th rowspan="4" class="sticky left-[160px] z-30 bg-blue-800 border p-2 w-56">ชื่อ - สกุล</th>

                  @for ($w = 1; $w <= 20; $w++)
                    <th colspan="6" class="border bg-blue-700">สัปดาห์ที่ {{ $w }}</th>
                  @endfor

                  <th colspan="6" rowspan="3" class="border bg-blue-900">สรุปผล</th>
                </tr>

                <!-- แถว 2: เดือน -->
                <tr class="bg-blue-600 text-white">
                  @php
                    $months = ['พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม'];
                  @endphp
                  @for ($w = 1; $w <= 20; $w++)
                    <th colspan="6" class="border bg-blue-600">
                      {{ $months[($w - 1) % count($months)] }}
                    </th>
                  @endfor
                </tr>

                <!-- แถว 3: วันที่ -->
                {{-- <tr class="bg-blue-500 text-white text-xs">
                  @for ($w = 1; $w <= 20; $w++)
                    @for ($i = 1; $i <= 6; $i++)
                      <th class="border p-1">{{ $i + ($w - 1) * 6 }}</th>
                    @endfor
                  @endfor
                </tr> --}}

                <!-- แถว 4: ชั่วโมงที่ -->
                <tr class="bg-blue-400 text-white text-xs">
                  @for ($i = 1; $i <= 120; $i++)  {{-- 20 สัปดาห์ × 6 ช่อง --}}
                    <th class="border p-1">#</th>
                  @endfor
                  <th class="border p-1">มา</th>
                  <th class="border p-1">ขาด</th>
                  <th class="border p-1">ลา</th>
                  <th class="border p-1">ป่วย</th>
                  <th class="border p-1">%มาเรียน</th>
                  <th class="border p-1">สถานะ</th>
                </tr>
              </thead>

              <tbody class="text-gray-800">
                @php
                  $students = [
                    ['no'=>1,'id'=>2997,'name'=>'นายเจนวิทย์ บุตรหมัน'],
                    ['no'=>2,'id'=>3006,'name'=>'นายปภาวิน สายนุ้ย'],
                    ['no'=>3,'id'=>3366,'name'=>'นายณัฐศิษฏ์ จงรักษ์'],
                    ['no'=>4,'id'=>4474,'name'=>'นายอนุชิต โล่เสื้อ'],
                    ['no'=>5,'id'=>2706,'name'=>'น.ส.ชนากานต์ ป้องปิด'],
                  ];
                @endphp

                @foreach ($students as $s)
                <tr class="hover:bg-blue-50 transition">
                  <!-- Sticky Columns -->
                  <td class="sticky left-0 bg-white border p-2 font-medium z-20">{{ $s['no'] }}</td>
                  <td class="sticky left-[60px] bg-white border p-2 z-20">{{ $s['id'] }}</td>
                  <td class="sticky left-[160px] bg-white border p-2 text-left w-56 z-20">{{ $s['name'] }}</td>

                  <!-- ช่องบันทึก -->
                  @for ($i = 1; $i <= 120; $i++)
                    <td class="border p-1">
                      <input type="text" maxlength="1" placeholder="-"
                        class="w-8 text-center border border-gray-300 rounded text-xs">
                    </td>
                  @endfor

                  <!-- รวม -->
                  <td class="border text-green-600 font-semibold">34</td>
                  <td class="border text-red-500 font-semibold">6</td>
                  <td class="border text-yellow-600 font-semibold">-</td>
                  <td class="border text-purple-600 font-semibold">-</td>
                  <td class="border text-blue-800 font-semibold">85%</td>
                  <td class="border text-gray-700 font-semibold">ปกติ</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ✅ ปุ่มด้านล่าง -->
      <div class="flex justify-end mt-4 space-x-3">
        <button id="exportExcel" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
          ส่งออก Excel
        </button>
        <button class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-lg transition">
          บันทึกทั้งหมด
        </button>
      </div>

    </div>
  </main>

  <!-- ✅ Script -->
  <script>
    document.getElementById("exportExcel").addEventListener("click", () => {
      const wb = XLSX.utils.table_to_book(document.getElementById("attendanceTable"));
      XLSX.writeFile(wb, "บันทึกเวลาเรียน.xlsx");
    });
  </script>
</body>
</html>
