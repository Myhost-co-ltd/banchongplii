<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
  <div>
    <h1 class="text-lg font-bold mb-8">ผอ .</h1>

    <nav class="space-y-2">
      <a href="/dashboard/director" class="nav-item {{ request()->is('dashboard/director') ? 'active' : '' }}">แดชบอร์ดผู้อำนวยการ</a>
      <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">แบบบันทึกผลการเรียน </a>
      <a href="/summary" class="nav-item {{ request()->is('summary') ? 'active' : '' }}">สรุปผลสัมฤทธิ์รายวิชา</a>
      <a href="{{ route('director.teacher-plans') }}" class="nav-item {{ request()->routeIs('director.teacher-plans', 'director.course-detail') ? 'active' : '' }}">แผนการสอนของครู</a>
      <a href="/chart-summary" class="nav-item {{ request()->is('chart-summary') ? 'active' : '' }}">แผนภูมิ</a>
      
    </nav>
  </div>

  <form method="POST" action="{{ route('logout') }}" class="mt-8">
  @csrf
  <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
    
  </button>
  </form>

</aside>

