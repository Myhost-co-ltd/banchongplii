@extends('layouts.layout')

@section('title', 'กำหนดชิ้นงาน | โรงเรียนบ้านช่องพลี')

@section('content')
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">กำหนดชิ้นงาน</h2>
    <button id="addRow" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ เพิ่มชิ้นงาน</button>
  </div>

  <div class="border rounded-2xl overflow-x-auto">
    <table class="min-w-[1200px] text-sm text-center border-collapse">
      <thead class="bg-blue-700 text-white sticky top-0">
        <tr>
          <th class="p-2 border">ลำดับ</th>
          <th class="p-2 border">ชื่อชิ้นงาน</th>
          <th class="p-2 border">ตัวชี้วัด</th>
          <th class="p-2 border">คะแนน</th>
          <th class="p-2 border">กำหนดส่ง</th>
          <th class="p-2 border">หมายเหตุ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 text-gray-700">
        {{-- <tr class="hover:bg-blue-50">
          <td class="p-2 border">1</td>
          <td class="p-2 border">แนวคิดเชิงคำนวณ</td>
          <td class="p-2 border">ง 4.2 ม.4-6/1</td>
          <td class="p-2 border">8</td>
          <td class="p-2 border">-</td>
          <td class="p-2 border">-</td>
        </tr> --}}
      </tbody>
    </table>
  </div>
@endsection
