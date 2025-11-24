@extends('layouts.layout')

@section('title', 'เลือกหลักสูตร')

@section('content')
<div class="p-8">

    <h2 class="text-2xl font-bold mb-6">เลือกหลักสูตรที่ต้องการดูรายละเอียด</h2>

    <div class="bg-white shadow rounded-2xl p-6 space-y-3">

        @foreach ($courses as $c)

            <a href="{{ route('course.detail', $c['id']) }}"
               class="block px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200">
                <span class="text-lg font-semibold text-blue-700">{{ $c['name'] }}</span>
            </a>

        @endforeach

    </div>

</div>
@endsection
