<aside id="appSidebar" class="sidebar-panel">
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 id="sidebarTeacherName" class="text-lg font-bold">
                    {{ auth()->user()->name ?? '?????????' }}
                </h1>
                <div id="sidebarTeacherCode" class="text-sm text-gray-300">
                    {{ auth()->user()->teacher_code ?? 'T0001' }}
                </div>
            </div>
            <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" aria-pressed="false"
                    title="ซ่อน/แสดงเมนู" data-i18n-title-th="ซ่อน/แสดงเมนู" data-i18n-title-en="Hide/Show menu">
                ☰
            </button>
    </div>

    <nav class="space-y-2">
        <a href="{{ route('dashboard.teacher') }}"
           class="nav-item {{ request()->routeIs('dashboard.teacher') ? 'active' : '' }}"
           data-i18n-th="แดชบอร์ดครู" data-i18n-en="Teacher Dashboard">
               แดชบอร์ดครู
            </a>

            <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}"
               data-i18n-th="บันทึกเวลาเรียน" data-i18n-en="Attendance">
               บันทึกเวลาเรียน
            </a>

            <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}"
               data-i18n-th="กำหนดชิ้นงาน" data-i18n-en="Assignments">
                กำหนดชิ้นงาน
            </a>

            <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}"
               data-i18n-th="แบบบันทึกผลการเรียน" data-i18n-en="Gradebook">
               แบบบันทึกผลการเรียน 
            </a>

            <a href="{{ route('teacher.course-create') }}"
               class="nav-item {{ request()->routeIs('teacher.course-create') ? 'active' : '' }}"
               data-i18n-th="สร้างหลักสูตรการสอน" data-i18n-en="Create Course">
               สร้างหลักสูตรการสอน
            </a>

            <!-- Course Detail -->
            <a href="{{ route('course.detail') }}"
               class="nav-item {{ request()->routeIs('course.detail') ? 'active' : '' }}"
               data-i18n-th="รายละเอียดหลักสูตร" data-i18n-en="Course Detail">
               รายละเอียดหลักสูตร
            </a>
    </nav>

    </div>

    <div class="mt-6 space-y-3">

        <!-- Profile Modal Button -->
        <button onclick="openProfileModal()"
            class="w-full py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition text-center">
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
            <button type="submit"
                    class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
                <span data-i18n-th="ออกจากระบบ" data-i18n-en="Logout">ออกจากระบบ</span>
            </button>
        </form>
    </div>
</aside>


<script>
function toggleCourseList() {
    document.getElementById('courseListDropdown').classList.toggle('hidden');
}
</script>
