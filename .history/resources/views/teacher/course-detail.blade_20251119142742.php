    @extends('layouts.layout')

    @section('title', 'รายละเอียดหลักสูตร | แดชบอร์ดครู')

    @section('content')
    <div class="space-y-8 overflow-y-auto pr-2 pb-6">

        {{-- กล่องหัวข้อด้านบน --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
            <p class="text-sm text-slate-500 uppercase tracking-wide">รายละเอียดหลักสูตร</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">รายละเอียดหลักสูตร</h2>
            <p class="text-gray-600 mt-1">
                ดูรายละเอียดของหลักสูตรที่ครูกำลังสอนและจัดการข้อมูลที่เกี่ยวข้องทั้งหมด
            </p>
        </div>

        {{-- กล่องรายละเอียดหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

            {{-- แถวบน: ข้อความ "ข้อมูลหลักสูตร" + ปุ่มด้านขวา --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">ข้อมูลหลักสูตร</h1>
                </div>

                <div class="flex gap-3">
                    
                    <a href="{{ route('teacher.courses.edit', $course) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl">
                        แก้ไขข้อมูลหลักสูตร
                    </a>
                </div>
            </div>

            {{-- เนื้อหาหลักของข้อมูลหลักสูตร --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                    <p class="font-semibold text-gray-800 text-lg">{{ $course->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse ($course->rooms ?? [] as $room)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">
                                {{ $room }}
                            </span>
                        @empty
                            <span class="text-gray-400 text-sm">ยังไม่มีการระบุห้องเรียน</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500">ภาคเรียน</p>
                    <p class="font-semibold text-gray-800">
                        {{ $course->term ? 'ภาคเรียนที่ '.$course->term : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">ปีการศึกษา</p>
                    <p class="font-semibold text-gray-800">{{ $course->year ?? '-' }}</p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                    <p class="text-gray-700 mt-1 leading-relaxed">
                        {{ $course->description ?? '-' }}
                    </p>
                </div>

            </div>
        </div>

    </div>
    @endsection
