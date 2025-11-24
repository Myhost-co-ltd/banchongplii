<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบผู้ดูแล')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    <!-- ===================== -->
    <!--      SIDEBAR FIXED    -->
    <!-- ===================== -->
    <aside class="w-64 bg-gray-800 text-white shadow-xl p-6 flex flex-col justify-between 
                  fixed left-0 top-0 bottom-0 rounded-r-3xl z-50">

        <div>
            <h1 class="text-lg font-bold mb-8 leading-tight select-none">ผู้ดูแลระบบ</h1>

            <nav class="space-y-2">

                <!-- Dashboard -->
                <a href="{{ route('dashboard.admin') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('dashboard.admin') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}">
                    แดชบอร์ด
                </a>

                <!-- Student -->
                <a href="{{ route('admin.add-student') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.add-student') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}">
                    จัดการข้อมูลนักเรียน
                </a>

                <!-- Teacher -->
                <a href="{{ route('admin.add-teacher') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.add-teacher') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}">
                    จัดการข้อมูลครู
                </a>

                <!-- Course -->
                <a href="{{ route('admin.courses.index') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.courses.*') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}">
                    จัดการหลักสูตร
                </a>

            </nav>
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit"
                    class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-200">
                ออกจากระบบ
            </button>
        </form>
    </aside>

    <!-- ===================== -->
    <!--       CONTENT         -->
    <!-- ===================== -->
    <main class="ml-64 min-h-screen p-10 bg-gray-50">

        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 min-h-[calc(100vh-120px)] overflow-hidden">
            @yield('content')
        </div>

    </main>

    @stack('scripts')
</body>
</html>
