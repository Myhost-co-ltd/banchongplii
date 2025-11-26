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
    <button id="sidebarToggleFab" type="button" class="sidebar-fab hidden" aria-pressed="false" title="แสดงเมนู">
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
    </script>
</body>
</html>
