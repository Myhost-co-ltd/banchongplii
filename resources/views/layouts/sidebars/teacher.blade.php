<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
    <div>
        <div class="mb-6">
            <h1 id="sidebarTeacherName" class="text-lg font-bold">
                {{ auth()->user()->name ?? '?????????' }}
            </h1>
            <div id="sidebarTeacherCode" class="text-sm text-gray-300">
                {{ auth()->user()->teacher_code ?? 'T0001' }}
            </div>
        </div>

        <nav class="space-y-2">
            <a href="{{ route('dashboard.teacher') }}"
               class="nav-item {{ request()->routeIs('dashboard.teacher') ? 'active' : '' }}">
               แดชบอร์ดครู
            </a>

            <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">
               บันทึกเวลาเรียน
            </a>

            <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">
                กำหนดชิ้นงาน
            </a>

            <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">
               แบบบันทึกผลการเรียน 
            </a>

            <a href="{{ route('teacher.course-create') }}"
               class="nav-item {{ request()->routeIs('teacher.course-create') ? 'active' : '' }}">
               สร้างหลักสูตรการสอน
            </a>

            <!-- Course Detail -->
            <a href="{{ route('course.detail') }}"
               class="nav-item {{ request()->routeIs('course.detail') ? 'active' : '' }}">
               รายละเอียดหลักสูตร
            </a>
        </nav>
    </div>

    <div class="mt-6 space-y-3">

        <!-- Profile Modal Button -->
        <button onclick="openProfileModal()"
            class="w-full py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition text-center">
            จัดการโปรไฟล์
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
                ออกจากระบบ
            </button>
        </form>
    </div>
</aside>


<script>
function toggleCourseList() {
    document.getElementById('courseListDropdown').classList.toggle('hidden');
}
</script>
