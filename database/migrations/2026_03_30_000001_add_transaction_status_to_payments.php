<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom transaction_status untuk tracking status dari Midtrans
     * Status: pending, capture, settlement, deny, cancel, expire, require_action
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'transaction_status')) {
                $table->string('transaction_status')
                    ->nullable()
                    ->after('status')
                    ->comment('Status dari Midtrans: pending, capture, settlement, deny, cancel, expire');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'transaction_status')) {
                $table->dropColumn('transaction_status');
            }
        });
    }
};
