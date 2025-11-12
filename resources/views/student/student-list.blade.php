<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจัดการข้อมูลนักเรียน | โรงเรียนบ้านช่องพลี</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const modal = document.getElementById("addModal");
      const openBtn = document.getElementById("openModal");
      const closeBtns = document.querySelectorAll(".closeModal");
      const overlay = document.getElementById("overlay");

      const toggleModal = (show) => {
        modal.classList.toggle("hidden", !show);
        overlay.classList.toggle("hidden", !show);
        document.body.classList.toggle("overflow-hidden", show);
      };

      openBtn.addEventListener("click", () => toggleModal(true));
      closeBtns.forEach(btn => btn.addEventListener("click", () => toggleModal(false)));
      overlay.addEventListener("click", () => toggleModal(false));
    });
  </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-100 font-sans p-8">

  <!-- Header -->
  <header class="max-w-6xl mx-auto mb-8 flex justify-between items-center">
    <div>
      <h1 class="text-3xl font-bold text-blue-700">🏫 โรงเรียนบ้านช่องพลี</h1>
      <p class="text-gray-500 text-sm mt-1">ระบบจัดการข้อมูลนักเรียน (Student Management System)</p>
    </div>
    <button id="openModal"
      class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-md transition duration-300">
      ➕ เพิ่มนักเรียน
    </button>
  </header>

  <!-- Main Table -->
  <div class="max-w-6xl mx-auto bg-white shadow-2xl rounded-2xl p-6 border border-gray-100">
    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">📋 ข้อมูลนักเรียน</h2>

    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-gray-300 rounded-lg">
        <thead class="bg-green-200 text-gray-800 font-semibold text-center">
          <tr>
            <th class="py-3 px-2 border">เลขที่</th>
            <th class="py-3 px-2 border">StudentID</th>
            <th class="py-3 px-2 border">เลขประจำตัว</th>
            <th class="py-3 px-2 border">คำนำหน้าชื่อ</th>
            <th class="py-3 px-2 border">ชื่อ</th>
            <th class="py-3 px-2 border">นามสกุล</th>
            <th class="py-3 px-2 border">การจัดการ</th>
          </tr>
        </thead>
        <tbody class="text-center divide-y divide-gray-200">
          <tr class="hover:bg-blue-50">
            <td class="border py-2">1</td>
            <td class="border py-2">2997</td>
            <td class="border py-2">2997</td>
            <td class="border py-2 font-medium">นาย</td>
            <td class="border py-2">เจนวิทย์</td>
            <td class="border py-2">บุตรหมัน</td>
            <td class="border py-2 space-x-2">
              <button class="text-yellow-600 hover:underline font-medium">แก้ไข</button>
              <button class="text-red-600 hover:underline font-medium">ลบ</button>
            </td>
          </tr>
          <tr class="hover:bg-blue-50">
            <td class="border py-2">2</td>
            <td class="border py-2">3006</td>
            <td class="border py-2">3006</td>
            <td class="border py-2 font-medium">นาย</td>
            <td class="border py-2">ปภาวิน</td>
            <td class="border py-2">สายนุ้ย</td>
            <td class="border py-2 space-x-2">
              <button class="text-yellow-600 hover:underline font-medium">แก้ไข</button>
              <button class="text-red-600 hover:underline font-medium">ลบ</button>
            </td>
          </tr>
          <tr class="hover:bg-blue-50">
            <td class="border py-2">3</td>
            <td class="border py-2">3366</td>
            <td class="border py-2">3366</td>
            <td class="border py-2 font-medium">นาย</td>
            <td class="border py-2">ณัฐศิษฏ์</td>
            <td class="border py-2">จงรักษ์</td>
            <td class="border py-2 space-x-2">
              <button class="text-yellow-600 hover:underline font-medium">แก้ไข</button>
              <button class="text-red-600 hover:underline font-medium">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Overlay -->
  <div id="overlay" class="hidden fixed inset-0 bg-black bg-opacity-40 z-40 backdrop-blur-sm"></div>

  <!-- Modal -->
  <div id="addModal"
    class="hidden fixed inset-0 flex items-center justify-center z-50 transition-all duration-300 ease-in-out">
    <div class="bg-white rounded-2xl shadow-2xl w-[90%] max-w-lg p-6 relative border border-gray-200">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center border-b pb-2">➕ เพิ่มข้อมูลนักเรียน</h3>

      <form class="space-y-4 max-h-[65vh] overflow-y-auto px-1">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้าชื่อ</label>
          <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option>นาย</option>
            <option>นางสาว</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ</label>
          <input type="text" placeholder="กรอกชื่อ"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">นามสกุล</label>
          <input type="text" placeholder="กรอกนามสกุล"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">เลขประจำตัว</label>
          <input type="text" placeholder="กรอกเลขประจำตัว"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
      </form>

      <div class="mt-6 flex justify-end gap-3">
        <button type="button"
          class="closeModal px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-600 font-medium">
          ยกเลิก
        </button>
        <button type="button"
          class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md">
          บันทึก
        </button>
      </div>
    </div>
  </div>

</body>
</html>
