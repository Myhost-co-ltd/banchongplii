@extends('layouts.layout-admin')

@section('title', 'ตั้งค่าโลโก้หน้าเข้าสู่ระบบ')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-2" data-i18n-th="ตั้งค่าโลโก้หน้าเข้าสู่ระบบ" data-i18n-en="Login logo settings">
    ตั้งค่าโลโก้&หน้าเข้าสู่ระบบ
</h1>
<p class="text-gray-600 mb-6"
   data-i18n-th="อัปโหลดโลโก้ใหม่เพื่อใช้ในหน้าเข้าสู่ระบบและแถบด้านข้างของผู้ดูแล/ครู/ผอ. ชื่อด้านล่างใช้เฉพาะหน้าเข้าสู่ระบบ"
   data-i18n-en="Upload a new logo used on the login page and the admin/teacher/director sidebars. The name below only affects the login page.">
    อัปโหลดโลโก้ใหม่เพื่อใช้ในหน้าเข้าสู่ระบบและแถบด้านข้างของผู้ดูแล/ครู/ผอ. ชื่อด้านล่างใช้เฉพาะหน้าเข้าสู่ระบบ
</p>

@if (session('status'))
    <div class="mb-4 border border-green-200 bg-green-50 text-green-800 rounded-2xl p-3 text-sm">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 border border-red-200 bg-red-50 text-red-700 rounded-2xl p-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-8">
    <form method="POST" action="{{ route('admin.login-logo.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="action" value="logo">

        <div class="flex flex-col md:flex-row gap-6 items-start">
            <div class="w-40 h-40 bg-gray-50 border border-dashed border-gray-300 rounded-2xl flex items-center justify-center p-3">
                <img src="{{ $logoUrl }}" alt="โลโก้โรงเรียน" class="max-h-full max-w-full object-contain">
            </div>
            <div class="flex-1 space-y-2">
                <label class="block text-sm font-medium text-gray-700" data-i18n-th="ไฟล์โลโก้ (PNG/JPG)" data-i18n-en="Logo file (PNG/JPG)">
                    ไฟล์โลโก้ (PNG/JPG)
                </label>
                <input type="file" name="logo" accept="image/png,image/jpeg"
                       class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                <p class="text-xs text-gray-500" data-i18n-th="แนะนำขนาดสี่เหลี่ยมจัตุรัสหรือแนวนอน ความกว้างไม่เกิน 800px"
                   data-i18n-en="Recommended: square or landscape, max width 800px">
                    แนะนำขนาดสี่เหลี่ยมจัตุรัสหรือแนวนอน ความกว้างไม่เกิน 800px
                </p>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                <span data-i18n-th="บันทึกโลโก้" data-i18n-en="Save logo">บันทึกโลโก้</span>
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.login-logo.update') }}" class="space-y-3 border-t border-gray-100 pt-6">
        @csrf
        <input type="hidden" name="action" value="title">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
                   data-i18n-th="ชื่อโรงเรียน/ระบบ (เฉพาะหน้าเข้าสู่ระบบ)"
                   data-i18n-en="School/System name (login page only)">
                ชื่อโรงเรียน/ระบบ (เฉพาะหน้าเข้าสู่ระบบ)
            </label>
            <input type="text" name="login_title" value="{{ old('login_title', $loginTitle ?? '') }}"
                   class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="เช่น โรงเรียนบ้านช่องพลี"
                   data-i18n-placeholder-th="เช่น โรงเรียนบ้านช่องพลี"
                   data-i18n-placeholder-en="e.g. Ban Chong Phli School" required>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                <span data-i18n-th="บันทึกชื่อหน้าเข้าสู่ระบบ" data-i18n-en="Save login name">บันทึกชื่อหน้าเข้าสู่ระบบ</span>
            </button>
        </div>
    </form>
</div>
@endsection
