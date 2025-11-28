<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
  <div>
    <h1 class="text-lg font-bold mb-8">Superadmin</h1>

    <nav class="space-y-2">
      <a href="/dashboard/superadmin" class="nav-item {{ request()->is('dashboard/superadmin') ? 'active' : '' }}">ข้อมูลนักเรียน</a>
      {{-- <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">บันทึกเวลาเรียน</a> --}}
      {{-- <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">กำหนดชิ้นงาน</a> --}}
      
      <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">ประเมินผลการเรียน</a>
      <a href="/summary" class="nav-item {{ request()->is('summary') ? 'active' : '' }}">สรุปผลสัมฤทธิ์</a>
      <a href="/chart-summary" class="nav-item {{ request()->is('chart-summary') ? 'active' : '' }}">แผนภูมิสรุป</a>
      <a href="/course-structure" class="nav-item {{ request()->is('course-structure') ? 'active' : '' }}">โครงสร้างรายวิชา</a>
    </nav>
  </div>

  <div class="mt-8 space-y-3">
    <button type="button" data-lang-toggle class="lang-toggle w-full justify-center"
            aria-label="เปลี่ยนภาษา" title="เปลี่ยนภาษา"
            data-i18n-aria-th="เปลี่ยนภาษา" data-i18n-aria-en="Switch language"
            data-i18n-title-th="เปลี่ยนภาษา" data-i18n-title-en="Switch language">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v18m9-9H3m14.5 0a14.5 14.5 0 00-5.5-11 14.5 14.5 0 00-5.5 11 14.5 14.5 0 005.5 11 14.5 14.5 0 005.5-11z" />
      </svg>
      <span data-lang-label>TH</span>
    </button>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
        ออกจากระบบ
      </button>
    </form>
  </div>

</aside>
