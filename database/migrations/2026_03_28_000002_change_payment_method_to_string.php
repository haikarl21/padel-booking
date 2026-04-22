<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY, only MySQL does
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(50) DEFAULT 'bank_transfer'");
        }
        // For SQLite, the column is already VARCHAR, no need to modify
    }

    public function down(): void
    {
        // Only revert for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('bank_transfer', 'cash') DEFAULT 'bank_transfer'");
        }
    }
};
