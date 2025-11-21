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

    <!-- ========================= -->
    <!--     COURSE INFORMATION    -->
    <!-- ========================= -->
    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">ข้อมูลหลักสูตร</h3>

            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl">
                 แก้ไขข้อมูลหลักสูตร
            </button>
        </div>

        @php
            $course = [
                'name' => 'คณิตศาสตร์พื้นฐาน ป.1',
                'rooms' => ['ป.1/1','ป.1/2'],
                'term' => '',
                'year' => 2567,
                'description' => 'หลักสูตรนี้ครอบคลุมพื้นฐานการบวก ลบ การนับเลข และการแก้ปัญหาเบื้องต้น'
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">ชื่อหลักสูตร</p>
                <p class="font-semibold text-gray-800 text-lg">{{ $course['name'] }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">ห้องเรียนที่สอน</p>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach ($course['rooms'] as $room)
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">
                            {{ $room }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500">ภาคเรียน</p>
                <select
                    class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">-- เลือกภาคเรียน --</option>
                    <option value="1" {{ $course['term'] == 1 ? 'selected' : '' }}>ภาคเรียนที่ 1</option>
                    <option value="2" {{ $course['term'] == 2 ? 'selected' : '' }}>ภาคเรียนที่ 2</option>
                </select>
            </div>

            <div>
                <p class="text-sm text-gray-500">ปีการศึกษา</p>
                <p class="font-semibold text-gray-800">{{ $course['year'] }}</p>
            </div>

            <div class="col-span-2">
                <p class="text-sm text-gray-500">รายละเอียดหลักสูตร</p>
                <p class="text-gray-700 mt-1 leading-relaxed">{{ $course['description'] }}</p>
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

        <div id="hourList" class="space-y-3 mb-4">
            <div class="p-4 bg-gray-100 rounded-xl flex justify-between">
                <span>สอนทฤษฎี — 1 ชั่วโมง/สัปดาห์</span>
                <button type="button" class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
            </div>

            <div class="p-4 bg-gray-100 rounded-xl flex justify-between">
                <span>สอนปฏิบัติ — 2 ชั่วโมง/สัปดาห์</span>
                <button type="button" class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
            </div>
        </div>

        <!-- input form -->
        <div id="hourInputArea" class="grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
            <!-- dropdown หัวข้อ ทฤษฎี/ปฏิบัติ -->
            <select id="newHourName" class="border rounded-lg px-3 py-2">
                <option value="">เลือกหัวข้อ</option>
                <option value="สอนทฤษฎี">ทฤษฎี</option>
                <option value="สอนปฏิบัติ">ปฏิบัติ</option>
            </select>

            <!-- dropdown ชั่วโมง 1,2 -->
            <select id="newHourValue" class="border rounded-lg px-3 py-2">
                <option value="">เลือกชั่วโมง</option>
                <option value="1">1 ชั่วโมง</option>
                <option value="2">2 ชั่วโมง</option>
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

        {{-- รายการหัวข้อ จะถูกเติมด้วย JS --}}
        <div id="topicList" class="space-y-3"></div>

        <!-- input topic -->
        <div id="topicInput" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3 hidden">

            <input type="text" id="newTopic" placeholder="หัวข้อบทเรียน"
                   class="border rounded-lg px-3 py-2 col-span-2">

            <!-- dropdown ชั่วโมง 1–5 -->
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

        <div id="hwList" class="space-y-3">
            <div class="p-4 bg-gray-100 rounded-xl">
                <div class="flex justify-between">
                    <span class="font-semibold">ใบงานที่ 1 : นับจำนวนรูปภาพ</span>
                    <button type="button" class="text-red-600 hover:text-red-800"
                            onclick="confirmDelete(this)">ลบ</button>
                </div>

                <p class="text-sm text-gray-600 mt-1">📅 กำหนดส่ง: 12 มกราคม 2568</p>
                <p class="text-sm text-gray-600">🏆 คะแนนเต็ม: 10 คะแนน</p>
                <p class="text-sm text-gray-600 mt-1">รายละเอียด: ทำแบบฝึกหัดจากใบงานที่ 1</p>
            </div>
        </div>

        <!-- input HW -->
        <div id="hwInput" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 hidden">

            <!-- dropdown ชื่อการบ้านอิงจากหัวข้อเนื้อหา -->
            <select id="newHW" class="border rounded-lg px-3 py-2">
                <option value="">เลือกหัวข้อบทเรียน</option>
            </select>

            <input type="date" id="newHWDate"
                   class="border rounded-lg px-3 py-2">

            <input type="number" id="newHWScore" placeholder="คะแนนเต็ม"
                   class="border rounded-lg px-3 py-2">

            <!-- ช่องรายละเอียดการบ้าน -->
            <textarea id="newHWDetail" rows="2"
                      class="border rounded-lg px-3 py-2 md:col-span-3"
                      placeholder="รายละเอียดงาน / คำอธิบายการบ้าน"></textarea>

            <button type="button" onclick="saveHW()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl col-span-3">
                ✔ บันทึกการบ้าน
            </button>
        </div>

    </div>

</div>
@endsection



<!-- ============================= -->
<!--             SCRIPT            -->
<!-- ============================= -->
<script>
// ฟังก์ชันสร้างการ์ดหัวข้อ (มีโหมดแสดงผล + แก้ไข)
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

        <!-- บล็อกแก้ไข -->
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

// เริ่มต้น: ใส่หัวข้อตัวอย่าง + เติม dropdown การบ้าน
document.addEventListener('DOMContentLoaded', () => {
    const topicList = document.getElementById('topicList');

    const initialTopics = [
        {
            title: 'บทที่ 1 : การนับเลข 1–20',
            hour: '4',
            period: 'เดือน 1–2',
            details: '- ตัวเลข 1–20\n- การอ่านออกเสียง\n- แบบฝึกหัดพื้นฐาน'
        },
        {
            title: 'กบดหด',
            hour: '2',
            period: 'เดือน 1–2',
            details: '- เพิ่มรายละเอียดเพิ่มเติมได้ในภายหลัง'
        }
    ];

    initialTopics.forEach(t => {
        topicList.insertAdjacentHTML(
            'beforeend',
            createTopicCard(t.title, t.hour, t.period, t.details)
        );
    });

    populateHomeworkOptions();
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

    hourList.insertAdjacentHTML("beforeend", `
        <div class="p-4 bg-gray-100 rounded-xl flex justify-between">
            <span>${name} — ${hour} ชั่วโมง/สัปดาห์</span>
            <button type="button" class="text-red-600 hover:text-red-800"
                    onclick="confirmDelete(this)">ลบ</button>
        </div>
    `);

    document.getElementById('newHourName').value = "";
    document.getElementById('newHourValue').value = "";
    document.getElementById('hourInputArea').classList.add("hidden");
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

    // เพิ่มหัวข้อใหม่เข้า dropdown ชื่อการบ้านด้วย
    const hwSelect = document.getElementById('newHW');
    if (hwSelect && title) {
        const opt = document.createElement('option');
        opt.value = title;
        opt.textContent = title;
        hwSelect.appendChild(opt);
    }

    titleInput.value  = "";
    hourInput.value   = "";
    periodInput.value = "";
    detailInput.value = "";

    document.getElementById('topicInput').classList.add("hidden");
}

// เปิด/ปิดโหมดแก้ไขหัวข้อ
function toggleTopicEdit(btn){
    const card      = btn.closest('.topic-card');
    const editBlock = card.querySelector('.edit-block');
    const isHidden  = editBlock.classList.contains('hidden');

    if (isHidden) {
        // ดึงค่าปัจจุบันใส่ฟอร์ม
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

    // อัปเดต dropdown การบ้านให้มีหัวข้อที่เปลี่ยนชื่อใหม่ด้วย
    populateHomeworkOptions(true);
}



// ----------------------------- //
//            HOMEWORK           //
// ----------------------------- //

// ดึงหัวข้อเนื้อหาที่มีอยู่แล้วไปใส่ใน dropdown ชื่อการบ้าน
function populateHomeworkOptions(reset = false) {
    const hwSelect = document.getElementById('newHW');
    const titles = document.querySelectorAll('#topicList .topic-title');

    if (!hwSelect) return;

    if (reset) {
        // ล้าง option เดิม แล้วใส่ "เลือกหัวข้อบทเรียน" ใหม่
        hwSelect.innerHTML = '<option value="">เลือกหัวข้อบทเรียน</option>';
    }

    titles.forEach(el => {
        const t = el.textContent.trim();
        if (t) {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            hwSelect.appendChild(opt);
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

    // แปลง ค.ศ. → พ.ศ.
    const d = new Date(date);
    const thaiYear = d.getFullYear() + 543;
    const thaiMonths = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                        "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];

    const formatted = `${d.getDate()} ${thaiMonths[d.getMonth()]} ${thaiYear}`;

    hwList.insertAdjacentHTML("beforeend", `
        <div class="p-4 bg-gray-100 rounded-xl">
            <div class="flex justify-between">
                <span class="font-semibold">${hw}</span>
                <button type="button" class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">ลบ</button>
            </div>

            <p class="text-sm text-gray-600 mt-1"> กำหนดส่ง: ${formatted}</p>
            <p class="text-sm text-gray-600"> คะแนนเต็ม: ${score} คะแนน</p>
            ${detail ? `<p class="text-sm text-gray-600 mt-1">รายละเอียด: ${detail}</p>` : ""}
        </div>
    `);

    hwSelect.value = "";
    document.getElementById('newHWDate').value   = "";
    document.getElementById('newHWScore').value  = "";
    document.getElementById('newHWDetail').value = "";
    document.getElementById('hwInput').classList.add("hidden");
}
</script>
