<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'ระบบโรงเรียน')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen flex overflow-hidden bg-gray-100 font-sans">

  {{-- ⭐ เลือก Sidebar ตาม role --}}
  @auth
    @php
      $roleName = Auth::user()->role_name;
      $sidebarView = $roleName ? 'layouts.sidebars.' . $roleName : null;
    @endphp
    @if ($sidebarView && view()->exists($sidebarView))
      @include($sidebarView)
    @else
      @include('layouts.sidebars.teacher')
    @endif
  @endauth

  <!-- Main -->
  <main class="flex-1 flex flex-col p-10 bg-gray-50 overflow-hidden h-screen">
    <div class="flex flex-col flex-1 overflow-hidden">
      @yield('content')
    </div>
  </main>

</body>
</html>
