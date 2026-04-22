<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'time_slot_ids')) {
                $table->json('time_slot_ids')->nullable()->after('time_slot_id')->comment('Array of all selected time slot IDs for multi-hour booking');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'time_slot_ids')) {
                $table->dropColumn('time_slot_ids');
            }
        });
    }
};
