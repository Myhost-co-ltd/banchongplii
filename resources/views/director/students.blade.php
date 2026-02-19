@extends('layouts.layout-director')

@section('title', 'รายชื่อนักเรียน')

@section('content')
<div class="space-y-8 overflow-y-auto pr-2">
    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <h2 class="text-3xl font-bold text-gray-900" data-i18n-th="รายชื่อนักเรียน" data-i18n-en="Student List">รายชื่อนักเรียน</h2>
        <p class="text-gray-600 mt-2"
           data-i18n-th="เลือกชั้นและห้อง เพื่อดูรายชื่อนักเรียน"
           data-i18n-en="Select grade and room to view students.">
            เลือกชั้นและห้อง เพื่อดูรายชื่อนักเรียน
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="w-full">
                <label for="studentGradeSelect"
                       class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="ชั้นเรียน"
                       data-i18n-en="Grade">
                    ชั้นเรียน
                </label>
                <select id="studentGradeSelect"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @forelse(($gradeOptions ?? collect()) as $grade)
                        <option value="{{ $grade }}" {{ ($selectedGrade ?? '') === $grade ? 'selected' : '' }}>
                            {{ $grade }}
                        </option>
                    @empty
                        <option value="">ไม่มีข้อมูลชั้นเรียน</option>
                    @endforelse
                </select>
            </div>

            <div class="w-full lg:col-span-2">
                <label for="studentRoomSelect"
                       class="block text-sm font-medium text-gray-700 mb-1"
                       data-i18n-th="ห้องเรียน"
                       data-i18n-en="Classroom">
                    ห้องเรียน
                </label>
                <select id="studentRoomSelect"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @forelse(($roomOptions ?? collect()) as $roomOption)
                        @php
                            $roomValue = $roomOption['value'] ?? '';
                            $roomLabel = $roomOption['room_label'] ?? $roomValue;
                            $roomCount = $roomOption['count'] ?? 0;
                        @endphp
                        <option value="{{ $roomValue }}" {{ ($selectedRoom ?? '') === $roomValue ? 'selected' : '' }}>
                            ห้อง {{ $roomLabel }} ({{ number_format($roomCount) }} คน)
                        </option>
                    @empty
                        <option value="">ไม่มีข้อมูลห้องเรียน</option>
                    @endforelse
                </select>
            </div>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            <span data-i18n-th="จำนวนนักเรียนทั้งหมด:" data-i18n-en="Total students:">จำนวนนักเรียนทั้งหมด:</span>
            {{ number_format($studentCount ?? 0) }}
        </p>

        <p id="studentRoomSummary" class="text-sm text-gray-600 mt-4"></p>
        <div id="studentRoomList" class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const roomStudentsData = @json($studentsByRoomPayload ?? []);
        const roomsByGradeData = @json($roomsByGradePayload ?? []);
        const roomMetaData = @json($roomMetaPayload ?? []);
        const initialGrade = @json($selectedGrade ?? '');
        const initialRoom = @json($selectedRoom ?? '');

        const gradeSelect = document.getElementById('studentGradeSelect');
        const roomSelect = document.getElementById('studentRoomSelect');
        const summaryEl = document.getElementById('studentRoomSummary');
        const listEl = document.getElementById('studentRoomList');

        const labels = {
            th: {
                noRoomData: 'ไม่มีข้อมูลห้องเรียน',
                noRoomSelected: 'กรุณาเลือกห้องเรียน',
                noStudents: 'ไม่มีนักเรียนในห้องนี้',
                roomPrefix: 'ห้อง',
                gradePrefix: 'ชั้น',
                people: 'คน',
                studentCode: 'รหัส'
            },
            en: {
                noRoomData: 'No classroom data',
                noRoomSelected: 'Please select a classroom',
                noStudents: 'No students in this classroom',
                roomPrefix: 'Room',
                gradePrefix: 'Grade',
                people: 'students',
                studentCode: 'Code'
            }
        };

        const currentLang = () => {
            const locale = (localStorage.getItem('appLocale') || document.documentElement.getAttribute('lang') || 'th').toLowerCase();
            return locale.startsWith('en') ? 'en' : 'th';
        };

        const t = (key) => labels[currentLang()][key] || labels.th[key];

        const renderRoomOptions = (grade, preferredRoom = '') => {
            if (!roomSelect) return '';

            const rooms = roomsByGradeData[grade] || [];
            roomSelect.innerHTML = '';

            if (!rooms.length) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = t('noRoomData');
                roomSelect.appendChild(option);
                return '';
            }

            rooms.forEach((room) => {
                const option = document.createElement('option');
                option.value = room.value;
                option.textContent = `${t('roomPrefix')} ${room.room_label} (${room.count} ${t('people')})`;
                roomSelect.appendChild(option);
            });

            const validPreferred = rooms.some((room) => room.value === preferredRoom);
            const nextRoom = validPreferred ? preferredRoom : rooms[0].value;
            roomSelect.value = nextRoom;
            return nextRoom;
        };

        const renderStudents = (room) => {
            if (!summaryEl || !listEl) return;

            listEl.innerHTML = '';
            const students = room ? (roomStudentsData[room] || []) : [];
            const roomMeta = roomMetaData[room] || {};

            if (!room) {
                summaryEl.textContent = t('noRoomSelected');
                return;
            }

            const roomLabel = roomMeta.room_label || roomMeta.full_label || room;
            const gradeLabel = roomMeta.grade || '-';
            summaryEl.textContent = `${t('gradePrefix')} ${gradeLabel} • ${t('roomPrefix')} ${roomLabel} (${students.length} ${t('people')})`;

            if (!students.length) {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-gray-500';
                empty.textContent = t('noStudents');
                listEl.appendChild(empty);
                return;
            }

            students.forEach((student) => {
                const card = document.createElement('div');
                card.className = 'rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3';

                const nameEl = document.createElement('p');
                nameEl.className = 'text-sm font-semibold text-gray-900';
                nameEl.textContent = student.name || '-';

                const codeEl = document.createElement('p');
                codeEl.className = 'text-xs text-gray-500 mt-1';
                codeEl.textContent = `${t('studentCode')}: ${student.student_code || '-'}`;

                card.appendChild(nameEl);
                card.appendChild(codeEl);
                listEl.appendChild(card);
            });
        };

        const refreshSelections = (preferredRoom = '') => {
            const currentGrade = gradeSelect?.value || initialGrade || '';
            const nextRoom = renderRoomOptions(currentGrade, preferredRoom);
            renderStudents(nextRoom);
        };

        if (gradeSelect) {
            if (initialGrade && Array.from(gradeSelect.options).some((opt) => opt.value === initialGrade)) {
                gradeSelect.value = initialGrade;
            }

            gradeSelect.addEventListener('change', () => {
                refreshSelections('');
            });
        }

        roomSelect?.addEventListener('change', (event) => {
            renderStudents(event.target.value);
        });

        refreshSelections(initialRoom);

        document.querySelectorAll('[data-lang-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const selectedRoom = roomSelect?.value || '';
                window.setTimeout(() => {
                    refreshSelections(selectedRoom);
                }, 0);
            });
        });
    });
</script>
@endpush
