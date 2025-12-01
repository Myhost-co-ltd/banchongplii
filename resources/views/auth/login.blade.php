<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | โรงเรียนบ้านช่องพลี</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-[#cfd3db] font-sans">
@php
    // รูปใหม่ของโรงเรียนบ้านช่องพลี
    $logoPng = asset('images/school-logo-bcp.png');   // เปลี่ยนเป็นชื่อไฟล์โลโก้ใหม่
    $logoFallback = asset('images/school-logo-bcp.png'); // ใช้รูปเดียวกันเป็น fallback
    $sessionExpired = session('session_expired') || request()->boolean('expired');
@endphp

    <!-- กล่องฟอร์ม -->
    <div class="bg-white shadow-xl rounded-2xl p-8 w-[90%] max-w-md flex flex-col items-center border border-gray-300">

        <!-- โลโก้ -->
        <div class="flex flex-col items-center mb-6">
    <div class="w-32 h-32 overflow-hidden flex items-center justify-center mb-3">
        <img src="{{ $logoPng }}"
             alt="ตราโรงเรียนบ้านช่องพลี"
             class="w-full h-full object-contain"
             onerror="this.onerror=null; this.src='{{ $logoFallback }}';">
    </div>

    <h1 class="text-2xl font-semibold text-blue-700">โรงเรียนบ้านช่องพลี</h1>
</div>

        <!-- ฟอร์ม -->
        <form method="POST" action="{{ route('login.submit') }}" class="w-full space-y-3">
            @csrf

            @if ($errors->any())
              <div class="bg-red-100 text-red-700 text-sm p-2 rounded border border-red-200">
                <ul class="list-disc ml-5 space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <!-- อีเมล -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                <div class="flex items-center border border-gray-300 rounded">
                    <span class="px-3 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <input type="email" name="email" placeholder="อีเมล"
                        value="{{ old('email') }}"
                        class="w-full border-0 focus:ring-0 text-sm py-2 pr-3"
                        required>
                </div>
            </div>

            <!-- รหัสผ่าน -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                <div class="flex items-center border border-gray-300 rounded">
                    <span class="px-3 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4s-3 1.567-3 3.5S10.343 11 12 11zM5.5 20a6.5 6.5 0 1113 0H5.5z" />
                        </svg>
                    </span>
                    <input type="password" name="password" placeholder="รหัสผ่าน"
                        class="w-full border-0 focus:ring-0 text-sm py-2 pr-3"
                        required>
                </div>
            </div>

            <!-- ปุ่ม -->
            <div class="flex items-center justify-center pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow flex items-center gap-2">
                    <span>➜</span>
                    <span>เข้าสู่ระบบ</span>
                </button>
            </div>
        </form>

    </div>

    @if ($sessionExpired)
        <div id="sessionExpiredModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">เซสชันหมดอายุ</h2>
                <p class="text-sm text-gray-600 mb-4">เซสชันการเข้าสู่ระบบหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่เพื่อดำเนินการต่อ</p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            id="sessionExpiredClose"
                            class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                        ตกลง
                    </button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('sessionExpiredModal');
                document.getElementById('sessionExpiredClose')?.addEventListener('click', () => {
                    modal?.classList.add('hidden');
                });
            });
        </script>
    @endif
</body>
</html>
