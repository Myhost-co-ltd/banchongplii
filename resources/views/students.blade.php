<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลนักเรียน | โรงเรียนบ้านช่องพลี</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-100 font-sans">

  <!-- Sidebar -->
  <aside class="w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white shadow-2xl rounded-r-3xl p-6 flex flex-col justify-between">
    <div>
      <div class="flex items-center space-x-3 mb-8">
        <div class="bg-white text-blue-600 font-bold rounded-full w-10 h-10 flex items-center justify-center shadow-md">🏫</div>
        <h1 class="text-lg font-bold">โรงเรียนบ้านช่องพลี</h1>
      </div>

      <nav class="space-y-2">
        <a href="/dashboard" class="block py-2.5 px-4 rounded-xl hover:bg-blue-500 transition">🏠 หน้าหลัก</a>
        <a href="/students" class="block py-2.5 px-4 bg-blue-500 rounded-xl shadow-md">👩‍🎓 จัดการนักเรียน</a>
        <a href="#" class="block py-2.5 px-4 rounded-xl hover:bg-blue-500 transition">👨‍🏫 ครู</a>
      </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-8">
      @csrf
      <button type="submit" class="w-full py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">🚪 ออกจากระบบ</button>
    </form>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-10">
    <div class="bg-white rounded-3xl shadow-xl p-8">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">👩‍🎓 รายชื่อนักเรียน</h2>
        <button id="openModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition">
          ➕ เพิ่มนักเรียน
        </button>
      </div>

      <!-- ตารางนักเรียน -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-gray-200 rounded-xl overflow-hidden">
          <thead class="bg-blue-100 text-gray-800">
            <tr>
              <th class="py-3 px-4 text-left">#</th>
              <th class="py-3 px-4 text-left">รหัสประจำตัว</th>
              <th class="py-3 px-4 text-left">คำนำหน้า</th>
              <th class="py-3 px-4 text-left">ชื่อ</th>
              <th class="py-3 px-4 text-left">นามสกุล</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-700">
            @php
              $students = [
                ['id'=>1,'sid'=>2997,'title'=>'นาย','fname'=>'เจนวิทย์','lname'=>'บุตรหมัน'],
                ['id'=>2,'sid'=>3006,'title'=>'นาย','fname'=>'ปภาวิน','lname'=>'สายนุ้ย'],
                ['id'=>3,'sid'=>3366,'title'=>'นาย','fname'=>'ณัฐศิษฏ์','lname'=>'จงรักษ์'],
                ['id'=>4,'sid'=>4474,'title'=>'นาย','fname'=>'อนุชิต','lname'=>'โล่เสื้อ'],
                ['id'=>5,'sid'=>2706,'title'=>'นางสาว','fname'=>'ชนากานต์','lname'=>'ป้องปิด'],
              ];
            @endphp

            @foreach ($students as $s)
            <tr class="hover:bg-blue-50 transition">
              <td class="py-2 px-4">{{ $s['id'] }}</td>
              <td class="py-2 px-4">{{ $s['sid'] }}</td>
              <td class="py-2 px-4">{{ $s['title'] }}</td>
              <td class="py-2 px-4">{{ $s['fname'] }}</td>
              <td class="py-2 px-4">{{ $s['lname'] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal เพิ่มนักเรียน -->
    <div id="studentModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6 relative animate-fadeIn">
        <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl">✕</button>
        <h3 class="text-xl font-bold text-blue-700 mb-4">➕ เพิ่มข้อมูลนักเรียน</h3>
        <form class="space-y-3">
          <input type="text" placeholder="รหัสประจำตัว" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
          <select class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
            <option>-- คำนำหน้า --</option>
            <option>นาย</option>
            <option>นางสาว</option>
          </select>
          <input type="text" placeholder="ชื่อ" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
          <input type="text" placeholder="นามสกุล" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
          <div class="flex justify-end space-x-2 pt-2">
            <button type="button" id="cancelModal" class="px-4 py-2 rounded-lg border hover:bg-gray-100">ยกเลิก</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">บันทึก</button>
          </div>
        </form>
      </div>
    </div>

    <footer class="text-center text-gray-500 text-sm mt-10">
      © 2025 โรงเรียนบ้านช่องพลี — สงวนลิขสิทธิ์ทุกประการ
    </footer>
  </main>

  <script>
    const openModal = document.getElementById('openModalBtn');
    const closeModal = document.getElementById('closeModalBtn');
    const modal = document.getElementById('studentModal');
    const cancelModal = document.getElementById('cancelModal');

    openModal.addEventListener('click', () => modal.classList.remove('hidden'));
    closeModal.addEventListener('click', () => modal.classList.add('hidden'));
    cancelModal.addEventListener('click', () => modal.classList.add('hidden'));
  </script>

</body>
</html>
