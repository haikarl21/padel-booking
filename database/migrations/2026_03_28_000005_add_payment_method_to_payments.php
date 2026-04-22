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
        Schema::table('payments', function (Blueprint $table) {
            // Add payment_method untuk support multiple payment methods
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->default('bank_transfer')->after('status');
                // Supported methods: 'bank_transfer', 'ewallet', 'qrcode_dynamic', etc
            }

            // Add payment_details JSON untuk store method-specific info
            if (!Schema::hasColumn('payments', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('payment_method');
                // Contoh: {
                //   "bank": "BCA",
                //   "account_number": "1234567890",
                //   "account_name": "Padel Booking",
                //   "bank_code": "014"
                // }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            
            if (Schema::hasColumn('payments', 'payment_details')) {
                $table->dropColumn('payment_details');
            }
        });
    }
};
