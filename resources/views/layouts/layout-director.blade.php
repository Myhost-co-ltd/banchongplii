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

        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) modal.classList.add('hidden');
        }
        document.addEventListener('DOMContentLoaded', () => {
            const shouldOpenProfile = {!! session()->pull('profile_modal', false) ? 'true' : 'false' !!};
            if (shouldOpenProfile) {
                openProfileModal();
            }
        });
    </script>
    @include('layouts.partials.localization')
    @stack('scripts')
</body>
</html>
