@extends('layouts.layout')

@section('title', 'รายละเอียดหลักสูตร')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">

    <!-- ========================= -->
    <!--          HEADER           -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100 mb-2">
        <h2 class="text-3xl font-bold text-gray-900">รายละเอียดหลักสูตร</h2>
        <p class="text-gray-600 mt-2">ดูรายละเอียดของหลักสูตรที่ครูกำลังสอน</p>
    </div>

    {{-- =========================  --}}
    {{--    DROPDOWN เลือกหลักสูตร  --}}
    {{-- =========================  --}}
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
                                {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
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

    @if (! $course)
        {{-- ยังไม่ได้เลือกหลักสูตร / ไม่มีหลักสูตร --}}
        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <p class="text-gray-500">ยังไม่ได้เลือกหลักสูตร</p>
        </div>
    @else

    <!-- ========================= -->
    <!--     COURSE INFORMATION    -->
    <!-- ========================= -->
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
    <select
        class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none"
        name="term"
    >
        <option value="">-- เลือกภาคเรียน --</option>
        <option value="1" {{ $course->term == 1 ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
        <option value="2" {{ $course->term == 2 ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
    </select>
</div>


            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="font-semibold text-gray-800">{{ $course->year ?? '-' }}</p>
            </div>

            <div class="col-span-2">
                <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                <p class="text-gray-700 mt-1 leading-relaxed">
                    {{ $course->description ?: '-' }}
                </p>
            </div>

        </div>
    </div>


    <!-- ========================= -->
    <!--     TEACHING HOURS        -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">ชั่วโมงที่สอน (ภาพรวม)</h3>

            <button type="button" onclick="toggleHourInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มชั่วโมง
            </button>
        </div>

        {{-- การ์ดชั่วโมง จะถูกเติมด้วย JS --}}
        <div id="hourList" class="space-y-3 mb-4"></div>

        <!-- input form -->
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



    <!-- ========================= -->
    <!--           TOPICS          -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">เนื้อหาที่สอน + ระยะเวลา</h3>

            <button type="button" onclick="toggleTopicInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มหัวข้อ
            </button>
        </div>

        <div id="topicList" class="space-y-3"></div>

        <!-- input topic -->
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

            <button type="button" onclick="saveTopic()" class="bg-green-600 text-white px-4 py-2 rounded-xl col-span-4">
                ✔ บันทึกหัวข้อเนื้อหา
            </button>
        </div>

    </div>



    <!-- ========================= -->
    <!--        HOMEWORK AREA      -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">การบ้าน / ชิ้นงาน</h3>

            <button type="button" onclick="toggleHWInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มการบ้าน
            </button>
        </div>

        <div id="hwList" class="space-y-3"></div>

        <!-- input HW -->
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



<!-- ============================= -->
<!--             SCRIPT            -->
<!-- ============================= -->
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
                    <!-- options จะถูกเติมตอนเปิดแก้ไข -->
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

// ---------- init (ดึงข้อมูลจาก backend) ----------
document.addEventListener('DOMContentLoaded', () => {
    const hourList  = document.getElementById('hourList');
    const topicList = document.getElementById('topicList');
    const hwList    = document.getElementById('hwList');

    // เอา JSON จาก Laravel -> JS
    const initialHours    = @json($course?->teaching_hours ?? []);
    const initialTopics   = @json($course?->lessons ?? []);
    const initialHomework = @json($course?->assignments ?? []);

    // ชั่วโมงสอนจาก DB
    if (hourList && Array.isArray(initialHours)) {
        initialHours.forEach(h => {
            hourList.insertAdjacentHTML(
                'beforeend',
                createHourCard(h.category || h.name || '-', h.hours || h.value || '-')
            );
        });
    }

    // หัวข้อเนื้อหาจาก DB
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

    // การบ้านจาก DB
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

    // เตรียม options หัวข้อบทเรียนสำหรับ dropdown การบ้าน
    populateHomeworkOptions(true);

    // ✅ dropdown เลือกหลักสูตร -> redirect
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

// ----------------------------- //
//        DELETE CONFIRM         //
// ----------------------------- //
function confirmDelete(btn){
    if(confirm("ยืนยันการลบข้อมูลนี้?")){
        btn.closest(".p-4").remove();
        populateHomeworkOptions(true);
    }
}


// ----------------------------- //
//          TEACH HOURS          //
// ----------------------------- //
function toggleHourInput(){
    const hourInputArea = document.getElementById('hourInputArea');
    hourInputArea.classList.toggle("hidden");
}

function saveTeachHour(){
    const name = document.getElementById('newHourName').value.trim();
    const hour = document.getElementById('newHourValue').value.trim();
    const hourList = document.getElementById('hourList');

    if(!name || !hour){
        alert("กรุณาเลือกหัวข้อและจำนวนชั่วโมง");
        return;
    }

    hourList.insertAdjacentHTML("beforeend", createHourCard(name, hour));

    document.getElementById('newHourName').value = "";
    document.getElementById('newHourValue').value = "";
    document.getElementById('hourInputArea').classList.add("hidden");
}

function toggleHourEdit(btn){
    const card      = btn.closest('.hour-card');
    const editBlock = card.querySelector('.edit-block');
    const isHidden  = editBlock.classList.contains('hidden');

    if (isHidden) {
        const categorySpan = card.querySelector('.hour-category');
        const valueSpan    = card.querySelector('.hour-value');

        editBlock.querySelector('.edit-hour-category').value = categorySpan.textContent.trim();
        editBlock.querySelector('.edit-hour-value').value    = valueSpan.textContent.trim();
    }

    editBlock.classList.toggle('hidden');
}

function cancelHourEdit(btn){
    const card      = btn.closest('.hour-card');
    const editBlock = card.querySelector('.edit-block');
    editBlock.classList.add('hidden');
}

function saveHourEdit(btn){
    const card      = btn.closest('.hour-card');
    const editBlock = card.querySelector('.edit-block');

    const newCategory = editBlock.querySelector('.edit-hour-category').value.trim();
    const newValue    = editBlock.querySelector('.edit-hour-value').value.trim();

    if (!newCategory || !newValue) {
        alert('กรุณาเลือกหัวข้อและจำนวนชั่วโมง');
        return;
    }

    card.querySelector('.hour-category').textContent = newCategory;
    card.querySelector('.hour-value').textContent    = newValue;

    editBlock.classList.add('hidden');
}



// ----------------------------- //
//     TOPICS + RELATION HOURS   //
// ----------------------------- //
function toggleDetail(btn){
    const box = btn.nextElementSibling;
    box.classList.toggle("hidden");

    btn.innerText = box.classList.contains("hidden")
        ? "▶ ดูรายละเอียด"
        : "▼ ซ่อนรายละเอียด";
}

function toggleTopicInput(){
    const topicInput = document.getElementById('topicInput');
    topicInput.classList.toggle("hidden");
}

function saveTopic(){
    const titleInput   = document.getElementById('newTopic');
    const hourInput    = document.getElementById('newTopicHour');
    const periodInput  = document.getElementById('newTopicPeriod');
    const detailInput  = document.getElementById('newTopicDetail');
    const topicList    = document.getElementById('topicList');

    let title   = titleInput.value.trim();
    let hour    = hourInput.value.trim();
    let period  = periodInput.value;
    let details = detailInput.value.trim();

    if(!title || !hour || !period){
        alert("กรุณากรอกข้อมูลหัวข้อ / ชั่วโมง / ช่วงเวลา ให้ครบ");
        return;
    }

    topicList.insertAdjacentHTML(
        "beforeend",
        createTopicCard(title, hour, period, details || "- เพิ่มรายละเอียดเพิ่มเติมได้ในภายหลัง")
    );

    titleInput.value  = "";
    hourInput.value   = "";
    periodInput.value = "";
    detailInput.value = "";

    document.getElementById('topicInput').classList.add("hidden");

    populateHomeworkOptions(true);
}

function toggleTopicEdit(btn){
    const card      = btn.closest('.topic-card');
    const editBlock = card.querySelector('.edit-block');
    const isHidden  = editBlock.classList.contains('hidden');

    if (isHidden) {
        const titleSpan  = card.querySelector('.topic-title');
        const hoursSpan  = card.querySelector('.topic-hours');
        const periodSpan = card.querySelector('.topic-period');
        const detailsDiv = card.querySelector('.topic-details');

        editBlock.querySelector('.edit-title').value  = titleSpan.textContent.trim();
        editBlock.querySelector('.edit-hour').value   = hoursSpan.textContent.trim();
        editBlock.querySelector('.edit-period').value = periodSpan.textContent.trim();
        editBlock.querySelector('.edit-detail').value = detailsDiv.textContent.trim();
    }

    editBlock.classList.toggle('hidden');
}

function cancelTopicEdit(btn){
    const card      = btn.closest('.topic-card');
    const editBlock = card.querySelector('.edit-block');
    editBlock.classList.add('hidden');
}

function saveTopicEdit(btn){
    const card      = btn.closest('.topic-card');
    const editBlock = card.querySelector('.edit-block');

    const newTitle   = editBlock.querySelector('.edit-title').value.trim();
    const newHour    = editBlock.querySelector('.edit-hour').value.trim();
    const newPeriod  = editBlock.querySelector('.edit-period').value.trim();
    const newDetails = editBlock.querySelector('.edit-detail').value.trim();

    if(!newTitle || !newHour || !newPeriod){
        alert("กรุณากรอก: หัวข้อ / ชั่วโมง / ช่วงเวลา ให้ครบ");
        return;
    }

    card.querySelector('.topic-title').textContent   = newTitle;
    card.querySelector('.topic-hours').textContent   = newHour;
    card.querySelector('.topic-period').textContent  = newPeriod;
    card.querySelector('.topic-details').textContent = newDetails || 'ยังไม่ได้ระบุรายละเอียดเพิ่มเติม';

    editBlock.classList.add('hidden');

    populateHomeworkOptions(true);
}



// ----------------------------- //
//            HOMEWORK           //
// ----------------------------- //
function populateHomeworkOptions(reset = false) {
    const hwSelect = document.getElementById('newHW');
    const titles = document.querySelectorAll('#topicList .topic-title');

    if (!hwSelect) return;

    if (reset) {
        hwSelect.innerHTML = '<option value="">เลือกหัวข้อบทเรียน</option>';
    }

    titles.forEach(el => {
        const t = el.textContent.trim();
        if (t) {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (![...hwSelect.options].some(o => o.value === t)) {
                hwSelect.appendChild(opt);
            }
        }
    });
}

function toggleHWInput(){
    const hwInput = document.getElementById('hwInput');
    hwInput.classList.toggle("hidden");
}

function saveHW(){
    const hwSelect  = document.getElementById('newHW');
    const hw        = hwSelect.value.trim();
    const date      = document.getElementById('newHWDate').value;
    const score     = document.getElementById('newHWScore').value;
    const detail    = document.getElementById('newHWDetail').value.trim();
    const hwList    = document.getElementById('hwList');

    if(!hw || !date || !score){
        alert("กรุณาเลือกหัวข้อการบ้าน และกรอกวันที่/คะแนนเต็มให้ครบ");
        return;
    }

    hwList.insertAdjacentHTML(
        "beforeend",
        createHomeworkCard(hw, date, score, detail)
    );

    hwSelect.value = "";
    document.getElementById('newHWDate').value   = "";
    document.getElementById('newHWScore').value  = "";
    document.getElementById('newHWDetail').value = "";
    document.getElementById('hwInput').classList.add("hidden");
}

function toggleHWEdit(btn){
    const card      = btn.closest('.hw-card');
    const editBlock = card.querySelector('.edit-block');

    const currentTitle = card.querySelector('.hw-title').textContent.trim();
    const currentScore = card.querySelector('.hw-score').textContent.trim();
    const currentDate  = card.getAttribute('data-date') || '';
    const detailSpan   = card.querySelector('.hw-detail-text');
    const currentDetail = detailSpan ? detailSpan.textContent.trim() : '';

    // เติม options หัวข้อจาก topicList
    const select = editBlock.querySelector('.edit-hw-title');
    select.innerHTML = '<option value="">เลือกหัวข้อบทเรียน</option>';
    const titles = document.querySelectorAll('#topicList .topic-title');
    titles.forEach(el => {
        const t = el.textContent.trim();
        if (t) {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            select.appendChild(opt);
        }
    });
    if (currentTitle) {
        if (![...select.options].some(o => o.value === currentTitle)) {
            const extra = document.createElement('option');
            extra.value = currentTitle;
            extra.textContent = currentTitle;
            select.appendChild(extra);
        }
        select.value = currentTitle;
    }

    editBlock.querySelector('.edit-hw-date').value   = currentDate;
    editBlock.querySelector('.edit-hw-score').value  = currentScore;
    editBlock.querySelector('.edit-hw-detail').value = currentDetail;

    editBlock.classList.toggle('hidden');
}

function cancelHWEdit(btn){
    const card      = btn.closest('.hw-card');
    const editBlock = card.querySelector('.edit-block');
    editBlock.classList.add('hidden');
}

function saveHWEdit(btn){
    const card      = btn.closest('.hw-card');
    const editBlock = card.querySelector('.edit-block');

    const newTitle  = editBlock.querySelector('.edit-hw-title').value.trim();
    const newDate   = editBlock.querySelector('.edit-hw-date').value;
    const newScore  = editBlock.querySelector('.edit-hw-score').value.trim();
    const newDetail = editBlock.querySelector('.edit-hw-detail').value.trim();

    if (!newTitle || !newDate || !newScore) {
        alert('กรุณาเลือกหัวข้อการบ้าน และกรอกวันที่/คะแนนเต็มให้ครบ');
        return;
    }

    card.querySelector('.hw-title').textContent = newTitle;
    card.setAttribute('data-date', newDate);
    card.querySelector('.hw-date').textContent  = formatThaiDate(newDate);
    card.querySelector('.hw-score').textContent = newScore;

    let detailP = card.querySelector('.hw-detail');
    if (newDetail) {
        if (!detailP) {
            detailP = document.createElement('p');
            detailP.className = 'text-sm text-gray-600 mt-1 hw-detail';
            detailP.innerHTML = 'รายละเอียด: <span class="hw-detail-text"></span>';
            card.appendChild(detailP);
        }
        detailP.querySelector('.hw-detail-text').textContent = newDetail;
    } else if (detailP) {
        detailP.remove();
    }

    editBlock.classList.add('hidden');
}
</script>
