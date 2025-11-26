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
    <button id="sidebarToggleFab" type="button" class="sidebar-fab hidden" aria-pressed="false" title="แสดงเมนู">
        ☰
    </button>
    <aside id="appSidebar" class="sidebar-panel fixed left-0 top-0 bottom-0 z-50">

        <div>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-lg font-bold leading-tight select-none">ผู้ดูแลระบบ</h1>
                <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" aria-pressed="false" title="ซ่อน/แสดงเมนู">
                    ☰
                </button>
            </div>

            <nav class="space-y-2">

                <!-- Dashboard -->
                <a href="{{ route('dashboard.admin') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('dashboard.admin') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}"
                    data-i18n-th="แดชบอร์ด" data-i18n-en="Dashboard">
                    แดชบอร์ด
                </a>

                <!-- Student -->
                <a href="{{ route('admin.add-student') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.add-student') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}"
                    data-i18n-th="จัดการข้อมูลนักเรียน" data-i18n-en="Manage Students">
                    จัดการข้อมูลนักเรียน
                </a>

                <!-- Teacher -->
                <a href="{{ route('admin.add-teacher') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.add-teacher') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}"
                    data-i18n-th="จัดการข้อมูลครู" data-i18n-en="Manage Teachers">
                    จัดการข้อมูลครู
                </a>

                <!-- Course -->
                <a href="{{ route('admin.courses.index') }}"
                   class="block py-2.5 px-4 rounded-xl transition-all duration-200
                   {{ request()->routeIs('admin.courses.*') 
                        ? 'bg-gray-700 font-semibold shadow-inner' 
                        : 'hover:bg-gray-700' }}"
                    data-i18n-th="จัดการหลักสูตร" data-i18n-en="Manage Courses">
                    จัดการหลักสูตร
                </a>

            </nav>

            <div class="mt-4">
                <button type="button" data-lang-toggle class="lang-toggle w-full justify-center" aria-label="เปลี่ยนภาษา" title="เปลี่ยนภาษา">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v18m9-9H3m14.5 0a14.5 14.5 0 00-5.5-11 14.5 14.5 0 00-5.5 11 14.5 14.5 0 005.5 11 14.5 14.5 0 005.5-11z" />
                    </svg>
                    <span data-lang-label>TH</span>
                </button>
            </div>
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
    <main id="adminMainContent" class="content-panel with-sidebar min-h-screen p-10 bg-gray-50">

        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 min-h-[calc(100vh-120px)] overflow-hidden">
            @yield('content')
        </div>

    </main>

    <script>
        (function () {
            const sidebar = document.getElementById('appSidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const toggleFab = document.getElementById('sidebarToggleFab');
            const mainContent = document.getElementById('adminMainContent');
            const STORAGE_KEY = 'sidebarCollapsed';
            const LANG_KEY = 'appLocale';
            const langButtons = document.querySelectorAll('[data-lang-toggle]');
            const i18nElements = document.querySelectorAll('[data-i18n-th]');
            const i18nPlaceholders = document.querySelectorAll('[data-i18n-placeholder-th]');

            function applyState(collapsed) {
                if (!sidebar) return;
                sidebar.classList.toggle('sidebar-collapsed', collapsed);
                if (toggleBtn) toggleBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                if (mainContent) {
                    mainContent.classList.toggle('no-sidebar', collapsed);
                    mainContent.classList.toggle('with-sidebar', !collapsed);
                }
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
                applyState(false);
                saveState(false);
            });

            function setLanguage(lang) {
                document.documentElement.setAttribute('lang', lang);
                localStorage.setItem(LANG_KEY, lang);
                langButtons.forEach(btn => {
                    btn.setAttribute('aria-label', lang === 'th' ? 'เปลี่ยนเป็นภาษาอังกฤษ' : 'Switch to Thai');
                    const labelEl = btn.querySelector('[data-lang-label]');
                    if (labelEl) labelEl.textContent = lang.toUpperCase();
                });
                i18nElements.forEach(el => {
                    const text = lang === 'th'
                        ? el.dataset.i18nTh
                        : (el.dataset.i18nEn || el.dataset.i18nTh);
                    if (text) el.textContent = text;
                });
                i18nPlaceholders.forEach(el => {
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
    </script>

    @stack('scripts')
</body>
</html>
