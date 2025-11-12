<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'ระบบโรงเรียน')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen flex overflow-hidden bg-gray-100 font-sans">

  <!-- ✅ Sidebar -->
  <aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
    <div>
      <h1 class="text-lg font-bold mb-8 leading-tight">โรงเรียนบ้านช่องพลี</h1>
      <nav class="space-y-2">
  <a href="/dashboard"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('dashboard') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    ข้อมูลนักเรียน
  </a>

  <a href="/attendance"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('attendance') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    บันทึกเวลาเรียน
  </a>

  <a href="/evaluation"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('evaluation') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    ประเมินผลการเรียน
  </a>

  <a href="/summary"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('summary') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    สรุปผลสัมฤทธิ์
  </a>

  <a href="/assignments"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('assignments') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    กำหนดชิ้นงาน
  </a>

  <a href="/chart"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('chart') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    แผนภูมิสรุป
  </a>

  <a href="/course-structure"
    class="block py-2.5 px-4 rounded-xl transition 
    {{ request()->is('course-structure') ? 'bg-gray-700 font-medium' : 'hover:bg-gray-700' }}">
    โครงสร้างรายวิชา
  </a>
</nav>

    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-8">
      @csrf
      <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
        ออกจากระบบ
      </button>
    </form>
  </aside>

  <!-- ✅ Main -->
  <main class="flex-1 flex flex-col p-10 bg-gray-50 overflow-hidden h-screen">
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 flex flex-col flex-1 overflow-hidden">
      @yield('content')
    </div>
  </main>

</body>
</html>
