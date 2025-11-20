@extends('layouts.layout')

@section('title', '??????????????????')

@section('content')
@php($courseOptions = collect($courses ?? []))
<div class="space-y-8 overflow-y-auto pr-2 pb-10">

    <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm text-slate-500 uppercase tracking-widest">??????????????????</p>
                <h1 class="text-3xl font-bold text-gray-900">??????????????????????????????</h1>
                <p class="text-gray-600">????????????????????????????????????????? ?????????? ??????? ????????????????</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('teacher.course-create') }}"
                       class="px-4 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm">
                        ???????????????????????
                    </a>
                    @if($course)
                        <a href="{{ route('teacher.courses.edit', $course) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm">
                            ???????????????????
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-80">
                @if($courseOptions->isNotEmpty())
                    <label for="courseSelector" class="block text-sm font-semibold text-gray-700 mb-2">?????????????</label>
                    <select id="courseSelector"
                            class="w-full border border-gray-200 rounded-2xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($courseOptions as $courseOption)
                            <option value="{{ route('course.detail', $courseOption) }}"
                                    @selected(optional($course)->id === $courseOption->id)>
                                {{ $courseOption->name }} ({{ $courseOption->grade ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="border border-dashed border-gray-200 rounded-2xl p-4 text-sm text-gray-500">
                        ???????????????????????????
                    </div>
                @endif
            </div>
        </div>
    </div>

    @unless($course)
        <div class="bg-white rounded-3xl shadow-md p-10 border border-gray-100 text-center">
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">?????????????????????????</h3>
            <p class="text-gray-600 mb-6 max-w-3xl mx-auto">
                ??????????????????????????????????????????????????????
            </p>
            <a href="{{ route('teacher.course-create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-2xl shadow hover:bg-blue-500 transition">
                ?????????????????
            </a>
        </div>
    @else

        <div class="bg-white rounded-3xl shadow-md p-8 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">????????????</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $course->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">???????????????</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse($course->rooms ?? [] as $room)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-sm">{{ $room }}</span>
                        @empty
                            <span class="text-gray-400 text-sm">???????????????????????</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">????????</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $course->term ? '??????????? '.$course->term : '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">??????????</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $course->year ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">??????????????????</p>
                    <p class="text-gray-700 mt-1 leading-relaxed">{{ $course->description ?? '??????????????????' }}</p>
                </div>
            </div>
        </div>

        {{-- ????????????? --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">????????????? (??????)</h3>
                    <p class="text-sm text-gray-500">???????????????????????</p>
                </div>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-xl" onclick="toggleForm('hourForm')">
                    ????????????
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->teaching_hours ?? [] as $hour)
                    @php($hourId = $hour['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $hour['category'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">{{ $hour['hours'] ?? 0 }} {{ $hour['unit'] ?? '???????' }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                @if($hourId)
                                    <button type="button" class="text-blue-600 text-sm hover:underline" onclick="toggleEditForm('hour-edit-{{ $hourId }}')">
                                        ?????
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('teacher.courses.hours.destroy', ['course' => $course, 'hour' => $hourId ?? '']) }}"
                                      onsubmit="return confirm('???????????????????????????????')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">??</button>
                                </form>
                            </div>
                        </div>

                        @if($hourId)
                            <form id="hour-edit-{{ $hourId }}" method="POST"
                                  action="{{ route('teacher.courses.hours.update', ['course' => $course, 'hour' => $hourId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4">
                                @csrf
                                @method('PUT')
                                <select name="category" class="border rounded-xl px-3 py-2" required>
                                    <option value="?????" @selected(($hour['category'] ?? '') === '?????')>?????</option>
                                    <option value="???????" @selected(($hour['category'] ?? '') === '???????')>???????</option>
                                </select>
                                <input type="number" step="0.1" name="hours" class="border rounded-xl px-3 py-2"
                                       value="{{ $hour['hours'] ?? '' }}" required>
                                <select name="unit" class="border rounded-xl px-3 py-2" required>
                                    <option value="???????/???????" @selected(($hour['unit'] ?? '') === '???????/???????')>???????/???????</option>
                                </select>
                                <input type="hidden" name="note" value="">
                                <div class="md:col-span-4 text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">??????</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">????????????????????????</p>
                @endforelse
            </div>

            <form id="hourForm" method="POST" action="{{ route('teacher.courses.hours.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="category" class="border rounded-xl px-3 py-2" required>
                    <option value="">?????????????</option>
                    <option value="?????">?????</option>
                    <option value="???????">???????</option>
                </select>
                <input type="number" step="0.1" name="hours" class="border rounded-xl px-3 py-2" placeholder="???????" required>
                <select name="unit" class="border rounded-xl px-3 py-2" required>
                    <option value="">??????????</option>
                    <option value="???????/???????">???????/???????</option>
                </select>
                <input type="hidden" name="note" value="">
                <div class="md:col-span-4 text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">?????????????</button>
                </div>
            </form>
        </section>

        {{-- ????????????? --}}
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">????????????? + ????????</h3>
                    <p class="text-sm text-gray-500">????????????????????????????</p>
                </div>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-xl" onclick="toggleForm('lessonForm')">
                    ???????????
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->lessons ?? [] as $lesson)
                    @php($lessonId = $lesson['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $lesson['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">{{ $lesson['hours'] ?? 0 }} ??????? � {{ $lesson['period'] ?? '-' }}</p>
                                @if(!empty($lesson['details']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $lesson['details'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if($lessonId)
                                    <button type="button" class="text-blue-600 text-sm hover:underline" onclick="toggleEditForm('lesson-edit-{{ $lessonId }}')">
                                        ?????
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('teacher.courses.lessons.destroy', ['course' => $course, 'lesson' => $lessonId ?? '']) }}"
                                      onsubmit="return confirm('?????????????????????????????')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">??</button>
                                </form>
                            </div>
                        </div>

                        @if($lessonId)
                            <form id="lesson-edit-{{ $lessonId }}" method="POST"
                                  action="{{ route('teacher.courses.lessons.update', ['course' => $course, 'lesson' => $lessonId]) }}"
                                  class="hidden space-y-4">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <input type="text" name="title" class="border rounded-xl px-3 py-2"
                                           value="{{ $lesson['title'] ?? '' }}" required>
                                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                                        @foreach([1,2,3,4,5] as $hourOption)
                                            <option value="{{ $hourOption }}" @selected(($lesson['hours'] ?? null) == $hourOption)>
                                                {{ $hourOption }} ???????
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="period" class="border rounded-xl px-3 py-2" required>
                                        <option value="1-2 ?????" @selected(($lesson['period'] ?? '') === '1-2 ?????')>1-2 ?????</option>
                                        <option value="3-4 ?????" @selected(($lesson['period'] ?? '') === '3-4 ?????')>3-4 ?????</option>
                                    </select>
                                </div>
                                <textarea name="details" rows="3" class="w-full border rounded-xl px-3 py-2"
                                          placeholder="???????????????????">{{ $lesson['details'] ?? '' }}</textarea>
                                <div class="text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">??????</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">?????????????????????</p>
                @endforelse
            </div>

            <form id="lessonForm" method="POST" action="{{ route('teacher.courses.lessons.store', $course) }}"
                  class="hidden mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="title" class="border rounded-xl px-3 py-2" placeholder="?????????????" required>
                    <select name="hours" class="border rounded-xl px-3 py-2" required>
                        <option value="">????????????</option>
                        @foreach([1,2,3,4,5] as $hourOption)
                            <option value="{{ $hourOption }}">{{ $hourOption }} ???????</option>
                        @endforeach
                    </select>
                    <select name="period" class="border rounded-xl px-3 py-2" required>
                        <option value="">?????????????</option>
                        <option value="1-2 ?????">1-2 ?????</option>
                        <option value="3-4 ?????">3-4 ?????</option>
                    </select>
                </div>
                <textarea name="details" rows="3" class="w-full border rounded-xl px-3 py-2"
                          placeholder="???????????????????"></textarea>
                <div class="text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">????????????</button>
                </div>
            </form>
        </section>

        {{-- ??????? --}}
        @php($lessonTitles = collect($course->lessons ?? [])->pluck('title')->filter()->values())
        <section class="bg-white rounded-3xl shadow-md p-6 border border-gray-100 mb-10">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">??????? / ???????</h3>
                    <p class="text-sm text-gray-500">??????????????????????????????</p>
                </div>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-xl" onclick="toggleForm('assignmentForm')">
                    ????????????
                </button>
            </div>

            <div class="space-y-4">
                @forelse($course->assignments ?? [] as $assignment)
                    @php($assignmentId = $assignment['id'] ?? null)
                    <div class="border border-gray-100 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $assignment['title'] ?? '-' }}</p>
                                <p class="text-sm text-gray-600">
                                    ????????: {{ $assignment['score'] ?? '-' }}
                                    @if(!empty($assignment['due_date']))
                                        <span class="mx-2 text-gray-300">|</span>
                                        <!-- ????????: {{ \\Illuminate\\Support\\Carbon::parse($assignment['due_date'])->locale('th')->isoFormat('D MMM YYYY') }} -->
                                    @endif
                                </p>
                                @if(!empty($assignment['notes']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $assignment['notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                @if($assignmentId)
                                    <button type="button" class="text-blue-600 text-sm hover:underline" onclick="toggleEditForm('assignment-edit-{{ $assignmentId }}')">
                                        ?????
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('teacher.courses.assignments.destroy', ['course' => $course, 'assignment' => $assignmentId ?? '']) }}"
                                      onsubmit="return confirm('?????????????????????????')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 text-sm hover:underline">??</button>
                                </form>
                            </div>
                        </div>

                        @if($assignmentId)
                            <form id="assignment-edit-{{ $assignmentId }}" method="POST"
                                  action="{{ route('teacher.courses.assignments.update', ['course' => $course, 'assignment' => $assignmentId]) }}"
                                  class="hidden grid grid-cols-1 md:grid-cols-4 gap-4">
                                @csrf
                                @method('PUT')
                                <select name="title" class="border rounded-xl px-3 py-2" required>
                                    @foreach($lessonTitles as $title)
                                        <option value="{{ $title }}" @selected(($assignment['title'] ?? '') === $title)>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="date" name="due_date" class="border rounded-xl px-3 py-2"
                                       value="{{ $assignment['due_date'] ?? '' }}">
                                <input type="number" step="0.1" name="score" class="border rounded-xl px-3 py-2"
                                       value="{{ $assignment['score'] ?? '' }}" required>
                                <textarea name="notes" rows="1" class="border rounded-xl px-3 py-2"
                                          placeholder="??????????????????????">{{ $assignment['notes'] ?? '' }}</textarea>
                                <div class="md:col-span-4 text-right">
                                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl">??????</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center">?????????????????????????</p>
                @endforelse
            </div>

            <form id="assignmentForm" method="POST" action="{{ route('teacher.courses.assignments.store', $course) }}"
                  class="hidden mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <select name="title" class="border rounded-xl px-3 py-2" required {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>
                    <option value="">?????????????????????</option>
                    @foreach($lessonTitles as $title)
                        <option value="{{ $title }}">{{ $title }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_date" class="border rounded-xl px-3 py-2">
                <input type="number" step="0.1" name="score" class="border rounded-xl px-3 py-2" placeholder="????????" required>
                <textarea name="notes" rows="1" class="border rounded-xl px-3 py-2" placeholder="??????????????????????"></textarea>
                <div class="md:col-span-4 text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl" {{ $lessonTitles->isEmpty() ? 'disabled' : '' }}>?????????????</button>
                </div>
            </form>
        </section>
    @endunless
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const courseSelector = document.getElementById('courseSelector');
        if (courseSelector) {
            courseSelector.addEventListener('change', event => {
                const targetUrl = event.target.value;
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        }
    });

    function toggleForm(id) {
        const block = document.getElementById(id);
        if (block) {
            block.classList.toggle('hidden');
        }
    }

    function toggleEditForm(id) {
        const block = document.getElementById(id);
        if (block) {
            block.classList.toggle('hidden');
        }
    }
</script>
@endsection
