@extends('layouts.layout')

@section('title', 'แดชบอร์ดผู้ดูแลสูงสุด')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-4">Superadmin Dashboard</h2>
<p class="text-gray-600">คุณคือผู้ดูแลระบบสูงสุด (Superadmin)</p>

<div class="mt-6">
  <a href="/admin/manage-users" class="block p-4 bg-blue-700 text-white rounded-xl">
    จัดการผู้ใช้ทั้งหมด
  </a>
</div>
@endsection
