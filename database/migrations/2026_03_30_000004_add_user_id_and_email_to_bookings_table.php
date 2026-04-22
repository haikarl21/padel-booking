<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add user_id field for tracking which user made the booking
            if (!Schema::hasColumn('bookings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Add email field for guest bookings
            if (!Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'user_id')) {
                $table->dropForeignKeyIfExists(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('bookings', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
