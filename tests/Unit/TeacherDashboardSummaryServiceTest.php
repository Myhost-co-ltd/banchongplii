<?php

namespace Tests\Unit;

use App\Services\TeacherDashboardSummaryService;
use PHPUnit\Framework\TestCase;

class TeacherDashboardSummaryServiceTest extends TestCase
{
    public function test_it_uses_one_status_summary_for_all_dashboards(): void
    {
        $service = new TeacherDashboardSummaryService();

        $teacherUsers = collect([
            (object) ['id' => 1, 'name' => 'Teacher One', 'email' => 'one@example.com', 'major' => 'Math'],
            (object) ['id' => 2, 'name' => 'Teacher Two', 'email' => 'two@example.com', 'major' => 'Science'],
            (object) ['id' => 3, 'name' => 'Teacher Three', 'email' => 'three@example.com', 'major' => 'Thai'],
            (object) ['id' => 4, 'name' => 'Teacher Four', 'email' => 'four@example.com', 'major' => 'Art'],
        ]);

        $legacyTeacherDirectory = collect([
            ['id' => 101, 'name' => 'Legacy A', 'email' => ''],
            ['id' => 102, 'name' => 'Legacy B', 'email' => ''],
            ['id' => 103, 'name' => 'Legacy C', 'email' => ''],
            ['id' => 104, 'name' => 'Legacy D', 'email' => ''],
            ['id' => 105, 'name' => 'Legacy E', 'email' => ''],
        ]);

        $courses = collect([
            $this->course(
                id: 11,
                teacherId: 1,
                teacherName: 'Teacher One',
                teacherEmail: 'one@example.com',
                teacherMajor: 'Math',
                teachingHours: [['hours' => 1]],
                assignments: [['title' => 'A']]
            ),
            $this->course(
                id: 12,
                teacherId: 2,
                teacherName: 'Teacher Two',
                teacherEmail: 'two@example.com',
                teacherMajor: 'Science',
                teachingHours: [['hours' => 1]],
                assignments: []
            ),
            $this->course(
                id: 13,
                teacherId: 3,
                teacherName: 'Teacher Three',
                teacherEmail: 'three@example.com',
                teacherMajor: 'Thai',
                teachingHours: [['hours' => 1]],
                assignments: [['title' => 'B']]
            ),
        ]);

        $summary = $service->summarize($teacherUsers, $legacyTeacherDirectory, $courses);

        $this->assertSame(5, $summary['teacherCount']);
        $this->assertSame(2, $summary['completeTeacherCount']);
        $this->assertSame(2, $summary['incompleteTeacherCount']);
        $this->assertSame(
            ['Legacy A', 'Legacy B', 'Legacy C', 'Legacy D', 'Legacy E'],
            collect($summary['teacherListPayload'])->pluck('name')->all()
        );
        $this->assertSame(
            ['Teacher One', 'Teacher Three'],
            collect($summary['completeTeacherListPayload'])->pluck('name')->all()
        );
        $this->assertSame(
            ['Teacher Two', 'Teacher Four'],
            collect($summary['incompleteTeacherListPayload'])->pluck('name')->all()
        );
        $this->assertSame([], $summary['incompleteTeacherStatuses'][1]['courses']);
    }

    private function course(
        int $id,
        int $teacherId,
        string $teacherName,
        string $teacherEmail,
        string $teacherMajor,
        array $teachingHours,
        array $assignments
    ): object {
        return (object) [
            'id' => $id,
            'name' => 'Course ' . $id,
            'grade' => 'P' . $id,
            'teaching_hours' => $teachingHours,
            'assignments' => $assignments,
            'teacher' => (object) [
                'id' => $teacherId,
                'name' => $teacherName,
                'email' => $teacherEmail,
                'major' => $teacherMajor,
            ],
        ];
    }
}
