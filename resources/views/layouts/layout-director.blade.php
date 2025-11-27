<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบโรงเรียน')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen flex overflow-hidden bg-gray-100 font-sans">

    {{-- Sidebar ผู้อำนวยการ --}}
    <button id="sidebarToggleFab" type="button" class="sidebar-fab hidden" aria-pressed="false"
            title="แสดงเมนู" data-i18n-title-th="แสดงเมนู" data-i18n-title-en="Show menu">
        ☰
    </button>
    @include('layouts.sidebars.director')

    <main class="flex-1 flex flex-col p-10 bg-gray-50 overflow-hidden h-screen">
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 flex flex-col flex-1 overflow-hidden">
            @yield('content')
        </div>
    </main>

    <script>
        (function () {
            const sidebar = document.getElementById('appSidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const toggleFab = document.getElementById('sidebarToggleFab');
            const STORAGE_KEY = 'sidebarCollapsed';

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
                applyState(false);
                saveState(false);
            });
        })();
    </script>
    @include('layouts.partials.localization')
</body>
</html>
