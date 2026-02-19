@php
  $sidebarLogoCandidates = ['png', 'jpg', 'jpeg'];
  $sidebarLogoPath = null;
  $sidebarLogoUrl = asset('images/school-logo-bcp.png');
  foreach ($sidebarLogoCandidates as $ext) {
    $candidatePath = public_path("images/school-logo-bcp.$ext");
    if (file_exists($candidatePath)) {
      $sidebarLogoPath = $candidatePath;
      $sidebarLogoUrl = asset("images/school-logo-bcp.$ext");
      break;
    }
  }
  $sidebarLogoVersion = $sidebarLogoPath ? filemtime($sidebarLogoPath) : null;
  $sidebarLogoUrl = $sidebarLogoUrl . ($sidebarLogoVersion ? ('?v=' . $sidebarLogoVersion) : '');
@endphp
<aside id="appSidebar" class="sidebar-panel">
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center overflow-hidden">
          <img src="{{ $sidebarLogoUrl }}" alt="โลโก้โรงเรียน"
               class="w-full h-full object-contain"
               title="โลโก้โรงเรียน" data-i18n-title-th="โลโก้โรงเรียน" data-i18n-title-en="School logo">
        </div>
        <h1 class="text-lg font-bold" data-i18n-th="ผอ." data-i18n-en="Director">ผอ.</h1>
      </div>
      <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" aria-pressed="false"
              title="ซ่อน/แสดงเมนู" data-i18n-title-th="ซ่อน/แสดงเมนู" data-i18n-title-en="Hide/Show menu">
        ☰
      </button>
    </div>

    <nav class="space-y-2">
      <a href="/dashboard/director"
         class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
         {{ request()->routeIs('dashboard.director', 'dashboard.admin')
              ? 'bg-white/15 text-white font-semibold'
              : 'text-white/80 hover:text-white hover:bg-white/10' }}"
         data-i18n-th="แดชบอร์ดผู้อำนวยการ" data-i18n-en="Director Dashboard">แดชบอร์ดผู้อำนวยการ</a>
      {{-- <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}" data-i18n-th="แบบบันทึกผลการเรียน" data-i18n-en="Gradebook">แบบบันทึกผลการเรียน</a>
      <a href="/summary" class="nav-item {{ request()->is('summary') ? 'active' : '' }}" data-i18n-th="สรุปผลสัมฤทธิ์รายวิชา" data-i18n-en="Subject Summary">สรุปผลสัมฤทธิ์รายวิชา</a> --}}
      <a href="{{ route('director.students') }}"
         class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
         {{ request()->routeIs('director.students')
              ? 'bg-white/15 text-white font-semibold'
              : 'text-white/80 hover:text-white hover:bg-white/10' }}"
         data-i18n-th="ดูรายชื่อนักเรียน" data-i18n-en="Student List">ดูรายชื่อนักเรียน</a>
      <a href="{{ route('director.teacher-plans') }}"
         class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
         {{ request()->routeIs('director.teacher-plans', 'director.course-detail')
              ? 'bg-white/15 text-white font-semibold'
              : 'text-white/80 hover:text-white hover:bg-white/10' }}"
         data-i18n-th="แผนการสอนของครู" data-i18n-en="Teacher Plans">แผนการสอนของครู</a>
      {{-- <a href="/chart-summary" class="nav-item {{ request()->is('chart-summary') ? 'active' : '' }}" data-i18n-th="แผนภูมิ" data-i18n-en="Charts">แผนภูมิ</a> --}}
    </nav>

  </div>

  <div class="mt-6 space-y-2">
    <button onclick="openProfileModal()"
            class="w-full py-2.5 rounded-xl bg-transparent text-white/85 hover:text-white hover:bg-white/10 transition-colors text-center">
      <span data-i18n-th="จัดการโปรไฟล์" data-i18n-en="Manage profile">จัดการโปรไฟล์</span>
    </button>

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
      <button type="submit" class="w-full py-2.5 rounded-xl bg-transparent text-rose-200 hover:text-white hover:bg-rose-500/20 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/60">
        <span data-i18n-th="ออกจากระบบ" data-i18n-en="Logout">ออกจากระบบ</span>
      </button>
    </form>
  </div>
</aside>
