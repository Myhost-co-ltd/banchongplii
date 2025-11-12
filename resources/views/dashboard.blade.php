@extends('layouts.layout')

@section('title', 'แดชบอร์ด')

@section('content')
<!-- 🔹 ส่วนหัว -->
<div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">แดชบอร์ด</h2>
      <p class="text-gray-600 mt-1">
        ยินดีต้อนรับ <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
      </p>
    </div>

    <button id="openModalBtn" class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg shadow transition">
      + เพิ่มนักเรียน
    </button>
  </div>
</div>

<!-- 🔹 สรุปสถิติ -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
  <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl text-center shadow-sm">
    <h3 class="text-sm text-gray-600 mb-1">จำนวนนักเรียน</h3>
    <p class="text-4xl font-bold text-blue-700">5</p>
  </div>

  <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl text-center shadow-sm">
    <h3 class="text-sm text-gray-600 mb-1">ครูทั้งหมด</h3>
    <p class="text-4xl font-bold text-green-700">2</p>
  </div>

  <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-2xl text-center shadow-sm">
    <h3 class="text-sm text-gray-600 mb-1">รายงานล่าสุด</h3>
    <p class="text-4xl font-bold text-yellow-700">1</p>
  </div>
</div>

<!-- 🔹 ตารางนักเรียน -->
<div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
  <h2 class="text-xl font-semibold text-gray-800 mb-6">รายชื่อนักเรียน</h2>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden text-sm text-gray-700">
      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="py-3 px-4 text-left font-medium">#</th>
          <th class="py-3 px-4 text-left font-medium">รหัสประจำตัว</th>
          <th class="py-3 px-4 text-left font-medium">คำนำหน้า</th>
          <th class="py-3 px-4 text-left font-medium">ชื่อ</th>
          <th class="py-3 px-4 text-left font-medium">นามสกุล</th>
          <th class="py-3 px-4 text-center font-medium">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
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
          <td class="py-2 px-4 text-center">
            <button class="text-blue-600 hover:underline px-1">แก้ไข</button> |
            <button class="text-red-600 hover:underline px-1">ลบ</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- 🔹 Modal -->
<div id="studentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 transition-all">
  <div class="bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6 relative animate-fadeIn">
    <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl">×</button>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">เพิ่มข้อมูลนักเรียน</h3>
    
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
        <button type="button" id="cancelModal" class="px-4 py-2 rounded-lg border hover:bg-gray-100 transition">ยกเลิก</button>
        <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- 🔹 Script -->
<script>
  const openModal = document.getElementById('openModalBtn');
  const closeModal = document.getElementById('closeModalBtn');
  const cancelModal = document.getElementById('cancelModal');
  const modal = document.getElementById('studentModal');

  if (openModal) {
    openModal.addEventListener('click', () => modal.classList.remove('hidden'));
    closeModal.addEventListener('click', () => modal.classList.add('hidden'));
    cancelModal.addEventListener('click', () => modal.classList.add('hidden'));
  }
</script>

<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  .animate-fadeIn {
    animation: fadeIn 0.2s ease-in-out;
  }
</style>
@endsection
