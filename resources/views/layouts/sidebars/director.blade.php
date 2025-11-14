<aside class="w-64 bg-gray-800 text-white shadow-xl rounded-r-3xl p-6 flex flex-col justify-between">
  <div>
    <h1 class="text-lg font-bold mb-8">ผู้อำนวยการ</h1>

    <nav class="space-y-2">
      <a href="/dashboard/director" class="nav-item {{ request()->is('dashboard/director') ? 'active' : '' }}">แดชบอร์ด</a>
      <a href="/evaluation" class="nav-item {{ request()->is('evaluation') ? 'active' : '' }}">ประเมินผลการเรียน</a>
      <a href="/summary" class="nav-item {{ request()->is('summary') ? 'active' : '' }}">สรุปผลสัมฤทธิ์</a>
      <a href="/chart-summary" class="nav-item {{ request()->is('chart-summary') ? 'active' : '' }}">แผนภูมิสรุป</a>
    </nav>
  </div>

  <form method="POST" action="{{ route('logout') }}" class="mt-8">
  @csrf
  <button type="submit" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition">
    ออกจากระบบ
  </button>
</form>

</aside>
