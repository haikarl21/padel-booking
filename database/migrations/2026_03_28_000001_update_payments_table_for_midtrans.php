<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah struktur payments table untuk mendukung Midtrans integration
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Tambah kolom untuk Midtrans transaction tracking
            if (!Schema::hasColumn('payments', 'order_id')) {
                // order_id dari sistem kita (untuk reference)
                $table->string('order_id')->nullable()->after('booking_id');
            }
            
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                // transaction_id dari Midtrans (penting untuk tracking)
                $table->string('transaction_id')->nullable()->after('order_id');
            }
            
            if (!Schema::hasColumn('payments', 'gross_amount')) {
                // gross_amount: jumlah yang dikirim ke Midtrans
                $table->decimal('gross_amount', 10, 2)->nullable()->after('amount');
            }
            
            // Ubah status enum untuk mencakup status Midtrans yang lebih lengkap
            // pending: belum dibayar
            // settlement: pembayaran berhasil
            // expired: transaksi expired
            // failed: pembayaran gagal
            // cancel: transaksi dibatalkan
            $table->string('status')->default('pending')->change();
            
            // Ubah payment_method menjadi string untuk fleksibilitas dengan Midtrans
            $table->string('payment_method')->default('midtrans')->change();
            
            if (!Schema::hasColumn('payments', 'midtrans_response')) {
                // Kolom untuk menyimpan response dari Midtrans (JSON)
                $table->json('midtrans_response')->nullable()->after('order_id');
            }
            
            if (!Schema::hasColumn('payments', 'midtrans_signature_key')) {
                // Kolom untuk menyimpan signature key verifikasi (untuk security)
                $table->string('midtrans_signature_key')->nullable()->after('midtrans_response');
            }
            
            // Hapus kolom proof_file_path karena tidak lagi perlu dengan Midtrans
            // tetapi kita tidak drop, hanya allow nullable
            $table->string('proof_file_path')->nullable()->change();
            
            if (!Schema::hasColumn('payments', 'paid_at')) {
                // tambahan timestamp untuk payment completion
                $table->timestamp('paid_at')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'transaction_id', 'gross_amount', 'midtrans_response', 'midtrans_signature_key', 'paid_at']);
            
            // Restore status dan payment_method ke state sebelumnya
            $table->enum('status', ['pending', 'completed'])->default('pending')->change();
            $table->enum('payment_method', ['bank_transfer', 'qris', 'bca', 'bri', 'cash'])->default('bank_transfer')->change();
        });
    }
};
