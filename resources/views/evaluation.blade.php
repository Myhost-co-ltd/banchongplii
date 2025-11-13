@extends('layouts.layout')

@section('title', 'ประเมินผลการเรียน | โรงเรียนบ้านช่องพลี')

@section('content')
<div class="p-6 bg-gray-50 rounded-3xl shadow-inner space-y-6">

  <!-- 🔹 หัวข้อ -->
  <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
    แบบบันทึกผลการเรียน...
    
  </h2>

  <!-- ✅ ตาราง -->
  <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-200">
    <div class="overflow-x-auto relative">
    <table id="evaluationTable" class="min-w-[1600px] w-full text-sm text-center border-collapse table-fixed">
      <thead class="bg-blue-700 text-white sticky-header">
        <tr>
          <th class="p-2 border sticky-col-1" style="width:56px" rowspan="2">เลขที่</th>
          <th class="p-2 border sticky-col-2" style="width:96px" rowspan="2">รหัส</th>
          <th class="p-2 border sticky-col-3 text-left" style="width:320px" rowspan="2">ชื่อ - สกุล</th>

          <th class="p-2 border" colspan="8">คะแนนระหว่างภาค (รวม 80)</th>
          <th class="p-2 border" rowspan="2">รวม<br/>(80)</th>
          <th class="p-2 border" rowspan="2">สอบปลายภาค<br/>(20)</th>

          <th class="p-2 border" colspan="5">การประเมินผลปลายภาคเรียน</th>
          <th class="p-2 border" colspan="8">คุณลักษณะอันพึงประสงค์</th>

          <th class="p-2 border" rowspan="2">รวม<br/>(100)</th>
          <th class="p-2 border" rowspan="2">เกรด</th>
          <th class="p-2 border" rowspan="2">สถานะ</th>
          <th class="p-2 border sticky-col-4" style="width:80px" rowspan="2">จัดการ</th>
        </tr>
        <tr>
          <!-- 8 component columns -->
          <th class="p-2 border">1</th>
          <th class="p-2 border">2</th>
          <th class="p-2 border">3</th>
          <th class="p-2 border">4</th>
          <th class="p-2 border">5</th>
          <th class="p-2 border">6</th>
          <th class="p-2 border">7</th>
          <th class="p-2 border">8</th>

          <!-- eval 5 cols -->
          <th class="p-2 border">1</th>
          <th class="p-2 border">2</th>
          <th class="p-2 border">3</th>
          <th class="p-2 border">4</th>
          <th class="p-2 border">5</th>

          <!-- char 8 cols -->
          <th class="p-2 border">1</th>
          <th class="p-2 border">2</th>
          <th class="p-2 border">3</th>
          <th class="p-2 border">4</th>
          <th class="p-2 border">5</th>
          <th class="p-2 border">6</th>
          <th class="p-2 border">7</th>
          <th class="p-2 border">8</th>
        </tr>
      </thead>
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
          <td class="p-2 border sticky-col-1">{{ $s['no'] }}</td>
          <td class="p-2 border sticky-col-2">{{ $s['id'] }}</td>
          <td class="p-2 border text-left px-3 sticky-col-3">{{ $s['name'] }}</td>

          <!-- 8 components (each up to 10 points by default) -->
          @for ($i = 0; $i < 8; $i++)
            <td class="p-2 border"><input type="number" class="input-cell text-center comp" value="10" min="0" max="10"></td>
          @endfor

          <!-- midterm sum (80) -->
          <td class="p-2 border midterm font-semibold text-blue-700">80</td>

          <!-- final (20) -->
          <td class="p-2 border"><input type="number" class="input-cell text-center final" value="20" min="0" max="20"></td>

          <!-- การประเมินผลปลายภาคเรียน (5 small cols) -->
          @for ($j = 0; $j < 5; $j++)
            <td class="p-2 border"><input type="number" class="input-cell text-center eval" value="3" min="0" max="5"></td>
          @endfor

          <!-- คุณลักษณะอันพึงประสงค์ (8 small cols) -->
          @for ($k = 0; $k < 8; $k++)
            <td class="p-2 border"><input type="number" class="input-cell text-center char" value="3" min="0" max="5"></td>
          @endfor

          <td class="p-2 border total font-semibold text-blue-700">100</td>
          <td class="p-2 border grade font-semibold text-green-600">4.0</td>
          <td class="p-2 border status font-medium text-gray-700">ปกติ</td>
          <td class="p-2 border sticky-col-4">
            <button class="deleteRow text-red-600 hover:text-red-800">ลบ</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>

  <!-- ✅ ปุ่ม -->
  <div class="flex justify-end mt-4 space-x-3">
    <button id="addRow" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition">
      เพิ่มนักเรียน
    </button>
    <button id="saveBtn" class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded-lg transition">
       บันทึกทั้งหมด
    </button>
  </div>
</div>

<!-- ✅ Script -->
<script>
  const table = document.querySelector("#evaluationTable tbody");
  const addRowBtn = document.getElementById("addRow");

  // ฟังก์ชันเพิ่มแถวใหม่ (สร้างคอลัมน์ทั้งหมดให้ตรงกับ header)
  addRowBtn.addEventListener("click", () => {
    const rowCount = table.rows.length + 1;
    const row = document.createElement("tr");
    row.className = "hover:bg-blue-50 transition";

    let comps = '';
    for (let i = 0; i < 8; i++) comps += `<td class="p-2 border"><input type="number" class="input-cell text-center comp" min="0" max="10" value="0"></td>`;

    let evals = '';
    for (let i = 0; i < 5; i++) evals += `<td class="p-2 border"><input type="number" class="input-cell text-center eval" min="0" max="5" value="0"></td>`;

    let chars = '';
    for (let i = 0; i < 8; i++) chars += `<td class="p-2 border"><input type="number" class="input-cell text-center char" min="0" max="5" value="0"></td>`;

    row.innerHTML = `
      <td class="p-2 border sticky-col-1">${rowCount}</td>
      <td class="p-2 border sticky-col-2"><input type="text" class="input-cell text-center" placeholder="รหัส"></td>
      <td class="p-2 border text-left px-3 sticky-col-3"><input type="text" class="input-cell" placeholder="ชื่อนักเรียน"></td>
      ${comps}
      <td class="p-2 border midterm font-semibold text-blue-700">0</td>
      <td class="p-2 border"><input type="number" class="input-cell text-center final" min="0" max="20" value="0"></td>
      ${evals}
      ${chars}
      <td class="p-2 border total font-semibold text-blue-700">0</td>
      <td class="p-2 border grade font-semibold text-green-600">-</td>
      <td class="p-2 border status font-medium text-gray-700">-</td>
      <td class="p-2 border sticky-col-4"><button class="deleteRow text-red-600 hover:text-red-800">ลบ</button></td>
    `;

    table.appendChild(row);
    updateRowNumbers();
    updateDeleteButtons();
    updateGradeSystem();
  });

  // ฟังก์ชันลบแถว
  function updateDeleteButtons() {
    document.querySelectorAll(".deleteRow").forEach(btn => {
      btn.onclick = function() {
        this.closest("tr").remove();
        updateRowNumbers();
      };
    });
  }

  // อัปเดตเลขที่
  function updateRowNumbers() {
    document.querySelectorAll("#evaluationTable tbody tr").forEach((tr, idx) => {
      tr.children[0].textContent = idx + 1;
    });
  }

  // ✅ อัปเดตคะแนนรวม + เกรดอัตโนมัติ
  function updateGradeSystem() {
    document.querySelectorAll('#evaluationTable tbody tr').forEach(tr => {
      const compInputs = tr.querySelectorAll('.comp');
      const finalInput = tr.querySelector('.final');

      function recalc() {
        let mid = 0;
        compInputs.forEach(c => mid += parseFloat(c.value) || 0);
        if (mid > 80) mid = 80;
        tr.querySelector('.midterm').textContent = mid;

        const final = Math.min(20, parseFloat(finalInput ? finalInput.value : 0) || 0);
        const total = mid + final;
        tr.querySelector('.total').textContent = total;

        // คำนวณเกรด
        let grade = 0;
        if (total >= 80) grade = 4.0;
        else if (total >= 75) grade = 3.5;
        else if (total >= 70) grade = 3.0;
        else if (total >= 65) grade = 2.5;
        else if (total >= 60) grade = 2.0;
        else if (total >= 55) grade = 1.5;
        else if (total >= 50) grade = 1.0;
        else grade = 0;

        const gradeCell = tr.querySelector('.grade');
        gradeCell.textContent = grade > 0 ? grade.toFixed(1) : '0.0';
        gradeCell.className = 'grade p-2 border font-semibold ' + (grade >= 1 ? 'text-green-600' : 'text-red-500');
        tr.querySelector('.status').textContent = grade >= 1 ? 'ปกติ' : 'ตก';
      }

      compInputs.forEach(i => i.addEventListener('input', recalc));
      if (finalInput) finalInput.addEventListener('input', recalc);

      // initial calc so default values reflect
      recalc();
    });
  }

  updateDeleteButtons();
  updateGradeSystem();

  // ✅ ปุ่มบันทึก
  document.getElementById("saveBtn").addEventListener("click", () => {
    const data = [];
    document.querySelectorAll("#evaluationTable tbody tr").forEach(tr => {
      const id = tr.querySelector('td:nth-child(2) input') ? tr.querySelector('td:nth-child(2) input').value : tr.children[1].textContent.trim();
      const name = tr.querySelector('td:nth-child(3) input') ? tr.querySelector('td:nth-child(3) input').value : tr.children[2].textContent.trim();
      const comps = Array.from(tr.querySelectorAll('.comp')).map(i => parseFloat(i.value) || 0);
      const evals = Array.from(tr.querySelectorAll('.eval')).map(i => parseFloat(i.value) || 0);
      const chars = Array.from(tr.querySelectorAll('.char')).map(i => parseFloat(i.value) || 0);
      const midterm = parseFloat(tr.querySelector('.midterm').textContent) || 0;
      const final = parseFloat(tr.querySelector('.final').value) || 0;
      const total = parseFloat(tr.querySelector('.total').textContent) || 0;
      const grade = tr.querySelector('.grade').textContent;
      const status = tr.querySelector('.status').textContent;

      data.push({ id, name, comps, evals, chars, midterm, final, total, grade, status });
    });
    console.log("ข้อมูลที่บันทึก:", data);
    alert("✅ บันทึกผลการเรียนสำเร็จ (Log ดูใน Console)");
  });
</script>

<!-- ✅ สไตล์ input -->
<style>
  /* Sticky columns and header */
  .sticky-header th {
    position: sticky;
    top: 0;
    z-index: 60;
    background: #1e40af; /* same as header bg */
    color: white;
  }
  .sticky-col-1 {
    position: sticky;
    left: 0;
    z-index: 55;
    background: white;
  }
  .sticky-col-2 {
    position: sticky;
    left: 56px; /* width of col1 */
    z-index: 55;
    background: white;
  }
  .sticky-col-3 {
    position: sticky;
    left: 152px; /* col1 + col2 */
    z-index: 55;
    background: white;
  }
  .sticky-col-4 {
    position: sticky;
    right: 0;
    z-index: 50;
    background: white;
  }

  .input-cell {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 4px 6px;
    font-size: 0.875rem;
    transition: 0.2s;
  }
  .input-cell:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px #bfdbfe;
  }
</style>
@endsection
