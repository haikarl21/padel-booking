<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom snap_token untuk menyimpan token dari Midtrans Snap
     * Token ini digunakan untuk menampilkan popup pembayaran di frontend
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'snap_token')) {
                $table->string('snap_token')
                    ->nullable()
                    ->after('transaction_status')
                    ->comment('Snap token dari Midtrans Snap untuk menampilkan popup');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'snap_token')) {
                $table->dropColumn('snap_token');
            }
        });
    }
};
