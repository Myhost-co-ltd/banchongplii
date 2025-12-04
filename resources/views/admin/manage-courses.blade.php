@extends('layouts.layout-admin')

@section('title', 'จัดการหลักสูตร / ชั่วโมงสอน')

@section('content')
<div class="space-y-8">

    <div class="flex flex-col gap-2">
        <p class="text-sm text-slate-500 uppercase tracking-[0.2em]" data-i18n-th="สำหรับผู้ดูแลระบบ" data-i18n-en="For administrators">สำหรับผู้ดูแลระบบ</p>
        <h1 class="text-3xl font-bold text-gray-900" data-i18n-th="สร้างหลักสูตรและกำหนดชั่วโมงสอน" data-i18n-en="Create courses and set teaching hours">สร้างหลักสูตรและกำหนดชั่วโมงสอน</h1>
        <p class="text-gray-600" data-i18n-th="แอดมินสร้างวิชาและชั่วโมงคร่าวๆ แล้วครูจะเข้ามารับและเติมรายละเอียดชั้น/ห้องเอง" data-i18n-en="Admins create subjects and rough hours, then teachers claim them and fill class/room details">แอดมินสร้างวิชาและชั่วโมงคร่าวๆ แล้วครูจะเข้ามารับและเติมรายละเอียดชั้น/ห้องเอง</p>
    </div>

    @if (session('status'))
        <div class="border border-green-200 bg-green-50 text-green-800 rounded-2xl p-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="border border-red-200 bg-red-50 text-red-700 rounded-2xl p-4">
            <p class="font-semibold mb-2" data-i18n-th="กรุณาตรวจสอบข้อมูลที่กรอก" data-i18n-en="Please review the submitted information">กรุณาตรวจสอบข้อมูลที่กรอก</p>
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
                <h2 class="text-xl font-semibold text-gray-900" data-i18n-th="เพิ่มหลักสูตรใหม่" data-i18n-en="Add new course">เพิ่มหลักสูตรใหม่</h2>
                <p class="text-sm text-gray-500"
                   data-i18n-th="แอดมินสร้างวิชาและชั่วโมงสอนเบื้องต้น ส่วนครูจะเข้ามารับหลักสูตร เลือกชั้น/ห้อง และจัดการรายละเอียดต่อ"
                   data-i18n-en="Admins set up the subject and base hours; teachers will claim it, pick grade/room, and refine details.">
                    สร้างวิชาและชั่วโมงสอนเบื้องต้น ส่วนครูจะเข้ามารับหลักสูตร เลือกชั้น/ห้อง และจัดการรายละเอียดต่อ
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="เลือกวิชา (ตัวเลือกด่วน)" data-i18n-en="Pick subject (quick option)">เลือกวิชา (ตัวเลือกด่วน)</label>
                    <select id="presetCourseSelect"
                            class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value=""
                                data-i18n-th="-- เลือกวิชา --"
                                data-i18n-en="-- Select subject --">-- เลือกวิชา --</option>
                        @php
                            $majorOptions = [
                                'คณิตศาสตร์',
                                'วิทยาศาสตร์',
                                'ภาษาไทย',
                                'ภาษาอังกฤษ',
                                'สังคมศึกษา',
                                'สุขศึกษา/พลศึกษา',
                                'ศิลปะ',
                                'ดนตรี',
                                'การงานอาชีพ',
                                'คอมพิวเตอร์',
                            ];
                            $existingCourseNames = ($courses ?? collect())->pluck('name')->filter()->unique()->sort()->values();
                            $majorOptions = array_values(array_unique(array_merge($majorOptions, $existingCourseNames->toArray())));
                            $majorTranslations = [
                                'คณิตศาสตร์' => 'Mathematics',
                                'วิทยาศาสตร์' => 'Science',
                                'ภาษาไทย' => 'Thai language',
                                'ภาษาอังกฤษ' => 'English language',
                                'สังคมศึกษา' => 'Social studies',
                                'สุขศึกษา/พลศึกษา' => 'Health/Physical education',
                                'ศิลปะ' => 'Art',
                                'ดนตรี' => 'Music',
                                'การงานอาชีพ' => 'Career and technology',
                                'คอมพิวเตอร์' => 'Computer',
                            ];
                        @endphp
                        @foreach($majorOptions as $preset)
                            @php $presetEn = $majorTranslations[$preset] ?? $preset; @endphp
                            <option value="{{ $preset }}"
                                    data-i18n-th="{{ $preset }}"
                                    data-i18n-en="{{ $presetEn }}">{{ $preset }}</option>
                        @endforeach
                        <option value="__custom__" data-i18n-th="+ เพิ่มวิชาใหม่..." data-i18n-en="+ Add new subject...">+ เพิ่มวิชาใหม่...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1"
                       data-i18n-th="เลือกแล้วระบบจะเติมชื่อหลักสูตรให้อัตโนมัติ (ยังแก้ไขได้)"
                       data-i18n-en="Selecting will auto-fill the course name (you can still edit)">
                        เลือกแล้วระบบจะเติมชื่อหลักสูตรให้อัตโนมัติ (ยังแก้ไขได้)
                        </p>
                    <div id="customSubjectWrapper" class="mt-3 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="เพิ่มวิชาใหม่" data-i18n-en="Add new subject">เพิ่มวิชาใหม่</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   id="customSubjectInput"
                                   class="flex-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                   placeholder="กรอกชื่อวิชาใหม่"
                                   data-i18n-placeholder-th="กรอกชื่อวิชาใหม่"
                                   data-i18n-placeholder-en="Enter new subject name">
                            <button type="button"
                                    id="addCustomSubjectBtn"
                                    class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                                <span data-i18n-th="เพิ่มในรายการ" data-i18n-en="Add to list">เพิ่มในรายการ</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"
                           data-i18n-th="กดเพิ่มแล้วระบบจะเลือกวิชาให้อัตโนมัติ"
                           data-i18n-en="Click add to select it automatically">
                            กดเพิ่มแล้วระบบจะเลือกวิชาให้อัตโนมัติ
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ชื่อหลักสูตร" data-i18n-en="Course name">ชื่อหลักสูตร</label>
                    <input type="text" name="name"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                           {{-- placeholder="เช่น คณิตศาสตร์พื้นฐาน ป.1"
                           data-i18n-placeholder-th="เช่น คณิตศาสตร์พื้นฐาน ป.1"
                           data-i18n-placeholder-en="e.g. Basic Mathematics G.1" --}}
                           value="{{ old('name') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ภาคเรียน (ถ้ามี)" data-i18n-en="Term (if any)">ภาคเรียน (ถ้ามี)</label>
                    <select name="term" class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="" data-i18n-th="-- เลือกภาคเรียน --" data-i18n-en="-- Select term --">-- เลือกภาคเรียน --</option>
                        <option value="1" @selected(old('term') == 1) data-i18n-th="ภาคเรียนที่ 1" data-i18n-en="Term 1">ภาคเรียนที่ 1</option>
                        <option value="2" @selected(old('term') == 2) data-i18n-th="ภาคเรียนที่ 2" data-i18n-en="Term 2">ภาคเรียนที่ 2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ปีการศึกษา" data-i18n-en="Academic year">ปีการศึกษา</label>
                    <input type="number" name="year"
                           class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                           {{-- data-i18n-placeholder-th="2567"
                           data-i18n-placeholder-en="2024" --}}
                           value="{{ old('year') }}">
                </div>
            </div>

            {{-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <span data-i18n-th="รายละเอียดหลักสูตร" data-i18n-en="Course details">รายละเอียดหลักสูตร</span>
                    <span class="text-xs text-gray-400" data-i18n-th="(ให้ครูใส่/แก้ไขได้เอง)" data-i18n-en="(Teachers can fill/edit on their own)">(ให้ครูใส่/แก้ไขได้เอง)</span>
                </label>
                <textarea name="description" rows="3"
                          class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500"
                          placeholder="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ"
                          data-i18n-placeholder-th="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ"
                          data-i18n-placeholder-en="Course objectives / Summary">{{ old('description') }}</textarea>
            </div> --}}

            <div class="text-right">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                    <span data-i18n-th="สร้างหลักสูตร" data-i18n-en="Create course">สร้างหลักสูตร</span>
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900" data-i18n-th="หลักสูตรทั้งหมด" data-i18n-en="All courses">หลักสูตรทั้งหมด</h2>
                <p class="text-sm text-gray-500" data-i18n-th="ดูภาพรวมหลักสูตร พร้อมจัดสรรชั่วโมงสอนให้ครู" data-i18n-en="View all courses and allocate teaching hours to teachers">ดูภาพรวมหลักสูตร พร้อมจัดสรรชั่วโมงสอนให้ครู</p>
            </div>
            <div class="flex flex-col md:flex-row md:items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span data-i18n-th="จำนวนหลักสูตร:" data-i18n-en="Courses:">จำนวนหลักสูตร:</span>
                    <span class="font-semibold text-gray-800">{{ $courses->count() }}</span>
                </div>
                @php
                    $courseNames = $courses->pluck('name')->filter()->unique()->sort();
                @endphp
                <select id="subjectFilter"
                        class="w-full md:w-48 border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- เลือกวิชา --</option>
                    @foreach($courseNames as $courseName)
                        <option value="{{ strtolower($courseName) }}">{{ $courseName }}</option>
                    @endforeach
                </select>
                <input id="courseSearch"
                       type="text"
                       placeholder="ค้นหาชื่อหลักสูตร / ปีการศึกษา"
                       data-i18n-placeholder-th="ค้นหาชื่อหลักสูตร / ปีการศึกษา"
                       data-i18n-placeholder-en="Search course name / academic year"
                       class="w-full md:w-64 border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        @if($courses->isEmpty())
            <div class="border border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                <span data-i18n-th="ยังไม่มีหลักสูตรในระบบ กรุณาเพิ่มหลักสูตรใหม่ก่อน" data-i18n-en="No courses in the system yet. Please add a new course first.">ยังไม่มีหลักสูตรในระบบ กรุณาเพิ่มหลักสูตรใหม่ก่อน</span>
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500" data-i18n-th="หลักสูตร" data-i18n-en="Course">หลักสูตร</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $course->name }}</h3>
                                <p class="text-sm text-gray-600">
                                    <span data-i18n-th="ครูผู้รับผิดชอบ:" data-i18n-en="Responsible teacher:">ครูผู้รับผิดชอบ:</span>
                                    @if($course->teacher?->name)
                                        <span class="font-semibold text-gray-800">{{ $course->teacher->name }}</span>
                                    @else
                                        <span class="font-semibold text-gray-800" data-i18n-th="ไม่พบข้อมูลครู" data-i18n-en="Teacher not found">ไม่พบข้อมูลครู</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm text-gray-700 items-center">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">{{ $course->grade }}</span>
                                @foreach(($course->rooms ?? []) as $room)
                                    <span class="px-3 py-1 rounded-full bg-white border text-gray-700">{{ $room }}</span>
                                @endforeach
                                <span class="px-3 py-1 rounded-full bg-white border text-gray-600">
                                    <span data-i18n-th="ปีการศึกษา" data-i18n-en="Academic year">ปีการศึกษา</span> {{ $course->year ?? '-' }}
                                </span>
                                <div class="flex flex-col items-start gap-2 text-sm ml-2">
                                    <button type="button"
                                            class="text-gray-600 hover:underline"
                                            onclick="toggleCourseBody('course-body-{{ $course->id }}')">
                                        <span data-i18n-th="แสดงรายละเอียด" data-i18n-en="Show details">แสดงรายละเอียด</span>
                                    </button>
                                    <button type="button"
                                            class="text-blue-600 hover:underline"
                                            onclick="toggleCourseEdit('course-edit-{{ $course->id }}')">
                                        <span data-i18n-th="แก้ไข" data-i18n-en="Edit">แก้ไข</span>
                                    </button>
                                    <form method="POST"
                                          action="{{ route('admin.courses.destroy', $course) }}"
                                          onsubmit="return confirmWithLocale('ยืนยันการลบหลักสูตรนี้หรือไม่?', 'Delete this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit" data-i18n-th="ลบ" data-i18n-en="Delete">ลบ</button>
                                    </form>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ชื่อหลักสูตร" data-i18n-en="Course name">ชื่อหลักสูตร</label>
                                    <input type="text" name="name"
                                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                           value="{{ $course->name }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ภาคเรียน (ถ้ามี)" data-i18n-en="Term (if any)">ภาคเรียน (ถ้ามี)</label>
                                    <select name="term" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                                        <option value="" data-i18n-th="-- เลือกภาคเรียน --" data-i18n-en="-- Select term --">-- เลือกภาคเรียน --</option>
                                        <option value="1" @selected(($course->term ?? null) == '1') data-i18n-th="ภาคเรียนที่ 1" data-i18n-en="Term 1">ภาคเรียนที่ 1</option>
                                        <option value="2" @selected(($course->term ?? null) == '2') data-i18n-th="ภาคเรียนที่ 2" data-i18n-en="Term 2">ภาคเรียนที่ 2</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="ปีการศึกษา" data-i18n-en="Academic year">ปีการศึกษา</label>
                                    <input type="number" name="year"
                                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                           value="{{ $course->year }}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n-th="รายละเอียดหลักสูตร" data-i18n-en="Course details">รายละเอียดหลักสูตร</label>
                                <textarea name="description" rows="2"
                                          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                          placeholder="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ"
                                          data-i18n-placeholder-th="จุดประสงค์รายวิชา / เนื้อหาโดยย่อ"
                                          data-i18n-placeholder-en="Course objectives / Summary">{{ $course->description }}</textarea>
                            </div>
                            <div class="text-right mt-3">
                                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <span data-i18n-th="บันทึกการแก้ไข" data-i18n-en="Save changes">บันทึกการแก้ไข</span>
                                </button>
                            </div>
                        </form>

                        <div id="course-body-{{ $course->id }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6 hidden">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-900" data-i18n-th="ชั่วโมงสอนที่ตั้งไว้" data-i18n-en="Scheduled teaching hours">ชั่วโมงสอนที่ตั้งไว้</p>
                                    <span class="text-xs text-gray-500" data-i18n-th="จัดการภาคเรียนแยกกัน" data-i18n-en="Manage separately by term">จัดการภาคเรียนแยกกัน</span>
                                </div>

                                @if($groupedHours->isEmpty())
                                    <p class="text-sm text-gray-500 bg-white border border-dashed border-gray-200 rounded-xl p-4">
                                        <span data-i18n-th="ยังไม่มีข้อมูลชั่วโมงสอนสำหรับหลักสูตรนี้" data-i18n-en="No teaching hours for this course yet">ยังไม่มีข้อมูลชั่วโมงสอนสำหรับหลักสูตรนี้</span>
                                    </p>
                                @else
                                    <div class="space-y-3">
                                        @foreach($groupedHours as $term => $hours)
                                            <div class="bg-white border border-gray-100 rounded-xl p-3">
                                                <p class="text-sm font-semibold text-gray-800 mb-2">
                                                    <span data-i18n-th="ภาคเรียนที่" data-i18n-en="Term">ภาคเรียนที่</span> {{ $term ?? '-' }}
                                                </p>
                                                <div class="space-y-2">
                                                    @foreach($hours as $hour)
                                                        @php $hourId = $hour['id'] ?? null; @endphp
                                                        @php
                                                            $categoryLabel = $hour['category'] ?? '-';
                                                            $categoryEnMap = ['ทฤษฎี' => 'Theory', 'ปฏิบัติ' => 'Practical'];
                                                            $categoryEn = $categoryEnMap[$categoryLabel] ?? $categoryLabel;
                                                        @endphp
                                                        <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div>
                                                                    <p class="font-medium text-gray-900"
                                                                       data-i18n-th="{{ $categoryLabel }}"
                                                                       data-i18n-en="{{ $categoryEn }}">{{ $categoryLabel }}</p>
                                                                    <p class="text-sm text-gray-600">
                                                                        {{ $hour['hours'] ?? 0 }} <span data-i18n-th="ชั่วโมง" data-i18n-en="hours">ชั่วโมง</span>
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
                                                                            <span data-i18n-th="แก้ไข" data-i18n-en="Edit">แก้ไข</span>
                                                                        </button>
                                                                        <form method="POST"
                                                                              action="{{ route('admin.courses.hours.destroy', ['course' => $course, 'hour' => $hourId]) }}"
                                                                              onsubmit="return confirmWithLocale('ยืนยันลบชั่วโมงนี้หรือไม่?', 'Delete this hour?')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button class="text-red-600 hover:underline" data-i18n-th="ลบ" data-i18n-en="Delete">ลบ</button>
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
                                                                        <option value="1" @selected(($hour['term'] ?? null) == '1') data-i18n-th="ภาคเรียนที่ 1" data-i18n-en="Term 1">ภาคเรียนที่ 1</option>
                                                                        <option value="2" @selected(($hour['term'] ?? null) == '2') data-i18n-th="ภาคเรียนที่ 2" data-i18n-en="Term 2">ภาคเรียนที่ 2</option>
                                                                    </select>
                                        <select name="category" class="border rounded-lg px-2 py-2" required>
                                            <option value="" data-i18n-th="เลือกประเภทชั่วโมง" data-i18n-en="Select hour type">เลือกประเภทชั่วโมง</option>
                                            <option value="ทฤษฎี" @selected(($hour['category'] ?? '') === 'ทฤษฎี') data-i18n-th="ทฤษฎี" data-i18n-en="Theory">ทฤษฎี</option>
                                            <option value="ปฏิบัติ" @selected(($hour['category'] ?? '') === 'ปฏิบัติ') data-i18n-th="ปฏิบัติ" data-i18n-en="Practical">ปฏิบัติ</option>
                                        </select>
                                                                    <input type="number" step="0.1" name="hours"
                                                                           class="border rounded-lg px-2 py-2"
                                                                           value="{{ $hour['hours'] ?? '' }}" required>
                                        <input type="hidden" name="unit" value="ชั่วโมง/สัปดาห์">
                                                                    <textarea name="note" rows="2"
                                                                              class="md:col-span-4 border rounded-lg px-2 py-2"
                                                                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"
                                                                              data-i18n-placeholder-th="หมายเหตุเพิ่มเติม (ถ้ามี)"
                                                                              data-i18n-placeholder-en="Additional notes (if any)">{{ $hour['note'] ?? '' }}</textarea>
                                                                    <div class="md:col-span-4 text-right">
                                                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                                                                            <span data-i18n-th="บันทึกการแก้ไข" data-i18n-en="Save changes">บันทึกการแก้ไข</span>
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
                                <h4 class="font-semibold text-gray-900 mb-3" data-i18n-th="เพิ่มชั่วโมงสอนให้หลักสูตรนี้" data-i18n-en="Add teaching hours to this course">เพิ่มชั่วโมงสอนให้หลักสูตรนี้</h4>
                                <form method="POST"
                                      action="{{ route('admin.courses.hours.store', $course) }}"
                                      class="space-y-3 text-sm">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <select name="term" class="border rounded-lg px-2 py-2" required>
                                            <option value="1" @selected($defaultTerm == '1') data-i18n-th="ภาคเรียนที่ 1" data-i18n-en="Term 1">ภาคเรียนที่ 1</option>
                                            <option value="2" @selected($defaultTerm == '2') data-i18n-th="ภาคเรียนที่ 2" data-i18n-en="Term 2">ภาคเรียนที่ 2</option>
                                        </select>
                                        <select name="category" class="border rounded-lg px-2 py-2" required>
                                            <option value="" data-i18n-th="เลือกประเภทชั่วโมง" data-i18n-en="Select hour type">เลือกประเภทชั่วโมง</option>
                                            <option value="ทฤษฎี" data-i18n-th="ทฤษฎี" data-i18n-en="Theory">ทฤษฎี</option>
                                            <option value="ปฏิบัติ" data-i18n-th="ปฏิบัติ" data-i18n-en="Practical">ปฏิบัติ</option>
                                        </select>
                                        <input type="number" step="0.1" name="hours"
                                               class="border rounded-lg px-2 py-2"
                                               placeholder="จำนวนชั่วโมง"
                                               data-i18n-placeholder-th="จำนวนชั่วโมง"
                                               data-i18n-placeholder-en="Number of hours" required>
                                    </div>
                                    <input type="hidden" name="unit" value="ชั่วโมง/สัปดาห์">
                                    <textarea name="note" rows="2"
                                              class="w-full border rounded-lg px-2 py-2"
                                              placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"
                                              data-i18n-placeholder-th="หมายเหตุเพิ่มเติม (ถ้ามี)"
                                              data-i18n-placeholder-en="Additional notes (if any)"></textarea>
                                    <div class="text-right">
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                                            <span data-i18n-th="บันทึกชั่วโมงสอน" data-i18n-en="Save teaching hours">บันทึกชั่วโมงสอน</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- เพดานคะแนนเก็บ --}}
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
const LANG_KEY = 'appLocale';

function getAppLocale() {
    return localStorage.getItem(LANG_KEY) || document.documentElement.getAttribute('lang') || 'th';
}

function confirmWithLocale(thText, enText) {
    const lang = getAppLocale();
    return confirm(lang === 'en' ? enText : thText);
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('courseSearch');
    const courseCards = document.querySelectorAll('.course-card');
    const presetCourseSelect = document.getElementById('presetCourseSelect');
    const courseNameInput = document.querySelector('input[name="name"]');
    const subjectFilter = document.getElementById('subjectFilter');
    const customSubjectWrapper = document.getElementById('customSubjectWrapper');
    const customSubjectInput = document.getElementById('customSubjectInput');
    const addCustomSubjectBtn = document.getElementById('addCustomSubjectBtn');
    const editCourses = [];

    function filterCourses() {
        const term = (searchInput.value || '').toLowerCase().trim();
        const subject = (subjectFilter?.value || '').toLowerCase().trim();
        courseCards.forEach(card => {
            const name = card.dataset.name || '';
            const year = (card.dataset.year || '').toLowerCase();
            const matchText = name.includes(term) || year.includes(term);
            const matchSubject = !subject || name === subject;
            const match = matchText && matchSubject;
            card.classList.toggle('hidden', !match);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCourses);
    }
    subjectFilter?.addEventListener('change', filterCourses);

    if (presetCourseSelect && courseNameInput) {
        presetCourseSelect.addEventListener('change', (event) => {
            const value = event.target.value;
            const isCustom = value === '__custom__';
            if (isCustom) {
                courseNameInput.value = '';
                showCustomSubjectInput();
                customSubjectInput?.focus();
            } else {
                if (value) {
                    courseNameInput.value = value;
                }
                hideCustomSubjectInput();
            }
        });

        function addPresetIfMissing(name) {
            const trimmed = (name || '').trim();
            if (!trimmed) return;
            const exists = Array.from(presetCourseSelect.options).some(
                opt => opt.value.toLowerCase() === trimmed.toLowerCase()
            );
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = trimmed;
                opt.textContent = trimmed;
                const customOption = Array.from(presetCourseSelect.options).find(opt => opt.value === '__custom__');
                if (customOption?.parentElement) {
                    customOption.parentElement.insertBefore(opt, customOption);
                } else {
                    presetCourseSelect.appendChild(opt);
                }
            }
            presetCourseSelect.value = trimmed;
            return trimmed;
        }

        function showCustomSubjectInput() {
            customSubjectWrapper?.classList.remove('hidden');
        }

        function hideCustomSubjectInput() {
            customSubjectWrapper?.classList.add('hidden');
            if (customSubjectInput) customSubjectInput.value = '';
        }

        // เมื่อพิมพ์วิชาใหม่ ให้เพิ่มเข้า dropdown ด้วย
        courseNameInput.addEventListener('blur', () => {
            addPresetIfMissing(courseNameInput.value);
        });

        const addCustomSubject = () => {
            const created = addPresetIfMissing(customSubjectInput?.value || '');
            if (!created) return;
            courseNameInput.value = created;
            hideCustomSubjectInput();
            courseNameInput.focus();
        };

        addCustomSubjectBtn?.addEventListener('click', addCustomSubject);
        customSubjectInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCustomSubject();
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
