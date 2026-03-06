<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_attendance_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('term', 5);
            $table->date('holiday_date');
            $table->string('holiday_name', 255)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['course_id', 'term', 'holiday_date'],
                'course_attendance_holidays_unique'
            );
            $table->index(
                ['course_id', 'term', 'holiday_date'],
                'course_attendance_holidays_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_attendance_holidays');
    }
};
