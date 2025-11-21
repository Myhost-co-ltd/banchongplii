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
                ????????
            </a>

            <a href="/attendance" class="nav-item {{ request()->is('attendance') ? 'active' : '' }}">
                ???????????????
            </a>

            <a href="/assignments" class="nav-item {{ request()->is('assignments') ? 'active' : '' }}">
                ??????????
            </a>

            <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">
                บันทึกผลการเรียน
            </a>

            <a href="{{ route('teacher.course-create') }}"
               class="nav-item {{ request()->routeIs('teacher.course-create') ? 'active' : '' }}">
                สร้างหลักสูตรการสอน
            </a>

            <a href="{{ route('course.detail') }}"
               class="nav-item {{ request()->routeIs('course.detail') ? 'active' : '' }}">
                รายละเอียดหลักสูตร
            </a>
        </nav>
    </div>

    <div class="mt-6 space-y-3">
        <button onclick="openProfileModal()"
            class="w-full py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition text-center">
            ?????????????
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
                ??????????
            </button>
        </form>
    </div>
</aside>
