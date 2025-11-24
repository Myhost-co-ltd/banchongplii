@extends('layouts.layout-admin')

@section('title', 'จัดการหลักสูตร / ชั่วโมงสอน')

@section('content')
<div class="space-y-8">

    <div class="flex flex-col gap-2">
        <p class="text-sm text-slate-500 uppercase tracking-[0.2em]">สำหรับผู้ดูแลระบบ</p>
        <h1 class="text-3xl font-bold text-gray-900">สร้างหลักสูตรและกำหนดชั่วโมงสอน</h1>
        <p class="text-gray-600">แอดมินสร้างวิชาและชั่วโมงคร่าวๆ แล้วครูจะเข้ามารับและเติมรายละเอียดชั้น/ห้องเอง</p>
    </div>

    @if (session('status'))
        <div class="border border-green-200 bg-green-50 text-green-800 rounded-2xl p-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="border border-red-200 bg-red-50 text-red-700 rounded-2xl p-4">
            <p class="font-semibold mb-2">กรุณาตรวจสอบข้อมูลที่กรอก</p>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">เพิ่มหลักสูตรใหม่</h2>
                <p class="text-sm text-gray-500">
                    แอดมินสร้างวิชาและชั่วโมงสอนเบื้องต้น ส่วนครูจะเข้ามารับหลักสูตร เลือกชั้น/ห้อง และจัดการรายละเอียดต่อ
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลือกวิชา (ตัวเลือกด่วน)</label>
                    <select id="presetCourseSelect"
                            class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- เลือกวิชา --</option>
                        @foreach(['คณิตศาสตร์พื้นฐาน','วิทยาศาสตร์','ภาษาไทย','ภาษาอังกฤษ','สังคมศึกษา','สุขศึกษาและพลศึกษา','ดนตรี','ศิลปะ','ประวัติศาสตร์'] as $preset)
                            <option value="{{ $preset }}">{{ $preset }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">เลือกแล้วระบบจะเติมชื่อหลักสูตรให้อัตโนมัติ (ยังแก้ไขได้)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                    <input type="text" name="name"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                           placeholder="เช่น คณิตศาสตร์พื้นฐาน ป.1"
                           value="{{ old('name') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาคเรียน (ถ้ามี)</label>
                    <select name="term" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- เลือกภาคเรียน --</option>
                        <option value="1" @selected(old('term') == 1)>ภาคเรียนที่ 1</option>
                        <option value="2" @selected(old('term') == 2)>ภาคเรียนที่ 2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                    <input type="number" name="year"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                           placeholder="2567"
                           value="{{ old('year') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    รายละเอียดหลักสูตร
                    <span class="text-xs text-gray-400">(ให้ครูใส่/แก้ไขได้เอง)</span>
                </label>
                <textarea name="description" rows="3"
                          class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                          placeholder="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ">{{ old('description') }}</textarea>
            </div>

            <div class="text-right">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                    สร้างหลักสูตร
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">หลักสูตรทั้งหมด</h2>
                <p class="text-sm text-gray-500">ดูภาพรวมหลักสูตร พร้อมจัดสรรชั่วโมงสอนให้ครู</p>
            </div>
            <div class="flex flex-col md:flex-row md:items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>จำนวนหลักสูตร:</span>
                    <span class="font-semibold text-gray-800">{{ $courses->count() }}</span>
                </div>
                <input id="courseSearch"
                       type="text"
                       placeholder="ค้นหาชื่อหลักสูตร / ปีการศึกษา"
                       class="w-full md:w-64 border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        @if($courses->isEmpty())
            <div class="border border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                ยังไม่มีหลักสูตรในระบบ กรุณาเพิ่มหลักสูตรใหม่ก่อน
            </div>
        @else
            <div class="space-y-6" id="courseList">
                @foreach ($courses as $course)
                    @php
                        $groupedHours = collect($course->teaching_hours ?? [])->groupBy('term');
                        $defaultTerm = $course->term ?? '1';
                    @endphp
                    <div class="border border-gray-100 rounded-2xl p-6 shadow-sm bg-gray-50/70 course-card"
                         data-name="{{ strtolower($course->name) }}"
                         data-year="{{ $course->year ?? '' }}">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">หลักสูตร</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $course->name }}</h3>
                                <p class="text-sm text-gray-600">
                                    ครูผู้รับผิดชอบ: <span class="font-semibold text-gray-800">{{ $course->teacher->name ?? 'ไม่พบข้อมูลครู' }}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm text-gray-700 items-center">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">{{ $course->grade }}</span>
                                @foreach(($course->rooms ?? []) as $room)
                                    <span class="px-3 py-1 rounded-full bg-white border text-gray-700">{{ $room }}</span>
                                @endforeach
                                <span class="px-3 py-1 rounded-full bg-white border text-gray-600">
                                    ปีการศึกษา {{ $course->year ?? '-' }}
                                </span>
                                <div class="flex items-center gap-3 text-sm ml-2">
                                    <button type="button"
                                            class="text-blue-600 hover:underline"
                                            onclick="toggleCourseEdit('course-edit-{{ $course->id }}')">
                                        แก้ไข
                                    </button>
                                    <form method="POST"
                                          action="{{ route('admin.courses.destroy', $course) }}"
                                          onsubmit="return confirm('ยืนยันการลบหลักสูตรนี้หรือไม่?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">ลบ</button>
                                    </form>
                                    <button type="button"
                                            class="text-gray-600 hover:underline"
                                            onclick="toggleCourseBody('course-body-{{ $course->id }}')">
                                        แสดงรายละเอียด
                                    </button>
                                </div>
                            </div>
                        </div>

                        <form id="course-edit-{{ $course->id }}" method="POST"
                              action="{{ route('admin.courses.update', $course) }}"
                              class="hidden bg-white border border-gray-100 rounded-xl p-4 mb-4 text-sm">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร</label>
                                    <input type="text" name="name"
                                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                           value="{{ $course->name }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ภาคเรียน (ถ้ามี)</label>
                                    <select name="term" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- เลือกภาคเรียน --</option>
                                        <option value="1" @selected(($course->term ?? null) == '1')>ภาคเรียนที่ 1</option>
                                        <option value="2" @selected(($course->term ?? null) == '2')>ภาคเรียนที่ 2</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ปีการศึกษา</label>
                                    <input type="number" name="year"
                                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                           value="{{ $course->year }}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดหลักสูตร</label>
                                <textarea name="description" rows="2"
                                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                          placeholder="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ">{{ $course->description }}</textarea>
                            </div>
                            <div class="text-right mt-3">
                                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    บันทึกการแก้ไข
                                </button>
                            </div>
                        </form>

                        <div id="course-body-{{ $course->id }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6 hidden">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-900">ชั่วโมงสอนที่ตั้งไว้</p>
                                    <span class="text-xs text-gray-500">จัดการภาคเรียนแยกกัน</span>
                                </div>

                                @if($groupedHours->isEmpty())
                                    <p class="text-sm text-gray-500 bg-white border border-dashed border-gray-200 rounded-xl p-4">
                                        ยังไม่มีข้อมูลชั่วโมงสอนสำหรับหลักสูตรนี้
                                    </p>
                                @else
                                    <div class="space-y-3">
                                        @foreach($groupedHours as $term => $hours)
                                            <div class="bg-white border border-gray-100 rounded-xl p-3">
                                                <p class="text-sm font-semibold text-gray-800 mb-2">
                                                    ภาคเรียนที่ {{ $term ?? '-' }}
                                                </p>
                                                <div class="space-y-2">
                                                    @foreach($hours as $hour)
                                                        @php($hourId = $hour['id'] ?? null)
                                                        <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div>
                                                                    <p class="font-medium text-gray-900">{{ $hour['category'] ?? '-' }}</p>
                                                                    <p class="text-sm text-gray-600">
                                                                        {{ $hour['hours'] ?? 0 }} ชั่วโมง
                                                                    </p>
                                                                    @if(!empty($hour['note']))
                                                                        <p class="text-xs text-gray-500 mt-1">{{ $hour['note'] }}</p>
                                                                    @endif
                                                                </div>
                                                                @if($hourId)
                                                                    <div class="flex items-center gap-3 text-sm">
                                                                        <button type="button"
                                                                                class="text-blue-600 hover:underline"
                                                                                onclick="toggleHourForm('hour-edit-{{ $course->id }}-{{ $hourId }}')">
                                                                            แก้ไข
                                                                        </button>
                                                                        <form method="POST"
                                                                              action="{{ route('admin.courses.hours.destroy', ['course' => $course, 'hour' => $hourId]) }}"
                                                                              onsubmit="return confirm('ยืนยันลบชั่วโมงนี้หรือไม่?')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button class="text-red-600 hover:underline">ลบ</button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            @if($hourId)
                                                                <form id="hour-edit-{{ $course->id }}-{{ $hourId }}" method="POST"
                                                                      action="{{ route('admin.courses.hours.update', ['course' => $course, 'hour' => $hourId]) }}"
                                                                      class="hidden mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <select name="term" class="border rounded-lg px-2 py-2" required>
                                                                        <option value="1" @selected(($hour['term'] ?? null) == '1')>ภาคเรียนที่ 1</option>
                                                                        <option value="2" @selected(($hour['term'] ?? null) == '2')>ภาคเรียนที่ 2</option>
                                                                    </select>
                                        <select name="category" class="border rounded-lg px-2 py-2" required>
                                            <option value="">เลือกประเภทชั่วโมง</option>
                                            <option value="ทฤษฎี" @selected(($hour['category'] ?? '') === 'ทฤษฎี')>ทฤษฎี</option>
                                            <option value="ปฏิบัติ" @selected(($hour['category'] ?? '') === 'ปฏิบัติ')>ปฏิบัติ</option>
                                        </select>
                                                                    <input type="number" step="0.1" name="hours"
                                                                           class="border rounded-lg px-2 py-2"
                                                                           value="{{ $hour['hours'] ?? '' }}" required>
                                        <input type="hidden" name="unit" value="ชั่วโมง/สัปดาห์">
                                                                    <textarea name="note" rows="2"
                                                                              class="md:col-span-4 border rounded-lg px-2 py-2"
                                                                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)">{{ $hour['note'] ?? '' }}</textarea>
                                                                    <div class="md:col-span-4 text-right">
                                                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                                                                            บันทึกการแก้ไข
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="bg-white border border-gray-100 rounded-xl p-4">
                                <h4 class="font-semibold text-gray-900 mb-3">เพิ่มชั่วโมงสอนให้หลักสูตรนี้</h4>
                                <form method="POST"
                                      action="{{ route('admin.courses.hours.store', $course) }}"
                                      class="space-y-3 text-sm">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <select name="term" class="border rounded-lg px-2 py-2" required>
                                            <option value="1" @selected($defaultTerm == '1')>ภาคเรียนที่ 1</option>
                                            <option value="2" @selected($defaultTerm == '2')>ภาคเรียนที่ 2</option>
                                        </select>
                                        <select name="category" class="border rounded-lg px-2 py-2" required>
                                            <option value="">เลือกประเภทชั่วโมง</option>
                                            <option value="ทฤษฎี">ทฤษฎี</option>
                                            <option value="ปฏิบัติ">ปฏิบัติ</option>
                                        </select>
                                        <input type="number" step="0.1" name="hours"
                                               class="border rounded-lg px-2 py-2"
                                               placeholder="จำนวนชั่วโมง" required>
                                    </div>
                                    <input type="hidden" name="unit" value="ชั่วโมง/สัปดาห์">
                                    <textarea name="note" rows="2"
                                              class="w-full border rounded-lg px-2 py-2"
                                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                                    <div class="text-right">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                                            บันทึกชั่วโมงสอน
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('courseSearch');
    const courseCards = document.querySelectorAll('.course-card');
    const presetCourseSelect = document.getElementById('presetCourseSelect');
    const courseNameInput = document.querySelector('input[name="name"]');
    const editCourses = [];

    function filterCourses() {
        const term = (searchInput.value || '').toLowerCase().trim();
        courseCards.forEach(card => {
            const name = card.dataset.name || '';
            const year = (card.dataset.year || '').toLowerCase();
            const match = name.includes(term) || year.includes(term);
            card.classList.toggle('hidden', !match);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCourses);
    }

    if (presetCourseSelect && courseNameInput) {
        presetCourseSelect.addEventListener('change', (event) => {
            const value = event.target.value;
            if (value) {
                courseNameInput.value = value;
            }
        });
    }

});

function toggleHourForm(id) {
    const block = document.getElementById(id);
    if (block) {
        block.classList.toggle('hidden');
    }
}

function toggleCourseEdit(id) {
    const block = document.getElementById(id);
    if (block) {
        block.classList.toggle('hidden');
    }
}

function toggleCourseBody(id) {
    const block = document.getElementById(id);
    if (block) {
        block.classList.toggle('hidden');
    }
}
</script>
@endpush
