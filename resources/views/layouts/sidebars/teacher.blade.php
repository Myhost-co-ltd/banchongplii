<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
  <div>
    <div class="mb-6">
      <h1 id="sidebarTeacherName" class="text-lg font-bold">{{ auth()->user()->name ?? 'ครูผู้สอน' }}</h1>
      <div id="sidebarTeacherCode" class="text-sm text-gray-300">{{ auth()->user()->teacher_code ?? 'T0001' }}</div>
    </div>

    <nav class="space-y-2">
      <a href="/dashboard/teacher" class="nav-item {{ request()->is('dashboard/teacher') ? 'active' : '' }}">แดชบอร์ด</a>
      <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">บันทึกเวลาเรียน</a>
      <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">กำหนดชิ้นงาน</a>
      <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">ประเมินผลการเรียน</a>

      <!-- New menus -->
      <a href="{{ route('teacher.course-create') }}" class="nav-item {{ request()->is('teacher/course-create') || request()->routeIs('teacher.course-create') ? 'active' : '' }}">
        สร้างหลักสูตร
      </a>

    </nav>
  </div>

  <!-- เพิ่ม mt-6 หรือ mt-8 เพื่อดันปุ่มลง -->
 <div class="mt-6 space-y-3">
        <button onclick="openProfileModal()" 
            class="w-full py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition text-center pl-4">
            จัดการโปรไฟล์
        </button>

  <form method="POST" action="{{ route('logout') }}" class="mt-0">
    @csrf
    <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
      ออกจากระบบ
    </button>
  </form>

</aside>


<!-- Profile Modal -->
<div id="profileModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl w-[90%] max-w-md p-6 shadow-xl relative">
    <button onclick="closeProfileModal()" class="absolute top-3 right-3 text-gray-500 text-xl">&times;</button>
    <h2 class="text-xl font-bold text-black mb-4">จัดการโปรไฟล์</h2>

    <!-- Change display name (local only) -->
    <div class="mb-6 pb-6 border-b">
      <label class="font-semibold block mb-1">ชื่อที่แสดง</label>
      <input type="text" id="profileName" class="input w-full mb-2 border border-gray-300 rounded px-3 py-2" placeholder="ชื่อผู้สอน">
    </div>

    <!-- Change password -->
    <div class="space-y-3">
      <label class="font-semibold block mb-1">เปลี่ยนรหัสผ่าน</label>
      <input type="password" id="currentPassword" class="input w-full border border-gray-300 rounded px-3 py-2" placeholder="รหัสผ่านปัจจุบัน">
      <input type="password" id="newPassword" class="input w-full border border-gray-300 rounded px-3 py-2" placeholder="รหัสผ่านใหม่">
      <input type="password" id="confirmPassword" class="input w-full border border-gray-300 rounded px-3 py-2" placeholder="ยืนยันรหัสผ่านใหม่">
      <button onclick="changePasswordMock()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-xl w-full">บันทึกรหัสผ่านใหม่</button>
    </div>

    <button onclick="closeProfileModal()" class="mt-4 bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded-xl w-full">ปิด</button>
  </div>
</div>

<!-- Sidebar helper scripts -->
<script>
  // Initialize sidebar profile from localStorage or blade-provided defaults
  document.addEventListener('DOMContentLoaded', () => {
    const storedName = localStorage.getItem('teacher_name');
    const storedCode = localStorage.getItem('teacher_code');
    if (storedName) document.getElementById('sidebarTeacherName').innerText = storedName;
    if (storedCode) document.getElementById('sidebarTeacherCode').innerText = storedCode;
    // Prefill modal inputs if present
    if (storedName) document.getElementById('profileName').value = storedName;
    if (storedCode) document.getElementById('profileCode').value = storedCode;
  });

  function openCourseModal() { document.getElementById('courseModal').classList.remove('hidden'); }
  function closeCourseModal() { document.getElementById('courseModal').classList.add('hidden'); }
  function applyCourseFilter() {
    const val = document.getElementById('courseFilter').value;
    localStorage.setItem('course_filter', val);
    alert('ตั้งค่าการกรองหลักสูตรเป็น: ' + val + ' (mock)');
    closeCourseModal();
  }

  function openProfileModal() { document.getElementById('profileModal').classList.remove('hidden'); }
  function closeProfileModal() { document.getElementById('profileModal').classList.add('hidden'); }
  function saveProfile() {
    const name = document.getElementById('profileName').value.trim();
    const code = document.getElementById('profileCode').value.trim();
    if (!name || !code) { alert('กรุณากรอกชื่อและรหัส'); return; }
    localStorage.setItem('teacher_name', name);
    localStorage.setItem('teacher_code', code);
    document.getElementById('sidebarTeacherName').innerText = name;
    document.getElementById('sidebarTeacherCode').innerText = code;
    alert('บันทึกโปรไฟล์แล้ว (mock)');
  }

  function changePasswordMock() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    if (!current || !newPass || !confirm) {
      alert('กรุณากรอกข้อมูลให้ครบ');
      return;
    }
    if (newPass !== confirm) {
      alert('รหัสผ่านใหม่ไม่ตรงกัน');
      return;
    }
    alert('เปลี่ยนรหัสผ่าน (mock) สำเร็จ!');
    // Optionally clear fields
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
  }
</script>
