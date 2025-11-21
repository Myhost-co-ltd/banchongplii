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

        {{-- ✅ DROPDOWN เลือกหลักสูตรที่ครูสร้างไว้ --}}
        @if(!empty($courses) && count($courses))
            <div class="mt-4">
                <label for="courseSelector" class="block text-sm font-semibold text-gray-700 mb-1">
                    เลือกหลักสูตรที่ต้องการดู
                </label>
                <select id="courseSelector"
                        class="w-full md:w-80 border border-gray-200 rounded-xl px-4 py-2
                               focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    @foreach($courses as $courseItem)
                        <option
                            value="{{ route('course.detail', $courseItem) }}"
                            {{ optional($course)->id === $courseItem->id ? 'selected' : '' }}>
                            {{ $courseItem->name }}
                            @if(!empty($courseItem->grade))
                                ({{ $courseItem->grade }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <p class="text-sm text-gray-500 mt-3">
                ยังไม่มีหลักสูตรที่สร้างไว้ กรุณาเพิ่มหลักสูตรก่อน
            </p>
        @endif
    </div>

    @php
        // ใช้ $course ปัจจุบันสำหรับแสดงด้านล่าง ถ้าไม่มีให้ไม่แสดง blocks ด้านล่าง
        $currentCourse = $course ?? null;
    @endphp

    @if($currentCourse)
    <!-- ========================= -->
    <!--     COURSE INFORMATION    -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">ข้อมูลหลักสูตร</h3>

            <a href="{{ route('teacher.courses.edit', $currentCourse) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl">
                 แก้ไขข้อมูลหลักสูตร
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                <p class="font-semibold text-gray-800 text-lg">{{ $currentCourse->name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach ($currentCourse->rooms ?? [] as $room)
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">
                            {{ $room }}
                        </span>
                    @endforeach
                    @if(empty($currentCourse->rooms))
                        <span class="text-gray-400 text-sm">ยังไม่ได้กำหนดห้องเรียน</span>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500">ภาคเรียน</p>
                <select
                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    disabled>
                    <option value="">-- เลือกภาคเรียน --</option>
                    <option value="1" {{ $currentCourse->term == 1 ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                    <option value="2" {{ $currentCourse->term == 2 ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                </select>
            </div>

            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="font-semibold text-gray-800">{{ $currentCourse->year }}</p>
            </div>

            <div class="col-span-2">
                <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                <p class="text-gray-700 mt-1 leading-relaxed">{{ $currentCourse->description }}</p>
            </div>

        </div>
    </div>

    @php
        $teachingHours = $currentCourse->teaching_hours ?? [];
        $lessons       = $currentCourse->lessons ?? [];
        $assignments   = $currentCourse->assignments ?? [];
    @endphp


    <!-- ========================= -->
    <!--     TEACHING HOURS        -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">ชั่วโมงที่สอน (ภาพรวม)</h3>

            <button onclick="toggleHourInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มชั่วโมง
            </button>
        </div>

        <div id="hourList" class="space-y-3 mb-4">
            @foreach ($teachingHours as $hour)
                <div class="p-4 bg-gray-100 rounded-xl flex justify-between">
                    <span>{{ $hour['category'] ?? '-' }} — {{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? 'ชั่วโมง/สัปดาห์' }}</span>
                    <button class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
                </div>
            @endforeach
        </div>

        <!-- input form -->
        <div id="hourInputArea" class="grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
            <input type="text" id="newHourName" placeholder="หัวข้อ"
                   class="border rounded-lg px-3 py-2">

            <input type="number" id="newHourValue" placeholder="ชั่วโมง" min="1"
                   class="border rounded-lg px-3 py-2">

            <button onclick="saveTeachHour()"
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

            <button onclick="toggleTopicInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มหัวข้อ
            </button>
        </div>

        <div id="topicList" class="space-y-3">
            @foreach($lessons as $lesson)
                <div class="p-4 bg-gray-100 rounded-xl">
                    <div class="flex justify-between">
                        <span class="font-semibold">{{ $lesson['title'] ?? '-' }}</span>
                        <button class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
                    </div>

                    <p class="text-sm text-gray-600 mt-1">
                        ใช้เวลา: <b>{{ $lesson['hours'] ?? '-' }} ชั่วโมง</b>
                        — ช่วงเวลา: <b>{{ $lesson['period'] ?? '-' }}</b>
                    </p>

                    <button onclick="toggleDetail(this)" class="text-blue-600 text-sm mt-2">
                        ▶ ดูรายละเอียด
                    </button>

                    <div class="hidden detail text-gray-600 mt-2 pl-4">
                        {{ $lesson['details'] ?? 'ยังไม่ได้ระบุรายละเอียดเพิ่มเติม' }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- input topic -->
        <div id="topicInput" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3 hidden">

            <input type="text" id="newTopic" placeholder="หัวข้อบทเรียน" class="border rounded-lg px-3 py-2 col-span-2">

            <input type="number" id="newTopicHour" placeholder="ชั่วโมง" class="border rounded-lg px-3 py-2">

            <select id="newTopicPeriod" class="border rounded-lg px-3 py-2">
                <option value="">เลือกช่วงเวลา</option>
                <option>เดือน 1–2</option>
                <option>เดือน 3–4</option>
                <option>สัปดาห์ 1–2</option>
                <option>สัปดาห์ 3–4</option>
            </select>

            <button onclick="saveTopic()" class="bg-green-600 text-white px-4 py-2 rounded-xl col-span-4">
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

            <button onclick="toggleHWInput()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">
                 เพิ่มการบ้าน
            </button>
        </div>

        <div id="hwList" class="space-y-3">
            @foreach($assignments as $assignment)
                <div class="p-4 bg-gray-100 rounded-xl">
                    <div class="flex justify-between">
                        <span class="font-semibold">{{ $assignment['title'] ?? '-' }}</span>
                        <button class="text-red-600 hover:text-red-800"
                                onclick="confirmDelete(this)">ลบ</button>
                    </div>

                    @if(!empty($assignment['due_date']))
                        <p class="text-sm text-gray-600 mt-1">📅 กำหนดส่ง: {{ $assignment['due_date'] }}</p>
                    @endif
                    <p class="text-sm text-gray-600">🏆 คะแนนเต็ม: {{ $assignment['score'] ?? '-' }} คะแนน</p>
                </div>
            @endforeach
        </div>

        <!-- input HW -->
        <div id="hwInput" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 hidden">

            <input type="text" id="newHW" placeholder="ชื่อการบ้าน"
                   class="border rounded-lg px-3 py-2">

            <input type="date" id="newHWDate"
                   class="border rounded-lg px-3 py-2">

            <input type="number" id="newHWScore" placeholder="คะแนนเต็ม"
                   class="border rounded-lg px-3 py-2">

            <button onclick="saveHW()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl col-span-3">
                ✔ บันทึกการบ้าน
            </button>
        </div>

    </div>
    @endif {{-- จบส่วนที่ต้องมี course --}}
</div>
@endsection



<!-- ============================= -->
<!--             SCRIPT            -->
<!-- ============================= -->
<script>
// เปลี่ยนหลักสูตรเมื่อเลือกจาก dropdown
document.addEventListener('DOMContentLoaded', () => {
    const courseSelector = document.getElementById('courseSelector');
    if (courseSelector) {
        courseSelector.addEventListener('change', (event) => {
            const url = event.target.value;
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
    }
}

// ----------------------------- //
//          TEACH HOURS          //
// ----------------------------- //
function toggleHourInput(){
    hourInputArea.classList.toggle("hidden");
}

function saveTeachHour(){
    let name = newHourName.value.trim();
    let hour = newHourValue.value.trim();

    if(!name || !hour){
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    hourList.insertAdjacentHTML("beforeend", `
        <div class="p-4 bg-gray-100 rounded-xl flex justify-between">
            <span>${name} — ${hour} ชั่วโมง/สัปดาห์</span>
            <button class="text-red-600 hover:text-red-800"
                    onclick="confirmDelete(this)">ลบ</button>
        </div>
    `);

    newHourName.value = "";
    newHourValue.value = "";
    hourInputArea.classList.add("hidden");
}

// ----------------------------- //
//     TOPICS + RELATION HOURS   //
// ----------------------------- //
function toggleDetail(btn){
    let box = btn.nextElementSibling;
    box.classList.toggle("hidden");

    btn.innerText = box.classList.contains("hidden")
        ? "▶ ดูรายละเอียด"
        : "▼ ซ่อนรายละเอียด";
}

function toggleTopicInput(){
    topicInput.classList.toggle("hidden");
}

function saveTopic(){
    let title = newTopic.value.trim();
    let hour = newTopicHour.value.trim();
    let period = newTopicPeriod.value;

    if(!title || !hour || !period){
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    topicList.insertAdjacentHTML("beforeend", `
        <div class="p-4 bg-gray-100 rounded-xl">
            <div class="flex justify-between">
                <span class="font-semibold">${title}</span>
                <button class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
            </div>
            <p class="text-sm text-gray-600 mt-1">
                 ใช้เวลา: <b>${hour} ชั่วโมง</b> — ช่วงเวลา: <b>${period}</b>
            </p>
            <button onclick="toggleDetail(this)" class="text-blue-600 text-sm mt-2">
                ▶ ดูรายละเอียด
            </button>
            <div class="hidden detail text-gray-600 mt-2 pl-4">
                - เพิ่มรายละเอียดเพิ่มเติมได้ในภายหลัง
            </div>
        </div>
    `);

    newTopic.value = "";
    newTopicHour.value = "";
    newTopicPeriod.value = "";
    topicInput.classList.add("hidden");
}

// ----------------------------- //
//            HOMEWORK           //
// ----------------------------- //
function toggleHWInput(){
    hwInput.classList.toggle("hidden");
}

function saveHW(){
    let hw = newHW.value.trim();
    let date = newHWDate.value;
    let score = newHWScore.value;

    if(!hw || !date || !score){
        alert("กรุณากรอกข้อมูลให้ครบ");
        return;
    }

    const d = new Date(date);
    const thaiYear = d.getFullYear() + 543;
    const thaiMonths = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                        "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    const formatted = `${d.getDate()} ${thaiMonths[d.getMonth()]} ${thaiYear}`;

    hwList.insertAdjacentHTML("beforeend", `
        <div class="p-4 bg-gray-100 rounded-xl">
            <div class="flex justify-between">
                <span class="font-semibold">${hw}</span>
                <button class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
            </div>
            <p class="text-sm text-gray-600 mt-1"> กำหนดส่ง: ${formatted}</p>
            <p class="text-sm text-gray-600"> คะแนนเต็ม: ${score} คะแนน</p>
        </div>
    `);

    newHW.value = "";
    newHWDate.value = "";
    newHWScore.value = "";
    hwInput.classList.add("hidden");
}
</script>
