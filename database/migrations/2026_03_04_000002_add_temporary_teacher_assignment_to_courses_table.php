<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('temporary_teacher_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->date('temporary_until')
                ->nullable()
                ->after('temporary_teacher_id');

            $table->foreignId('temporary_assigned_by')
                ->nullable()
                ->after('temporary_until')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['temporary_teacher_id']);
            $table->dropForeign(['temporary_assigned_by']);
            $table->dropColumn([
                'temporary_teacher_id',
                'temporary_until',
                'temporary_assigned_by',
            ]);
        });
    }
};

