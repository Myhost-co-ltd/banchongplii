<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_teaching_hours') && Schema::hasColumn('course_teaching_hours', 'unit')) {
            Schema::table('course_teaching_hours', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_teaching_hours') && ! Schema::hasColumn('course_teaching_hours', 'unit')) {
            Schema::table('course_teaching_hours', function (Blueprint $table) {
                $table->string('unit', 50)->nullable()->after('hours');
            });
        }
    }
};
