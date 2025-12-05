<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi.
     * Menambahkan indeks untuk optimasi performa query pada tabel borrowings.
     */
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            // Indeks untuk query berdasarkan status (sering digunakan untuk filter peminjaman aktif)
            $table->index('status', 'idx_borrowings_status');
            
            // Indeks untuk query berdasarkan tanggal pembuatan
            $table->index('created_at', 'idx_borrowings_created_at');
        });
    }

    /**
     * Membatalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex('idx_borrowings_status');
            $table->dropIndex('idx_borrowings_created_at');
        });
    }
};
