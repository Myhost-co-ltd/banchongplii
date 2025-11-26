@extends('layouts.layout-admin')

@section('title', 'แดชบอร์ดผู้ดูแลระบบ')

@section('content')

<h1 class="text-3xl font-bold text-gray-800 mb-2" data-i18n-th="แดชบอร์ดผู้ดูแลระบบ" data-i18n-en="Admin Dashboard">แดชบอร์ดผู้ดูแลระบบ</h1>
<p class="text-gray-600 mb-6" data-i18n-th="ยินดีต้อนรับ ผู้ดูแลระบบ" data-i18n-en="Welcome, Admin">ยินดีต้อนรับ ผู้ดูแลระบบ</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- จำนวนผู้ใช้งาน -->
    <div class="p-6 bg-blue-100 border border-blue-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนผู้ใช้งานทั้งหมด" data-i18n-en="Total users">จำนวนผู้ใช้งานทั้งหมด</h3>
        <p class="text-4xl font-bold text-blue-700">{{ number_format($userCount ?? 0) }}</p>
    </div>

    <!-- จำนวนครู -->
    <div class="p-6 bg-green-100 border border-green-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนครูทั้งหมด" data-i18n-en="Total teachers">จำนวนครูทั้งหมด</h3>
        <p class="text-4xl font-bold text-green-700">{{ number_format($teacherCount ?? 0) }}</p>
    </div>

    <!--จำนวนห้อง -->
    <div class="p-6 bg-purple-100 border border-purple-200 rounded-2xl shadow-sm">
        <h3 class="text-gray-600 mb-1" data-i18n-th="จำนวนห้องเรียน" data-i18n-en="Total classrooms">จำนวนห้องเรียน</h3>
        <p class="text-4xl font-bold text-purple-700">{{ number_format($classroomCount ?? 0) }}</p>
    </div>

</div>

@endsection
