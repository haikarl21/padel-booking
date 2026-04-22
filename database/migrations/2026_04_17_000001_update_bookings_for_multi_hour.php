<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambahkan kolom baru HANYA jika belum ada
            if (!Schema::hasColumn('bookings', 'duration_hours')) {
                $table->integer('duration_hours')->default(1)->after('date');
            }
            if (!Schema::hasColumn('bookings', 'start_time')) {
                $table->time('start_time')->nullable()->after('duration_hours');
            }
            if (!Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['duration_hours', 'start_time', 'email']);
        });
    }
};
