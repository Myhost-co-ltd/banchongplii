@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    {{-- ============ HEADER ============ --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <h2 class="text-3xl font-bold text-gray-900">รายละเอียดหลักสูตร</h2>
        <p class="text-gray-600 mt-2">ดูรายละเอียดของหลักสูตรที่ครูกำลังสอน</p>
    </div>

    {{-- ================================= --}}
    {{--   DROPDOWN เลือกหลักสูตรจาก DB   --}}
    {{-- ================================= --}}
    @php
        /** @var \Illuminate\Support\Collection|\App\Models\Course[] $courses */
        $courseOptions = collect($courses ?? []);
    @endphp

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">เลือกหลักสูตร</h3>
                <p class="text-gray-500 text-sm mt-1">
                    เลือกหลักสูตรที่ครูสร้างไว้เพื่อดูรายละเอียดและจัดการข้อมูล
                </p>
            </div>

            <div class="w-full md:w-80">
                @if($courseOptions->isNotEmpty())
                    <label for="courseSelector" class="block text-sm text-gray-600 mb-1">
                        เลือกหลักสูตร
                    </label>
                    <select id="courseSelector"
                            class="w-full border border-gray-300 rounded-2xl px-4 py-2
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($courseOptions as $courseOption)
                            <option value="{{ route('course.detail', $courseOption) }}"
                                @selected(optional($course)->id === $courseOption->id)>
                                {{ $courseOption->name }}
                                ({{ $courseOption->grade ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <p class="text-gray-500 text-sm">
                        ยังไม่มีหลักสูตรที่สร้างไว้ กรุณาไปที่เมนู "สร้างหลักสูตร" ก่อน
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if(!$course)
        {{-- ไม่มีหลักสูตรให้แสดง --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <p class="text-gray-500">ยังไม่ได้เลือกหลักสูตร</p>
        </div>
    @else
    @php
        // ดึงข้อมูลจาก field JSON ของ model (ต้อง cast ใน Model เป็น array ด้วยจะดี)
        $teachingHours = $course->teaching_hours ?? [];
        $lessons       = $course->lessons ?? [];
        $assignments   = $course->assignments ?? [];
    @endphp

    {{-- ========================= --}}
    {{--     COURSE INFORMATION    --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">ข้อมูลหลักสูตร</h3>

            <a href="{{ route('teacher.courses.edit', $course) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl">
                แก้ไขข้อมูลหลักสูตร
            </a>
        </div>

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
                        <span class="text-gray-400 text-sm">-</span>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500">ภาคเรียน</p>
                <p class="font-semibold text-gray-800">
                    @if($course->term == 1)
                        ภาคเรียนที่ 1
                    @elseif($course->term == 2)
                        ภาคเรียนที่ 2
                    @else
                        -
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="font-semibold text-gray-800">{{ $course->year ?? '-' }}</p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                <p class="text-gray-700 mt-1 leading-relaxed">
                    {{ $course->description ?: '-' }}
                </p>
            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{--     TEACHING HOURS        --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">ชั่วโมงที่สอน (ภาพรวม)</h3>

            {{-- ปุ่มเพิ่ม (ตอนนี้ยังเป็น front-end-only; ถ้าจะให้บันทึกจริง ใช้ form POST ไปที่
                 route('teacher.courses.hours.store', $course) ได้) --}}
            <button type="button" onclick="toggleHourInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มชั่วโมง
            </button>
        </div>

        <div id="hourList" class="space-y-3 mb-4"></div>

        <div id="hourInputArea" class="grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
            <select id="newHourName" class="border rounded-lg px-3 py-2">
                <option value="">เลือกหัวข้อ</option>
                <option value="สอนทฤษฎี">ทฤษฎี</option>
                <option value="สอนปฏิบัติ">ปฏิบัติ</option>
            </select>

            <select id="newHourValue" class="border rounded-lg px-3 py-2">
                <option value="">เลือกชั่วโมง</option>
                <option value="1">1 ชั่วโมง</option>
                <option value="2">2 ชั่วโมง</option>
                <option value="3">3 ชั่วโมง</option>
                <option value="4">4 ชั่วโมง</option>
            </select>

            <button type="button" onclick="saveTeachHour()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl">
                ✔ บันทึก
            </button>
        </div>
    </div>


    {{-- ========================= --}}
    {{--           TOPICS          --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">เนื้อหาที่สอน + ระยะเวลา</h3>

            <button type="button" onclick="toggleTopicInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มหัวข้อ
            </button>
        </div>

        <div id="topicList" class="space-y-3"></div>

        <div id="topicInput" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3 hidden">

            <input type="text" id="newTopic" placeholder="หัวข้อบทเรียน"
                   class="border rounded-lg px-3 py-2 col-span-2">

            <select id="newTopicHour" class="border rounded-lg px-3 py-2">
                <option value="">ชั่วโมง</option>
                <option value="1">1 ชั่วโมง</option>
                <option value="2">2 ชั่วโมง</option>
                <option value="3">3 ชั่วโมง</option>
                <option value="4">4 ชั่วโมง</option>
                <option value="5">5 ชั่วโมง</option>
            </select>

            <select id="newTopicPeriod" class="border rounded-lg px-3 py-2">
                <option value="">เลือกช่วงเวลา</option>
                <option>เดือน 1–2</option>
                <option>เดือน 3–4</option>
                <option>สัปดาห์ 1–2</option>
                <option>สัปดาห์ 3–4</option>
            </select>

            <textarea id="newTopicDetail" rows="2"
                      class="border rounded-lg px-3 py-2 md:col-span-4"
                      placeholder="รายละเอียดเนื้อหา / สิ่งที่ต้องสอน"></textarea>

            <button type="button" onclick="saveTopic()"
                    class="bg-green-600 text-white px-4 py-2 rounded-xl col-span-4">
                ✔ บันทึกหัวข้อเนื้อหา
            </button>
        </div>

    </div>


    {{-- ========================= --}}
    {{--        HOMEWORK AREA      --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">การบ้าน / ชิ้นงาน</h3>

            <button type="button" onclick="toggleHWInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มการบ้าน
            </button>
        </div>

        <div id="hwList" class="space-y-3"></div>

        <div id="hwInput" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 hidden">

            <select id="newHW" class="border rounded-lg px-3 py-2">
                <option value="">เลือกหัวข้อบทเรียน</option>
            </select>

            <input type="date" id="newHWDate"
                   class="border rounded-lg px-3 py-2">

            <input type="number" id="newHWScore" placeholder="คะแนนเต็ม"
                   class="border rounded-lg px-3 py-2">

            <textarea id="newHWDetail" rows="2"
                      class="border rounded-lg px-3 py-2 md:col-span-3"
                      placeholder="รายละเอียดงาน / คำอธิบายการบ้าน"></textarea>

            <button type="button" onclick="saveHW()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl col-span-3">
                ✔ บันทึกการบ้าน
            </button>
        </div>

    </div>

    @endif {{-- end if $course --}}
</div>
@endsection



{{-- ============================= --}}
{{--             SCRIPT            --}}
{{-- ============================= --}}
<script>
// ---------- helper format thai date ----------
function formatThaiDate(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '-';
    const months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                    "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    const year = d.getFullYear() + 543;
    return `${d.getDate()} ${months[d.getMonth()]} ${year}`;
}

// ---------- create cards (hour / topic / homework) ----------
function createHourCard(category, hours) {
    category = category || 'สอนทฤษฎี';
    hours    = hours || '1';
    return `
    <div class="p-4 bg-gray-100 rounded-xl hour-card">
        <div class="flex justify-between">
            <span class="hour-display">
                <span class="hour-category">${category}</span>
                —
                <span class="hour-value">${hours}</span> ชั่วโมง/สัปดาห์
            </span>
            <div class="flex gap-3">
                <button type="button"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                        onclick="toggleHourEdit(this)">
                    แก้ไข
                </button>
                <button type="button"
                        class="text-red-600 hover:text-red-800 text-sm"
                        onclick="confirmDelete(this)">
                    ลบ
                </button>
            </div>
        </div>

        <div class="hidden edit-block mt-3 border-t pt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
            <select class="border rounded-lg px-3 py-2 edit-hour-category">
                <option value="สอนทฤษฎี">ทฤษฎี</option>
                <option value="สอนปฏิบัติ">ปฏิบัติ</option>
            </select>

            <select class="border rounded-lg px-3 py-2 edit-hour-value">
                <option value="1">1 ชั่วโมง</option>
                <option value="2">2 ชั่วโมง</option>
                <option value="3">3 ชั่วโมง</option>
                <option value="4">4 ชั่วโมง</option>
            </select>

            <div class="flex justify-end gap-2 md:col-span-3">
                <button type="button"
                        class="px-3 py-1 rounded-lg bg-gray-200 text-gray-700 text-sm"
                        onclick="cancelHourEdit(this)">
                    ยกเลิก
                </button>
                <button type="button"
                        class="px-4 py-1 rounded-lg bg-green-600 text-white text-sm"
                        onclick="saveHourEdit(this)">
                    บันทึก
                </button>
            </div>
        </div>
    </div>
    `;
}

function createTopicCard(title, hour, period, details) {
    title   = title   || '-';
    hour    = hour    || '-';
    period  = period  || '-';
    details = details || 'ยังไม่ได้ระบุรายละเอียดเพิ่มเติม';

    return `
    <div class="p-4 bg-gray-100 rounded-xl topic-card">
        <div class="flex justify-between">
            <span class="font-semibold topic-title">${title}</span>
            <div class="flex gap-3">
                <button type="button"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                        onclick="toggleTopicEdit(this)">
                    แก้ไข
                </button>
                <button type="button"
                        class="text-red-600 hover:text-red-800 text-sm"
                        onclick="confirmDelete(this)">
                    ลบ
                </button>
            </div>
        </div>

        <p class="text-sm text-gray-600 mt-1">
            ใช้เวลา: <b class="topic-hours">${hour}</b> ชั่วโมง
            — ช่วงเวลา: <b class="topic-period">${period}</b>
        </p>

        <button type="button" onclick="toggleDetail(this)" class="text-blue-600 text-sm mt-2">
            ▶ ดูรายละเอียด
        </button>

        <div class="hidden detail text-gray-600 mt-2 pl-4">
            <div class="topic-details whitespace-pre-line">
                ${details}
            </div>
        </div>

        <div class="hidden edit-block mt-3 border-t pt-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text"
                       class="border rounded-lg px-3 py-2 md:col-span-2 edit-title"
                       placeholder="หัวข้อบทเรียน">

                <select class="border rounded-lg px-3 py-2 edit-hour">
                    <option value="">ชั่วโมง</option>
                    <option value="1">1 ชั่วโมง</option>
                    <option value="2">2 ชั่วโมง</option>
                    <option value="3">3 ชั่วโมง</option>
                    <option value="4">4 ชั่วโมง</option>
                    <option value="5">5 ชั่วโมง</option>
                </select>

                <select class="border rounded-lg px-3 py-2 edit-period">
                    <option value="">เลือกช่วงเวลา</option>
                    <option>เดือน 1–2</option>
                    <option>เดือน 3–4</option>
                    <option>สัปดาห์ 1–2</option>
                    <option>สัปดาห์ 3–4</option>
                </select>
            </div>

            <textarea rows="2"
                      class="border rounded-lg px-3 py-2 w-full mt-2 edit-detail"
                      placeholder="รายละเอียดเนื้อหา"></textarea>

            <div class="flex justify-end gap-2 mt-2">
                <button type="button"
                        class="px-3 py-1 rounded-lg bg-gray-200 text-gray-700 text-sm"
                        onclick="cancelTopicEdit(this)">
                    ยกเลิก
                </button>
                <button type="button"
                        class="px-4 py-1 rounded-lg bg-green-600 text-white text-sm"
                        onclick="saveTopicEdit(this)">
                    บันทึก
                </button>
            </div>
        </div>
    </div>
    `;
}

function createHomeworkCard(title, isoDate, score, detail) {
    title  = title  || '-';
    score  = score  || '-';
    detail = detail || '';

    const thaiDate = formatThaiDate(isoDate);

    return `
    <div class="p-4 bg-gray-100 rounded-xl hw-card" data-date="${isoDate}">
        <div class="flex justify-between">
            <span class="font-semibold hw-title">${title}</span>
            <div class="flex gap-3">
                <button type="button"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                        onclick="toggleHWEdit(this)">
                    แก้ไข
                </button>
                <button type="button"
                        class="text-red-600 hover:text-red-800 text-sm"
                        onclick="confirmDelete(this)">
                    ลบ
                </button>
            </div>
        </div>

        <p class="text-sm text-gray-600 mt-1">
            กำหนดส่ง: <span class="hw-date">${thaiDate}</span>
        </p>
        <p class="text-sm text-gray-600">
            คะแนนเต็ม: <span class="hw-score">${score}</span> คะแนน
        </p>
        ${detail ? `<p class="text-sm text-gray-600 mt-1 hw-detail">รายละเอียด: <span class="hw-detail-text">${detail}</span></p>` : ''}

        <div class="hidden edit-block mt-3 border-t pt-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <select class="border rounded-lg px-3 py-2 edit-hw-title">
                </select>

                <input type="date" class="border rounded-lg px-3 py-2 edit-hw-date">

                <input type="number" class="border rounded-lg px-3 py-2 edit-hw-score"
                       placeholder="คะแนนเต็ม">
            </div>

            <textarea rows="2"
                      class="border rounded-lg px-3 py-2 w-full mt-2 edit-hw-detail"
                      placeholder="รายละเอียดการบ้าน"></textarea>

            <div class="flex justify-end gap-2 mt-2">
                <button type="button"
                        class="px-3 py-1 rounded-lg bg-gray-200 text-gray-700 text-sm"
                        onclick="cancelHWEdit(this)">
                    ยกเลิก
                </button>
                <button type="button"
                        class="px-4 py-1 rounded-lg bg-green-600 text-white text-sm"
                        onclick="saveHWEdit(this)">
                    บันทึก
                </button>
            </div>
        </div>
    </div>
    `;
}

// ---------- init ----------
document.addEventListener('DOMContentLoaded', () => {
    const hourList  = document.getElementById('hourList');
    const topicList = document.getElementById('topicList');
    const hwList    = document.getElementById('hwList');

    // ✅ ดึงข้อมูลจริงจาก backend (PHP → JS)
    const initialHours      = @json($teachingHours ?? []);
    const initialTopics     = @json($lessons ?? []);
    const initialHomework   = @json($assignments ?? []);

    // เติมชั่วโมงสอนจาก DB
    if (hourList && Array.isArray(initialHours)) {
        initialHours.forEach(h => {
            hourList.insertAdjacentHTML(
                'beforeend',
                createHourCard(h.category || h.name || '-', h.hours || h.value || '-')
            );
        });
    }

    // เติมหัวข้อเนื้อหาจาก DB
    if (topicList && Array.isArray(initialTopics)) {
        initialTopics.forEach(t => {
            topicList.insertAdjacentHTML(
                'beforeend',
                createTopicCard(
                    t.title   || '-',
                    t.hours   || '-',
                    t.period  || '-',
                    t.details || ''
                )
            );
        });
    }

    // เติมการบ้านจาก DB
    if (hwList && Array.isArray(initialHomework)) {
        initialHomework.forEach(a => {
            hwList.insertAdjacentHTML(
                'beforeend',
                createHomeworkCard(
                    a.title    || '-',
                    a.due_date || '',
                    a.score    ?? '',
                    a.notes    || ''
                )
            );
        });
    }

    // ดึงหัวข้อไปใส่ dropdown การบ้าน
    populateHomeworkOptions(true);

    // ✅ dropdown เลือกหลักสูตร → redirect ไป course นั้น
    const courseSelector = document.getElementById('courseSelector');
    if (courseSelector) {
        courseSelector.addEventListener('change', (e) => {
            const url = e.target.value;
            if (url) {
                window.location.href = url;
            }
        });
    }
});

// ---- ที่เหลือเหมือนโค้ดเดิมของพี่ (confirmDelete, toggleHourInput, saveTopic ฯลฯ) ----
// ... (โค้ดส่วนล่างของพี่ใช้ต่อได้เลย ไม่ต้องเปลี่ยน)
</script>
