<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [];
        $code = 10001;

        // ชั้น ป.1-ป.6 ห้อง 1-10
        for ($grade = 1; $grade <= 6; $grade++) {
            for ($room = 1; $room <= 10; $room++) {
                $students[] = [
                    'student_code' => (string) $code++,
                    'title'        => 'เด็กชาย',
                    'first_name'   => "นักเรียน$grade$room",
                    'last_name'    => "ป{$grade}ห้อง{$room}",
                    'gender'       => 'M',
                    'room'         => "ป.$grade/$room",
                ];
            }
        }

        Student::insert($students);
    }
}
