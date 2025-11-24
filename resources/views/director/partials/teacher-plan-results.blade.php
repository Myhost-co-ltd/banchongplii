{{-- SUMMARY BOXES --}}
<div class="flex flex-wrap gap-4">
    <div class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-blue-100 text-blue-800 shadow-sm">
        <span class="text-sm font-medium text-blue-700">จำนวนหลักสูตร</span>
        <span class="text-2xl font-semibold leading-none">{{ $courses->count() }}</span>
    </div>
    <div class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-indigo-100 text-indigo-800 shadow-sm">
        <span class="text-sm font-medium text-indigo-700">จำนวนครูผู้สอน</span>
        <span class="text-2xl font-semibold leading-none">{{ $teacherCount }}</span>
    </div>
</div>

{{-- LIST COURSES --}}
<div class="grid grid-cols-1 gap-4 mt-4">
    @forelse ($courses as $course)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">หลักสูตร</p>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $course->name }}</h3>
                    </div>

                    <p class="text-sm text-gray-600">
                        ระดับ {{ $course->grade ?? '-' }}
                        @if (! empty($course->term))
                            | เทอม {{ $course->term }}
                        @endif
                        @if (! empty($course->year))
                            | ปี {{ $course->year }}
                        @endif
                    </p>

                    @if (! empty($course->rooms))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($course->rooms as $room)
                                <span class="px-3 py-1 text-xs bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                    {{ $room }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if (! empty($course->description))
                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ $course->description }}
                        </p>
                    @endif
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <p class="text-xs uppercase tracking-wide text-gray-500">ครูผู้สอน</p>
                    <p class="font-semibold text-gray-900">
                        {{ optional($course->teacher)->name ?? 'ยังไม่มีครูผู้สอน' }}
                    </p>
                    @if (! empty(optional($course->teacher)->email))
                        <p class="text-sm text-gray-600">
                            {{ $course->teacher->email }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            ไม่พบหลักสูตรสำหรับชั้นเรียนนี้
        </div>
    @endforelse
</div>
