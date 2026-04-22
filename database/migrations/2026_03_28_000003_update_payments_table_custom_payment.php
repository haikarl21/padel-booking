<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop old columns jika ada
            if (Schema::hasColumn('payments', 'proof_file_path')) {
                $table->dropColumn('proof_file_path');
            }
            
            // Add new columns untuk custom payment system
            if (!Schema::hasColumn('payments', 'unique_code')) {
                $table->string('unique_code', 3)->nullable()->after('order_id'); // 3 digit kode unik
            }
            
            if (!Schema::hasColumn('payments', 'total_unique')) {
                $table->decimal('total_unique', 10, 2)->nullable()->after('gross_amount'); // Total + kode unik
            }
            
            if (!Schema::hasColumn('payments', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('paid_at'); // Waktu expired
            }
            
            if (!Schema::hasColumn('payments', 'proof_file')) {
                $table->string('proof_file')->nullable()->after('expired_at'); // Nama file bukti
            }
            
            if (!Schema::hasColumn('payments', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete(); // User yang approve
            }
            
            if (!Schema::hasColumn('payments', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by'); // Alasan reject
            }
            
            // Change status enum to accept new values
            if (Schema::hasColumn('payments', 'status')) {
                $table->string('status')->change(); // Change dari enum ke string untuk flexibility
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = ['unique_code', 'total_unique', 'expired_at', 'proof_file', 'rejection_reason'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            if (Schema::hasColumn('payments', 'approved_by')) {
                $table->dropForeignKeyIfExists(['approved_by']);
                $table->dropColumn('approved_by');
            }
        });
    }
};
