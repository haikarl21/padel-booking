<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix kolom 'status' di tabel payments agar punya default value 'pending'
     * 
     * Error terjadi karena migration sebelumnya change status dari enum ke string
     * tanpa mempertahankan default value. Solusi: tambah default value.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Ganti kolom status agar punya default value 'pending'
            $table->string('status')
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')
                ->nullable()
                ->change();
        });
    }
};
