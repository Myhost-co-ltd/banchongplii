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
            <a href="/attendance"
               class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">
                บันทึกเวลาเรียน
            </a>

            <!-- Assignments -->
            <a href="/assignments"
               class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">
                กำหนดชิ้นงาน
            </a>

            <!-- Evaluation -->
            <a href="/evaluation"
               class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">
                ประเมินผลการเรียน
            </a>

            <!-- Create Course -->
            <a href="{{ route('teacher.course-create') }}"
               class="nav-item
               {{
                    request()->routeIs('teacher.course-create') ||
                    request()->routeIs('teacher.courses')
                    ? 'active'
                    : ''
               }}">
                สร้างหลักสูตร
            </a>


            <!-- =============================== -->
            <!-- Dropdown รายละเอียดหลักสูตร -->
            <!-- =============================== -->

            <button onclick="toggleCourseList()"
                class="nav-item w-full text-left
                {{ request()->routeIs('course.detail') ? 'active' : '' }}">
                รายละเอียดหลักสูตร ▼
            </button>

            <div id="courseListDropdown" class="hidden ml-4 mt-2 space-y-2">

                @php
                    // mock data (เปลี่ยนได้ภายหลังเป็น db จริง)
                    $myCourses = [
                        ['id' => 0, 'name' => 'คณิตศาสตร์พื้นฐาน ป.1'],
                        ['id' => 1, 'name' => 'ภาษาไทยเพื่อการสื่อสาร ป.1'],
                    ];
                @endphp

                @foreach ($myCourses as $course)
                    <a href="{{ route('course.detail', $course['id']) }}"
                       class="block bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-xl text-sm">
                        {{ $course['name'] }}
                    </a>
                @endforeach

            </div>

        </nav>
    </div>

    <!-- Buttons -->
    <div class="mt-6 space-y-3">

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
