<aside id="appSidebar" class="sidebar-panel">
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-lg font-bold">ผอ.</h1>
      <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" aria-pressed="false" title="ซ่อน/แสดงเมนู">
        ☰
      </button>
    </div>

    <nav class="space-y-2">
      <a href="/dashboard/director" class="nav-item {{ request()->is('dashboard/director') ? 'active' : '' }}" data-i18n-th="แดชบอร์ดผู้อำนวยการ" data-i18n-en="Director Dashboard">แดชบอร์ดผู้อำนวยการ</a>
      <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}" data-i18n-th="แบบบันทึกผลการเรียน" data-i18n-en="Gradebook">แบบบันทึกผลการเรียน</a>
      <a href="/summary" class="nav-item {{ request()->is('summary') ? 'active' : '' }}" data-i18n-th="สรุปผลสัมฤทธิ์รายวิชา" data-i18n-en="Subject Summary">สรุปผลสัมฤทธิ์รายวิชา</a>
      <a href="{{ route('director.teacher-plans') }}" class="nav-item {{ request()->routeIs('director.teacher-plans', 'director.course-detail') ? 'active' : '' }}" data-i18n-th="แผนการสอนของครู" data-i18n-en="Teacher Plans">แผนการสอนของครู</a>
      <a href="/chart-summary" class="nav-item {{ request()->is('chart-summary') ? 'active' : '' }}" data-i18n-th="แผนภูมิ" data-i18n-en="Charts">แผนภูมิ</a>
    </nav>

    <div class="mt-4">
      <button type="button" data-lang-toggle class="lang-toggle w-full justify-center" aria-label="เปลี่ยนภาษา" title="เปลี่ยนภาษา">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v18m9-9H3m14.5 0a14.5 14.5 0 00-5.5-11 14.5 14.5 0 00-5.5 11 14.5 14.5 0 005.5 11 14.5 14.5 0 005.5-11z" />
        </svg>
        <span data-lang-label>TH</span>
      </button>
    </div>
  </div>

  <form method="POST" action="{{ route('logout') }}" class="mt-8">
    @csrf
    <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
      ออกจากระบบ
    </button>
  </form>
</aside>
