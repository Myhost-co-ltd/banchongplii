@extends('layouts.layout')

@section('title', 'ประเมินผลการเรียน | โรงเรียนบ้านช่องพลี')

@section('content')
@php
  $contextTeacherName = trim((string) request('teacher_name', ''));
  $contextCourseName = trim((string) request('course_name', ''));
@endphp
<div class="p-6 bg-gray-50 rounded-3xl shadow-inner space-y-6">

  <!-- 🔹 หัวข้อ -->
  <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
    แบบบันทึกผลการเรียน นักเรียนห้อง ป.1/1
  </h2>

  <!-- ✅ ตาราง -->
  @if ($contextTeacherName !== '' || $contextCourseName !== '')
    <p class="text-center text-sm text-slate-600 -mt-2">
      ครู: {{ $contextTeacherName !== '' ? $contextTeacherName : '-' }}
      <span class="mx-2 text-slate-300">|</span>
      วิชา: {{ $contextCourseName !== '' ? $contextCourseName : '-' }}
    </p>
  @endif

  <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-200">
    <div class="overflow-x-auto relative">
    <table id="evaluationTable" class="min-w-[1600px] w-full text-sm text-center border-collapse table-fixed">

      <!-- Header -->
      <thead class="bg-blue-700 text-white sticky-header">
        <tr>
          <th class="p-2 border sticky-col-1 col-no" rowspan="2">เลขที่</th>
          <th class="p-2 border sticky-col-2 col-id" rowspan="2">รหัส</th>
          <th class="p-2 border sticky-col-3 col-name text-left" rowspan="2">ชื่อ - สกุล</th>

          <th class="p-2 border" colspan="8">คะแนนระหว่างภาค (รวม 80)</th>
          <th class="p-2 border" rowspan="2">รวม<br>(80)</th>
          <th class="p-2 border" rowspan="2">สอบปลายภาค<br>(20)</th>

          <th class="p-2 border" colspan="5">การประเมินผลปลายภาค</th>
          <th class="p-2 border" colspan="8">คุณลักษณะอันพึงประสงค์</th>

          <th class="p-2 border" rowspan="2">รวม<br>(100)</th>
          <th class="p-2 border" rowspan="2">เกรด</th>
          <th class="p-2 border" rowspan="2">สถานะ</th>
          <th class="p-2 border sticky-col-4 col-action" rowspan="2">จัดการ</th>
        </tr>

        <tr>
          @for ($i=1;$i<=8;$i++)
              <th class="p-2 border">{{ $i }}</th>
          @endfor

          @for ($i=1;$i<=5;$i++)
              <th class="p-2 border">{{ $i }}</th>
          @endfor

          @for ($i=1;$i<=8;$i++)
              <th class="p-2 border">{{ $i }}</th>
          @endfor
        </tr>
      </thead>

      <!-- BODY -->
      <tbody class="divide-y divide-gray-200 text-gray-700">
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

          <td class="p-2 border sticky-col-1 col-no">{{ $s['no'] }}</td>
          <td class="p-2 border sticky-col-2 col-id">{{ $s['id'] }}</td>
          <td class="p-2 border sticky-col-3 col-name text-left px-3">{{ $s['name'] }}</td>

          @for ($i = 0; $i < 8; $i++)
            <td class="p-2 border"><input type="number" class="input-cell comp" value="10"></td>
          @endfor

          <td class="p-2 border midterm font-semibold text-blue-700">80</td>

          <td class="p-2 border"><input type="number" class="input-cell final" value="20"></td>

          @for ($j=0;$j<5;$j++)
            <td class="p-2 border"><input type="number" class="input-cell eval" value="3"></td>
          @endfor

          @for ($k=0;$k<8;$k++)
            <td class="p-2 border"><input type="number" class="input-cell char" value="3"></td>
          @endfor

          <td class="p-2 border total font-semibold text-blue-700">100</td>
          <td class="p-2 border grade font-semibold text-green-600">4.0</td>
          <td class="p-2 border status">ปกติ</td>

          <td class="p-2 border sticky-col-4 col-action">
            <button class="deleteRow text-red-600">ลบ</button>
          </td>

        </tr>
        @endforeach

      </tbody>
    </table>
    </div>

    <div class="flex justify-end mt-4 space-x-3">
      <button id="addRow" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg">
        เพิ่มนักเรียน
      </button>
      <button id="saveBtn" class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded-lg">
        บันทึกทั้งหมด
      </button>
    </div>

  </div>
</div>


<!-- ======================= JS ======================= -->
<script>

  const table = document.querySelector("#evaluationTable tbody");
  const addRowBtn = document.getElementById("addRow");

  addRowBtn.addEventListener("click", () => {

    const row = document.createElement("tr");
    row.className = "hover:bg-blue-50 transition";

    let comps = "";
    for (let i=0;i<8;i++)
      comps += `<td class='p-2 border'><input type='number' class='input-cell comp' value='0'></td>`;

    let evals = "";
    for (let i=0;i<5;i++)
      evals += `<td class='p-2 border'><input type='number' class='input-cell eval' value='0'></td>`;

    let chars = "";
    for (let i=0;i<8;i++)
      chars += `<td class='p-2 border'><input type='number' class='input-cell char' value='0'></td>`;

    row.innerHTML = `
      <td class="p-2 border sticky-col-1 col-no"></td>
      <td class="p-2 border sticky-col-2 col-id"><input class='input-cell text-center'></td>
      <td class="p-2 border sticky-col-3 col-name text-left px-3"><input class='input-cell'></td>

      ${comps}

      <td class="p-2 border midterm">0</td>

      <td class="p-2 border"><input type="number" class="input-cell final" value="0"></td>

      ${evals}

      ${chars}

      <td class="p-2 border total">0</td>
      <td class="p-2 border grade">0.0</td>
      <td class="p-2 border status">-</td>

      <td class="p-2 border sticky-col-4 col-action">
        <button class="deleteRow text-red-600">ลบ</button>
      </td>
    `;

    table.appendChild(row);
    updateDeleteButtons();
    updateRowNumbers();
    updateAutoCalc();
  });

  function updateDeleteButtons() {
    document.querySelectorAll(".deleteRow").forEach(btn => {
      btn.onclick = () => {
        btn.closest("tr").remove();
        updateRowNumbers();
      };
    });
  }

  function updateRowNumbers() {
    document.querySelectorAll("#evaluationTable tbody tr").forEach((tr, i) => {
      tr.children[0].textContent = i + 1;
    });
  }

  function updateAutoCalc() {
    document.querySelectorAll("#evaluationTable tbody tr").forEach(tr => {
      function calc() {
        let mid = 0;
        tr.querySelectorAll(".comp").forEach(c => mid += Number(c.value));
        if (mid > 80) mid = 80;

        tr.querySelector(".midterm").textContent = mid;
        let final = Number(tr.querySelector(".final").value);
        if (final > 20) final = 20;

        let total = mid + final;
        tr.querySelector(".total").textContent = total;

        let grade = total >= 80 ? 4 :
                    total >= 75 ? 3.5 :
                    total >= 70 ? 3 :
                    total >= 65 ? 2.5 :
                    total >= 60 ? 2 :
                    total >= 55 ? 1.5 :
                    total >= 50 ? 1 : 0;

        tr.querySelector(".grade").textContent = grade.toFixed(1);
        tr.querySelector(".status").textContent = grade >= 1 ? "ปกติ" : "ตก";
      }

      tr.querySelectorAll("input").forEach(i => i.oninput = calc);
      calc();
    });
  }

  updateDeleteButtons();
  updateAutoCalc();
  updateRowNumbers();

</script>

<!-- ======================= CSS ======================= -->
<style>
  /* Sticky Header */
  .sticky-header th {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #1e40af;
    color: white;
  }

  /* คอลัมน์ความกว้างคงที่ */
  .col-no { width: 60px; }
  .col-id { width: 100px; }
  .col-name { width: 280px; }
  .col-action { width: 80px; }

  /* Sticky Columns */
  .sticky-col-1 { position: sticky; left: 0; z-index: 45; background: white; }
  .sticky-col-2 { position: sticky; left: 60px; z-index: 45; background: white; }
  .sticky-col-3 { position: sticky; left: 160px; z-index: 45; background: white; }
  .sticky-col-4 { position: sticky; right: 0; z-index: 45; background: white; }

  /* Input Style */
  .input-cell {
    width: 100%;
    padding: 4px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-align: center;
    transition: 0.2s;
  }
  .input-cell:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px #bfdbfe;
    outline: none;
  }
</style>

@endsection
