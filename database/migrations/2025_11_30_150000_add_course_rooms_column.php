<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'course_rooms')) {
                $table->json('course_rooms')->nullable()->after('rooms');
            }
        });

        // Backfill from existing rooms column
        if (Schema::hasColumn('courses', 'rooms') && Schema::hasColumn('courses', 'course_rooms')) {
            DB::table('courses')
                ->whereNotNull('rooms')
                ->update(['course_rooms' => DB::raw('rooms')]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'course_rooms')) {
                $table->dropColumn('course_rooms');
            }
        });
    }
};
