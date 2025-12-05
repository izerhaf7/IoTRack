<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk peminjaman barang.
 * Mencatat setiap transaksi peminjaman barang oleh pengunjung.
 */
class Borrowing extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'visit_id', 
        'item_id', 
        'quantity', 
        'status', 
        'returned_at'
    ];

    /**
     * Cast atribut ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'returned_at' => 'datetime',
    ];

    /**
     * Relasi ke barang.
     * Menggunakan withTrashed untuk tetap menampilkan barang yang sudah dihapus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    /**
     * Relasi ke kunjungan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Cek apakah peminjaman masih aktif.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'dipinjam';
    }
}