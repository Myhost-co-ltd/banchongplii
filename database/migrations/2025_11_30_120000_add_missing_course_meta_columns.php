<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'teaching_hours')) {
                $table->json('teaching_hours')->nullable()->after('description');
            }

            if (! Schema::hasColumn('courses', 'lessons')) {
                $table->json('lessons')->nullable()->after('teaching_hours');
            }

            if (! Schema::hasColumn('courses', 'assignments')) {
                $table->json('assignments')->nullable()->after('lessons');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'assignments')) {
                $table->dropColumn('assignments');
            }

            if (Schema::hasColumn('courses', 'lessons')) {
                $table->dropColumn('lessons');
            }

            if (Schema::hasColumn('courses', 'teaching_hours')) {
                $table->dropColumn('teaching_hours');
            }
        });
    }
};
