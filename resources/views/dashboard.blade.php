@extends('layouts.layout')

@section('title', 'ข้อมูลนักเรียน')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
  <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-sm text-slate-500 uppercase tracking-wide">แดชบอร์ด</p>
        <h2 class="text-3xl font-bold text-gray-900 mt-1">ข้อมูลนักเรียน</h2>
        <p class="text-gray-600 mt-1">
          ยินดีต้อนรับ <span class="font-semibold text-blue-700">{{ Auth::user()->name }}</span>
        </p>
      </div>

      <button id="openModalBtn"
        class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg shadow transition inline-flex items-center justify-center gap-2">
        <span class="text-lg leading-none">+</span>
        <span>เพิ่มนักเรียน</span>
      </button>
    </div>
  </div>

  @if (session('status'))
  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl text-sm">
    {{ session('status') }}
  </div>
  @endif

  @if ($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm">
    <p class="font-semibold">กรุณาตรวจสอบข้อมูลอีกครั้ง</p>
    <ul class="list-disc pl-5 mt-2 space-y-1">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl text-center shadow-sm">
      <h3 class="text-sm text-gray-600 mb-1">จำนวนนักเรียนทั้งหมด</h3>
      <p class="text-4xl font-bold text-blue-700">{{ number_format($studentCount) }}</p>
    </div>

    <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl text-center shadow-sm">
      <h3 class="text-sm text-gray-600 mb-1">ครูทั้งหมด</h3>
      <p class="text-4xl font-bold text-green-700">{{ number_format($teacherCount) }}</p>
    </div>

    <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-2xl text-center shadow-sm">
      <h3 class="text-sm text-gray-600 mb-1">นักเรียนที่เพิ่มวันนี้</h3>
      <p class="text-4xl font-bold text-yellow-700">{{ number_format($newToday) }}</p>
    </div>
  </div>

  <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">รายชื่อนักเรียน</h2>
        <p class="text-sm text-gray-500 mt-1">รายงานรายชื่อล่าสุดเรียงจากผู้ที่เพิ่มล่าสุดก่อน</p>
      </div>
      <span class="text-sm text-gray-500">ทั้งหมด {{ number_format($studentCount) }} คน</span>
    </div>

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
          @forelse ($students as $index => $student)
          <tr class="hover:bg-blue-50 transition">
            <td class="py-2 px-4">{{ $index + 1 }}</td>
            <td class="py-2 px-4 font-medium">{{ $student->student_code }}</td>
            <td class="py-2 px-4">{{ $student->title }}</td>
            <td class="py-2 px-4">{{ $student->first_name }}</td>
            <td class="py-2 px-4">{{ $student->last_name }}</td>
            <td class="py-2 px-4 text-center text-gray-400 text-xs">
              เร็วๆ นี้
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="py-6 px-4 text-center text-gray-500">
              ยังไม่มีข้อมูลนักเรียน กรุณาเพิ่มรายการใหม่
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="studentModal"
  class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
  <div class="absolute inset-0" id="modalOverlay"></div>

  <div class="relative bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6">
    <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none">
      &times;
    </button>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">เพิ่มข้อมูลนักเรียน</h3>

    <form method="POST" action="{{ route('students.store') }}" class="space-y-4">
      @csrf

      <div>
        <label for="student_code" class="block text-sm font-medium text-gray-700 mb-1">รหัสประจำตัวนักเรียน</label>
        <input type="text" name="student_code" id="student_code" value="{{ old('student_code') }}"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          placeholder="เช่น 2997 หรือ 3006">
        @error('student_code')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้า</label>
        <select name="title" id="title"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- เลือกคำนำหน้า --</option>
          @foreach (['เด็กชาย', 'เด็กหญิง', 'นาย', 'นางสาว'] as $title)
          <option value="{{ $title }}" @selected(old('title') === $title)>
            {{ $title }}
          </option>
          @endforeach
        </select>
        @error('title')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อ</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          placeholder="ชื่อนักเรียน">
        @error('first_name')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">นามสกุล</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
          placeholder="นามสกุลนักเรียน">
        @error('last_name')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex justify-end space-x-2 pt-2">
        <button type="button" id="cancelModal"
          class="px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">ยกเลิก</button>
        <button type="submit"
          class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalWrapper = document.getElementById('studentModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const openModal = document.getElementById('openModalBtn');
    const closeModal = document.getElementById('closeModalBtn');
    const cancelModal = document.getElementById('cancelModal');

    const toggleModal = (shouldOpen) => {
      modalWrapper.classList.toggle('hidden', !shouldOpen);
      document.body.classList.toggle('overflow-hidden', shouldOpen);
    };

    openModal?.addEventListener('click', () => toggleModal(true));
    closeModal?.addEventListener('click', () => toggleModal(false));
    cancelModal?.addEventListener('click', () => toggleModal(false));
    modalOverlay?.addEventListener('click', () => toggleModal(false));

    if (@json($errors->any())) {
      toggleModal(true);
    }
  });
</script>
@endsection
