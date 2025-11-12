<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | โรงเรียนบ้านช่องพลี</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-100 font-sans">

    <!-- กล่องฟอร์ม -->
    <div class="bg-white shadow-2xl rounded-2xl p-8 w-[90%] max-w-sm flex flex-col items-center border border-gray-100">

        <!-- โลโก้ -->
        <div class="flex flex-col items-center mb-6">
            <h1 class="text-lg font-semibold text-red-500 mt-2">โรงเรียนบ้านช่องพลี</h1>
        </div>

        <!-- หัวข้อ -->
        <h2 class="text-2xl font-bold text-gray-800 mb-1 text-center">ยินดีต้อนรับกลับ!</h2>
        <p class="text-gray-500 text-sm mb-6 text-center">กรุณาเข้าสู่ระบบเพื่อใช้งานระบบ</p>

        <!-- ฟอร์ม -->
        <form method="POST" action="{{ route('login.submit') }}" class="w-full space-y-4">
            @csrf

            <!-- แสดง error ถ้ามี -->
            @if ($errors->any())
              <div class="bg-red-100 text-red-700 text-sm p-2 rounded">
                <ul class="list-disc ml-5">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <!-- อีเมล -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                <input type="email" name="email" placeholder="กรอกอีเมล"
                    value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- รหัสผ่าน -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" placeholder="กรอกรหัสผ่าน"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- ปุ่มเข้าสู่ระบบ -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow-md transition duration-200">
                เข้าสู่ระบบ
            </button>
        </form>

        <!-- ลิงก์สมัครสมาชิก -->
        <p class="text-gray-400 text-sm mt-6 text-center">
            ยังไม่มีบัญชีผู้ใช้ใช่ไหม?
            <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">
                ลงทะเบียนที่นี่
            </a>
        </p>

    </div>
</body>
</html>
