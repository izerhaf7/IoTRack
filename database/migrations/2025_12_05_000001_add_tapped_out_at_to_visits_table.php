<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi.
     * Menambahkan kolom tapped_out_at untuk mencatat waktu Tap Out
     * dan indeks untuk optimasi performa query.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Kolom untuk mencatat waktu Tap Out (null jika belum Tap Out)
            $table->timestamp('tapped_out_at')->nullable()->after('status');
            
            // Indeks untuk optimasi performa query
            $table->index('visitor_id', 'idx_visitor_id');
            $table->index('created_at', 'idx_created_at');
            $table->index('tapped_out_at', 'idx_tapped_out');
        });
    }

    /**
     * Membatalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Hapus indeks terlebih dahulu
            $table->dropIndex('idx_visitor_id');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_tapped_out');
            
            // Hapus kolom tapped_out_at
            $table->dropColumn('tapped_out_at');
        });
    }
};
