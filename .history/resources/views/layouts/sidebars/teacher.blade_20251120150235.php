<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
    <div>

        <!-- Profile -->
        <div class="mb-6">
            <h1 id="sidebarTeacherName" class="text-lg font-bold">
                {{ auth()->user()->name ?? 'ครูผู้สอน' }}
            </h1>
            <div id="sidebarTeacherCode" class="text-sm text-gray-300">
                {{ auth()->user()->teacher_code ?? 'T0001' }}
            </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">

            <!-- Dashboard -->
            <a href="{{ route('dashboard.teacher') }}"
                class="nav-item {{ request()->routeIs('dashboard.teacher') ? 'active' : '' }}">
                แดชบอร์ด
            </a>

            <!-- Attendance -->
            <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">
                บันทึกเวลาเรียน
            </a>

            <!-- Assignments -->
            <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">
                กำหนดชิ้นงาน
            </a>

            <!-- Evaluation -->
            <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">
                ประเมินผลการเรียน
            </a>

            <!-- Create Course -->
            <a href="{{ route('teacher.course-create') }}" class="nav-item
               {{
    request()->routeIs('teacher.course-create') ||
    request()->routeIs('teacher.courses')
    ? 'active'
    : ''
               }}">
                สร้างหลักสูตร
            </a>

            <!-- NEW: Course Detail -->
            <a href="{{ route('course.detail', session('current_course_id', 1)) }}" class="nav-item
   {{ request()->routeIs('course.detail') ? 'active' : '' }}">
                รายละเอียดหลักสูตร
            </a>



        </nav>
    </div>

    <!-- Buttons -->
    <div class="mt-6 space-y-3">

        <!-- Profile Modal Button -->
        <button onclick="openProfileModal()"
            class="w-full py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition text-center">
            จัดการโปรไฟล์
        </button>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
                ออกจากระบบ
            </button>
        </form>
    </div>
</aside>