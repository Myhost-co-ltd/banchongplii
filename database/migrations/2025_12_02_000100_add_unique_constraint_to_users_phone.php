<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            return;
        }

        // Deduplicate phone numbers before adding unique constraint
        $duplicates = DB::table('users')
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            $ids = DB::table('users')
                ->where('phone', $phone)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            // keep first, null the rest
            array_shift($ids);
            if (! empty($ids)) {
                DB::table('users')
                    ->whereIn('id', $ids)
                    ->update(['phone' => null]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropUnique('users_phone_unique');
            }
        });
    }
};
