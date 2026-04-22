<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('payments', 'order_id')) {
                $table->string('order_id')->nullable()->after('booking_id');
            }
            
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('order_id');
            }
            
            if (!Schema::hasColumn('payments', 'gross_amount')) {
                $table->decimal('gross_amount', 10, 2)->nullable()->after('amount');
            }
            
            if (!Schema::hasColumn('payments', 'midtrans_response')) {
                $table->longText('midtrans_response')->nullable()->after('gross_amount');
            }
            
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop columns if they exist
            $columns = ['order_id', 'transaction_id', 'gross_amount', 'midtrans_response', 'paid_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
