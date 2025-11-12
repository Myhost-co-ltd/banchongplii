<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก | โรงเรียนบ้านช่องพลี</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-100 font-sans">
  <div class="bg-white shadow-2xl rounded-2xl p-8 w-[90%] max-w-sm flex flex-col items-center border border-gray-100">

    <!-- หัวข้อ -->
    <h2 class="text-2xl font-bold text-gray-800 mb-1 text-center">สร้างบัญชีผู้ใช้ใหม่ </h2>
    <p class="text-gray-500 text-sm mb-6 text-center">กรุณากรอกข้อมูลเพื่อสมัครใช้งานระบบ</p>

    <!-- ฟอร์ม -->
    <form method="POST" action="{{ route('register.submit') }}" class="w-full space-y-4">
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

      <!-- ชื่อ-นามสกุล -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ-นามสกุล</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="กรอกชื่อ-นามสกุล"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" required>
      </div>

      <!-- อีเมล -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="กรอกอีเมลของคุณ"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" required>
      </div>

      <!-- รหัสผ่าน -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
        <input type="password" name="password" placeholder="ตั้งรหัสผ่าน"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" required>
      </div>

      <!-- ยืนยันรหัสผ่าน -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่าน</label>
        <input type="password" name="password_confirmation" placeholder="กรอกรหัสผ่านอีกครั้ง"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" required>
      </div>

      <!-- ปุ่มสมัครสมาชิก -->
      <button type="submit"
        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg shadow-md transition duration-200">
        สมัครสมาชิก
      </button>

      <!-- เส้นแบ่ง -->
      <div class="flex items-center my-3">
        <hr class="flex-grow border-gray-300">
        <span class="px-3 text-gray-400 text-sm">หรือ</span>
        <hr class="flex-grow border-gray-300">
      </div>

      <!-- สมัครด้วย Gmail -->
      {{-- <button type="button"
        class="w-full flex items-center justify-center border border-gray-300 rounded-lg py-2 hover:bg-gray-100 transition duration-200">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 mr-2" alt="Google">
        <span class="text-sm text-gray-700 font-medium">สมัครด้วยบัญชี Gmail</span>
      </button> --}}
    </form>

    <!-- ลิงก์กลับหน้าเข้าสู่ระบบ -->
    <p class="text-gray-400 text-sm mt-8 text-center">
      มีบัญชีผู้ใช้อยู่แล้ว?
      <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">กลับไปหน้าเข้าสู่ระบบ</a>
    </p>

  </div>
</body>
</html>
