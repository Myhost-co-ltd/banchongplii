@extends('layouts.layout')

@section('title', 'แผนภูมิสรุป | โรงเรียนบ้านช่องพลี')

@section('content')
<div class="relative flex h-[calc(100vh-5rem)] overflow-hidden">

  <!-- 🔹 ส่วนกราฟหลัก (เลื่อนขึ้นลงได้) -->
  <div class="flex-1 overflow-y-auto p-6 bg-yellow-100 rounded-3xl shadow-inner space-y-8">
    <h2 class="text-2xl font-bold text-gray-800 text-center">
      แผนภูมิแสดงจำนวนนักเรียนที่ได้รับผลการเรียน รายวิชา วิทยาการคำนวณ 2 รหัสวิชา ว32102
    </h2>

    <!-- 🔸 กราฟแท่ง -->
    <div class="bg-white p-4 rounded-xl shadow-md">
      <canvas id="barChart" height="100"></canvas>
    </div>

    <!-- 🔸 กราฟวงกลมระดับคะแนน -->
    <div class="bg-white p-4 rounded-xl shadow-md">
      <h3 class="text-center font-semibold text-gray-700 mb-2">
        แผนภูมิแสดงร้อยละของนักเรียนที่ได้รับผลการเรียนแต่ละระดับ
      </h3>
      <canvas id="gradeChart" height="120"></canvas>
    </div>

    <!-- 🔸 กราฟวงกลมผ่าน/ไม่ผ่าน -->
    <div class="bg-white p-4 rounded-xl shadow-md mb-10">
      <h3 class="text-center font-semibold text-gray-700 mb-2">
        แผนภูมิแสดงร้อยละของนักเรียนที่ได้ผลการเรียนระดับ (ดี) ขึ้นไป
      </h3>
      <canvas id="passChart" height="120"></canvas>
    </div>
  </div>

  <!-- 🔹 แถบปุ่มด้านขวา -->
  <div class="w-40 bg-pink-500 text-white flex flex-col items-center justify-start p-4 space-y-4 rounded-l-3xl shadow-lg sticky top-0 h-[calc(100vh-5rem)]">
    <button onclick="window.location.href='/dashboard'" 
      class="w-full bg-pink-600 hover:bg-pink-700 rounded-lg py-2 transition">
      กลับหน้าหลัก
    </button>

    <button onclick="window.print()" 
      class="w-full bg-pink-600 hover:bg-pink-700 rounded-lg py-2 transition">
      พิมพ์ทั้งหมด
    </button>

    <button onclick="window.print()" 
      class="w-full bg-pink-600 hover:bg-pink-700 rounded-lg py-2 transition">
       พรีวิวก่อนพิมพ์
    </button>

    <button id="saveCharts" 
      class="w-full bg-pink-600 hover:bg-pink-700 rounded-lg py-2 transition">
       บันทึก
    </button>
  </div>
</div>

<!-- ✅ Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // 🔸 กราฟแท่ง
  const barCtx = document.getElementById('barChart');
  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: ['4', '3.5', '3', '2.5', '2', '1.5', '1', '0'],
      datasets: [{
        label: 'จำนวนนักเรียน (คน)',
        data: [11, 4, 2, 1, 0, 0, 0, 0],
        backgroundColor: '#2563eb'
      }]
    },
    options: {
      plugins: {
        title: { display: true, text: 'จำนวนผู้ได้ระดับผลการเรียนแต่ละเกรด', font: { size: 16 } },
        legend: { display: false }
      },
      scales: { y: { beginAtZero: true } }
    }
  });

  // 🔸 กราฟวงกลมระดับคะแนน
  const gradeCtx = document.getElementById('gradeChart');
  const gradeChart = new Chart(gradeCtx, {
    type: 'pie',
    data: {
      labels: ['4', '3.5', '3', '2.5', '2', '1.5', '1'],
      datasets: [{
        data: [57.89, 21.05, 10.53, 5.26, 5.26, 0, 0],
        backgroundColor: [
          '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#f87171', '#ef4444'
        ]
      }]
    },
    options: {
      plugins: { legend: { position: 'right' } }
    }
  });

  // 🔸 กราฟวงกลมผ่าน/ไม่ผ่าน
  const passCtx = document.getElementById('passChart');
  const passChart = new Chart(passCtx, {
    type: 'pie',
    data: {
      labels: ['ผ่านเกณฑ์ (ดีขึ้นไป)', 'ไม่ผ่านเกณฑ์'],
      datasets: [{
        data: [89.47, 10.53],
        backgroundColor: ['#22c55e', '#ef4444']
      }]
    },
    options: {
      plugins: { legend: { position: 'right' } }
    }
  });

  // 🔸 ปุ่มบันทึกทั้งหมดเป็น PNG
  document.getElementById('saveCharts').addEventListener('click', () => {
    const charts = [
      { chart: barChart, name: "barChart.png" },
      { chart: gradeChart, name: "gradeChart.png" },
      { chart: passChart, name: "passChart.png" }
    ];
    charts.forEach(c => {
      const link = document.createElement('a');
      link.download = c.name;
      link.href = c.chart.toBase64Image();
      link.click();
    });
    alert("✅ บันทึกแผนภูมิทั้งหมดเรียบร้อย!");
  });
</script>
@endsection
