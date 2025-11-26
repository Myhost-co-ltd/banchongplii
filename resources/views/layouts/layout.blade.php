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
    <button id="sidebarToggleFab" type="button" class="sidebar-fab hidden" aria-pressed="false" title="แสดงเมนู">
      ☰
    </button>
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

  {{-- Profile Modal --}}
  <div id="profileModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-40 flex items-center justify-center px-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 relative">
      <button type="button" onclick="closeProfileModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
        ✕
      </button>

      <h2 class="text-2xl font-semibold text-gray-900 mb-2">จัดการโปรไฟล์</h2>
      <p class="text-sm text-gray-500 mb-4">ปรับชื่อผู้ใช้ และเปลี่ยนรหัสผ่านได้จากที่นี่</p>

      @if ($errors->any())
        <div class="mb-4 border border-red-200 bg-red-50 text-red-700 rounded-2xl p-3 text-sm">
          <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (session('status'))
        <div class="mb-4 border border-green-200 bg-green-50 text-green-800 rounded-2xl p-3 text-sm">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อที่แสดง</label>
          <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                 class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
        </div>

        <hr class="my-2">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านปัจจุบัน</label>
          <input type="password" name="current_password"
                 class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                 placeholder="กรอกเมื่อจะเปลี่ยนรหัสผ่าน">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
            <input type="password" name="password"
                   class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="อย่างน้อย 6 ตัวอักษร">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="password_confirmation"
                   class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="ใส่ซ้ำให้ตรงกัน">
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" onclick="closeProfileModal()"
                  class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
            ยกเลิก
          </button>
          <button type="submit"
                  class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
            บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function () {
      const sidebar = document.getElementById('appSidebar');
      const toggleBtn = document.getElementById('sidebarToggle');
      const toggleFab = document.getElementById('sidebarToggleFab');
      const STORAGE_KEY = 'sidebarCollapsed';
      const LANG_KEY = 'appLocale';
      const langButtons = document.querySelectorAll('[data-lang-toggle]');

      function applyState(collapsed) {
        if (!sidebar) return;
        sidebar.classList.toggle('sidebar-collapsed', collapsed);
        if (toggleBtn) toggleBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
        if (toggleFab) {
          toggleFab.classList.toggle('hidden', !collapsed);
          toggleFab.style.display = collapsed ? 'flex' : 'none';
          toggleFab.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
        }
      }

      function loadState() {
        return localStorage.getItem(STORAGE_KEY) === '1';
      }

      function saveState(collapsed) {
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
      }

      applyState(loadState());

      toggleBtn?.addEventListener('click', () => {
        const newState = !sidebar?.classList.contains('sidebar-collapsed');
        applyState(newState);
        saveState(newState);
      });

      toggleFab?.addEventListener('click', () => {
        const newState = false; // always expand when clicking the floating button
        applyState(newState);
        saveState(newState);
      });

      function setLanguage(lang) {
        document.documentElement.setAttribute('lang', lang);
        localStorage.setItem(LANG_KEY, lang);
        langButtons.forEach(btn => {
          btn.setAttribute('aria-label', lang === 'th' ? 'เปลี่ยนเป็นภาษาอังกฤษ' : 'Switch to Thai');
          const labelEl = btn.querySelector('[data-lang-label]');
          if (labelEl) labelEl.textContent = lang.toUpperCase();
        });
        document.querySelectorAll('[data-i18n-th]').forEach(el => {
          const text = lang === 'th'
            ? el.dataset.i18nTh
            : (el.dataset.i18nEn || el.dataset.i18nTh);
          if (text) el.textContent = text;
        });
        document.querySelectorAll('[data-i18n-placeholder-th]').forEach(el => {
          const text = lang === 'th'
            ? el.dataset.i18nPlaceholderTh
            : (el.dataset.i18nPlaceholderEn || el.dataset.i18nPlaceholderTh);
          if (text) el.setAttribute('placeholder', text);
        });
      }

      const storedLang = localStorage.getItem(LANG_KEY) || document.documentElement.getAttribute('lang') || 'th';
      setLanguage(storedLang);

      langButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const current = localStorage.getItem(LANG_KEY) || 'th';
          const next = current === 'th' ? 'en' : 'th';
          setLanguage(next);
        });
      });
    })();

    function openProfileModal() {
      const modal = document.getElementById('profileModal');
      if (modal) modal.classList.remove('hidden');
    }
    function closeProfileModal() {
      const modal = document.getElementById('profileModal');
      if (modal) modal.classList.add('hidden');
    }
    // Auto-open only when specifically requested (session flag)
    document.addEventListener('DOMContentLoaded', () => {
      const shouldOpenProfile = {!! session()->pull('profile_modal', false) ? 'true' : 'false' !!};
      if (shouldOpenProfile) {
        openProfileModal();
      }
    });
  </script>

  @stack('scripts')
</body>
</html>
