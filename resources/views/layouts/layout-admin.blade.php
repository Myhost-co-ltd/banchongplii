<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'ระบบผู้ดูแล')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    <!-- ===================== -->
    <!--      SIDEBAR FIXED    -->
    <!-- ===================== -->
    <button id="sidebarToggleFab" type="button" class="sidebar-fab hidden" aria-pressed="false"
            title="แสดงเมนู" data-i18n-title-th="แสดงเมนู" data-i18n-title-en="Show menu">
        ☰
    </button>
    <aside id="appSidebar" class="sidebar-panel fixed left-0 top-0 bottom-0 z-50">
        @php
            $sidebarLogoCandidates = ['png', 'jpg', 'jpeg'];
            $sidebarLogoPath = null;
            $sidebarLogoUrl = asset('images/school-logo-bcp.png');
            foreach ($sidebarLogoCandidates as $ext) {
                $candidatePath = public_path("images/school-logo-bcp.$ext");
                if (file_exists($candidatePath)) {
                    $sidebarLogoPath = $candidatePath;
                    $sidebarLogoUrl = asset("images/school-logo-bcp.$ext");
                    break;
                }
            }
            $sidebarLogoVersion = $sidebarLogoPath ? filemtime($sidebarLogoPath) : null;
            $sidebarLogoUrl = $sidebarLogoUrl . ($sidebarLogoVersion ? ('?v=' . $sidebarLogoVersion) : '');
        @endphp

        <div>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center overflow-hidden">
                        <img src="{{ $sidebarLogoUrl }}" alt="โลโก้โรงเรียน"
                             class="w-full h-full object-contain"
                             title="โลโก้โรงเรียน" data-i18n-title-th="โลโก้โรงเรียน" data-i18n-title-en="School logo">
                    </div>
                    <h1 class="text-lg font-bold leading-tight select-none"
                        data-i18n-th="ผู้ดูแลระบบ" data-i18n-en="Administrator">ผู้ดูแลระบบ</h1>
                </div>
                <button id="sidebarToggle" type="button" class="sidebar-toggle-btn" aria-pressed="false"
                        title="ซ่อน/แสดงเมนู" data-i18n-title-th="ซ่อน/แสดงเมนู" data-i18n-title-en="Hide/Show menu">
                    ☰
                </button>
            </div>

            <nav class="space-y-2">

                <!-- Dashboard -->
                <a href="{{ route('dashboard.admin') }}"
                   class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
                   {{ request()->routeIs('dashboard.admin') 
                        ? 'bg-white/15 text-white font-semibold' 
                        : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   data-nav
                   @if(request()->routeIs('dashboard.admin')) data-current="1" @endif
                    data-i18n-th="แดชบอร์ด" data-i18n-en="Dashboard">
                    แดชบอร์ด
                </a>

                <!-- Student -->
                <a href="{{ route('admin.add-student') }}"
                   class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
                   {{ request()->routeIs('admin.add-student') 
                        ? 'bg-white/15 text-white font-semibold' 
                        : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   data-nav
                   @if(request()->routeIs('admin.add-student')) data-current="1" @endif
                    data-i18n-th="จัดการข้อมูลนักเรียน" data-i18n-en="Manage Students">
                    จัดการข้อมูลนักเรียน
                </a>

                <!-- Teacher -->
                <a href="{{ route('admin.add-teacher') }}"
                   class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
                   {{ request()->routeIs('admin.add-teacher') 
                        ? 'bg-white/15 text-white font-semibold' 
                        : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   data-nav
                   @if(request()->routeIs('admin.add-teacher')) data-current="1" @endif
                    data-i18n-th="จัดการข้อมูลครู" data-i18n-en="Manage Teachers">
                    จัดการข้อมูลครู
                </a>

                <!-- Course -->
                <a href="{{ route('admin.courses.index') }}"
                   class="block py-2.5 px-4 rounded-2xl transition-colors duration-200
                   {{ request()->routeIs('admin.courses.*') 
                        ? 'bg-white/15 text-white font-semibold' 
                        : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   data-nav
                   @if(request()->routeIs('admin.courses.*')) data-current="1" @endif
                    data-i18n-th="จัดการหลักสูตร" data-i18n-en="Manage Courses">
                    จัดการหลักสูตร
                </a>

            </nav>
        </div>

        <!-- Language + Logout -->
        <div class="mt-6 space-y-2">
            <button type="button"
                    class="w-full py-2.5 rounded-xl bg-transparent text-white/85 hover:text-white hover:bg-white/10 transition-colors text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/30"
                    data-i18n-th="จัดการโปรไฟล์" data-i18n-en="Manage profile"
                    onclick="openProfileModal()">
                <span data-i18n-th="จัดการโปรไฟล์" data-i18n-en="Manage profile">จัดการโปรไฟล์</span>
            </button>
            <a href="{{ route('admin.login-logo.edit') }}"
               class="w-full block py-2.5 px-4 rounded-xl text-center transition-colors
               {{ request()->routeIs('admin.login-logo.*')
                    ? 'bg-white/15 text-white font-semibold'
                    : 'text-white/85 hover:text-white hover:bg-white/10' }}"
               data-nav
               @if(request()->routeIs('admin.login-logo.*')) data-current="1" @endif
                data-i18n-th="ตั้งค่า/จัดการรูป" data-i18n-en="Login logo settings">
                ตั้งค่า/จัดการรูป
            </a>

            <button type="button" data-lang-toggle class="lang-toggle w-full justify-center"
                    aria-label="เปลี่ยนภาษา" title="เปลี่ยนภาษา"
                    data-i18n-aria-th="เปลี่ยนภาษา" data-i18n-aria-en="Switch language"
                    data-i18n-title-th="เปลี่ยนภาษา" data-i18n-title-en="Switch language">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v18m9-9H3m14.5 0a14.5 14.5 0 00-5.5-11 14.5 14.5 0 00-5.5 11 14.5 14.5 0 005.5 11 14.5 14.5 0 005.5-11z" />
                </svg>
                <span data-lang-label>TH</span>
            </button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-transparent text-rose-200 hover:text-white hover:bg-rose-500/20 transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/60">
                    <span data-i18n-th="ออกจากระบบ" data-i18n-en="Logout">ออกจากระบบ</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ===================== -->
    <!--       CONTENT         -->
    <!-- ===================== -->
    <main id="adminMainContent" class="content-panel with-sidebar min-h-screen p-10 bg-gray-50">

        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 min-h-[calc(100vh-120px)] overflow-hidden">
            @yield('content')
        </div>

    </main>

    {{-- Profile Modal --}}
    <div id="profileModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-40 flex items-center justify-center px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 relative">
            <button type="button" onclick="closeProfileModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                ✕
            </button>

            <h2 class="text-2xl font-semibold text-gray-900 mb-2" data-i18n-th="จัดการโปรไฟล์" data-i18n-en="Manage profile">จัดการโปรไฟล์</h2>
            <p class="text-sm text-gray-500 mb-4" data-i18n-th="ปรับชื่อผู้ใช้ และเปลี่ยนรหัสผ่านได้จากที่นี่" data-i18n-en="Update your display name and change password here">ปรับชื่อผู้ใช้ และเปลี่ยนรหัสผ่านได้จากที่นี่</p>

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
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ชื่อที่แสดง" data-i18n-en="Display name">ชื่อที่แสดง</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="อีเมล" data-i18n-en="Email">อีเมล</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                           placeholder="name@example.com" required
                           data-i18n-placeholder-th="name@example.com" data-i18n-placeholder-en="name@example.com">
                </div>

                <hr class="my-2">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="รหัสผ่านปัจจุบัน" data-i18n-en="Current password">รหัสผ่านปัจจุบัน</label>
                    <input type="password" name="current_password"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                           placeholder="กรอกเมื่อจะเปลี่ยนรหัสผ่าน"
                           data-i18n-placeholder-th="กรอกเมื่อจะเปลี่ยนรหัสผ่าน" data-i18n-placeholder-en="Fill in only if changing password">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="รหัสผ่านใหม่" data-i18n-en="New password">รหัสผ่านใหม่</label>
                        <input type="password" name="password"
                               class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="อย่างน้อย 6 ตัวอักษร"
                               data-i18n-placeholder-th="อย่างน้อย 6 ตัวอักษร" data-i18n-placeholder-en="At least 6 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ยืนยันรหัสผ่านใหม่" data-i18n-en="Confirm new password">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                               placeholder="ใส่ซ้ำให้ตรงกัน"
                               data-i18n-placeholder-th="ใส่ซ้ำให้ตรงกัน" data-i18n-placeholder-en="Repeat to match">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeProfileModal()"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
                        <span data-i18n-th="ยกเลิก" data-i18n-en="Cancel">ยกเลิก</span>
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                        <span data-i18n-th="บันทึกการเปลี่ยนแปลง" data-i18n-en="Save changes">บันทึกการเปลี่ยนแปลง</span>
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
            const body = document.body;
            const STORAGE_KEY = 'sidebarCollapsed';
            const mainContent = document.getElementById('adminMainContent');
            const MOBILE_BREAKPOINT = 1024;
            let currentCollapsed = false;
            const navLinks = sidebar ? sidebar.querySelectorAll('a') : [];

            function isMobile() {
                return window.innerWidth < MOBILE_BREAKPOINT;
            }

            function applyState(collapsed, opts = {}) {
                if (!sidebar) return;
                currentCollapsed = collapsed;
                sidebar.classList.toggle('sidebar-collapsed', collapsed);
                if (toggleBtn) toggleBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                if (mainContent) {
                    mainContent.classList.toggle('no-sidebar', collapsed);
                    mainContent.classList.toggle('with-sidebar', !collapsed);
                }
                if (body) {
                    body.classList.toggle('sidebar-open', !collapsed && isMobile());
                }
                if (toggleFab) {
                    toggleFab.classList.toggle('hidden', !collapsed);
                    toggleFab.style.display = collapsed ? 'flex' : 'none';
                    toggleFab.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                }
                if (!opts.skipSave) {
                    saveState(collapsed);
                }
            }

            function loadState() {
                return localStorage.getItem(STORAGE_KEY) === '1';
            }

            function saveState(collapsed) {
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            }

            const initialCollapsed = isMobile() ? true : loadState();
            applyState(initialCollapsed, { skipSave: true });

            toggleBtn?.addEventListener('click', () => {
                const newState = !currentCollapsed;
                applyState(newState);
            });

            toggleFab?.addEventListener('click', () => {
                applyState(false);
            });

            navLinks.forEach(link => {
                // block reload if already active (match by data-current or same path) to avoid flicker
                link.addEventListener('click', (e) => {
                    const samePath = link.pathname === window.location.pathname;
                    if (link.dataset.current === '1' || samePath) {
                        e.preventDefault();
                        return;
                    }
                    if (isMobile()) {
                        applyState(true);
                    }
                });
            });

            window.addEventListener('resize', () => {
                if (isMobile() && !currentCollapsed) {
                    applyState(true);
                }
                if (!isMobile() && currentCollapsed !== loadState()) {
                    applyState(loadState(), { skipSave: true });
                }
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

        // ป้องกันย้อนกลับจาก cache หลัง logout (Safari/Firefox bfcache)
        window.addEventListener('pageshow', (event) => {
            const navType = performance.getEntriesByType('navigation')[0]?.type;
            if (event.persisted || navType === 'back_forward') {
                window.location.reload();
            }
        });

        // Quick Thai text fix for admin add-teacher page (handles mojibake)
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.location.pathname.includes('/admin/add-teacher')) return;

            const ensureText = (selector, text, attr) => {
                document.querySelectorAll(selector).forEach(el => {
                    if (el.textContent.includes('�') || el.textContent.trim() === '' || el.textContent === text) {
                        el.textContent = text;
                    }
                    if (attr) {
                        Object.entries(attr).forEach(([k, v]) => el.setAttribute(k, v));
                    }
                });
            };

            ensureText("h1[data-i18n-en='Manage Teachers']", 'จัดการข้อมูลครู', {'data-i18n-th': 'จัดการข้อมูลครู'});
            ensureText("button[onclick='openAddTeacher()']", 'เพิ่มครู', {'data-i18n-th': 'เพิ่มครู'});

            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                const ph = 'ค้นหาชื่อ / อีเมล / เบอร์โทร...';
                searchInput.placeholder = ph;
                searchInput.setAttribute('data-i18n-placeholder-th', ph);
            }

            const headers = ['#', 'ชื่อ', 'อีเมล', 'เบอร์โทร', 'บทบาท', 'วิชาเอก', 'จัดการ'];
            const ths = document.querySelectorAll('#teacherTable thead th');
            ths.forEach((th, idx) => {
                if (headers[idx]) {
                    th.textContent = headers[idx];
                    th.setAttribute('data-i18n-th', headers[idx]);
                }
            });

            // Fix action buttons and role text if garbled
            document.querySelectorAll('.teacher-row button[type=\"button\"]').forEach(btn => {
                if (btn.textContent.includes('�')) btn.textContent = 'แก้ไข';
            });
            document.querySelectorAll('.teacher-row form button[type=\"submit\"]').forEach(btn => {
                if (btn.textContent.includes('�')) btn.textContent = 'ลบ';
            });
            document.querySelectorAll('.teacher-row td:nth-child(5)').forEach(td => {
                if (td.textContent.includes('�') || td.textContent.trim() === '') td.textContent = 'ครู';
            });
            document.querySelectorAll('.teacher-row td:nth-child(2) p.text-xs').forEach(p => {
                if (p.textContent.includes('�')) p.textContent = p.textContent.replace(/�/g, '').replace(':', ':') || 'สร้างเมื่อ:';
            });
        });
    </script>

    @include('layouts.partials.localization')
    @stack('scripts')
</body>
</html>
