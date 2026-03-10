<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasColumn('courses', 'grade')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `courses` MODIFY COLUMN `grade` VARCHAR(20) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasColumn('courses', 'grade')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('courses')->whereNull('grade')->update(['grade' => '']);
        DB::statement('ALTER TABLE `courses` MODIFY COLUMN `grade` VARCHAR(20) NOT NULL');
    }
};
