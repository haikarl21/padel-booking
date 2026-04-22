<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // expand payment_method enum to include new options
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_transfer', 'qris', 'bca', 'bri', 'cash'])
                  ->default('bank_transfer')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_transfer','cash'])
                  ->default('bank_transfer')
                  ->change();
        });
    }
};