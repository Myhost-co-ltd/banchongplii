<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('term', 5);
            $table->date('attendance_date');
            $table->string('status', 20)->default('present');
            $table->unsignedDecimal('deduction_points', 6, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['course_id', 'student_id', 'term', 'attendance_date'],
                'course_attendance_unique'
            );
            $table->index(['course_id', 'term', 'attendance_date'], 'course_attendance_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_attendance_records');
    }
};
