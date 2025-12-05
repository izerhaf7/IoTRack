<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model untuk barang/peralatan lab.
 * Mengelola data inventaris dan stok barang yang dapat dipinjam.
 */
class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'image', 
        'total_stock', 
        'current_stock'
    ];

    /**
     * Relasi ke peminjaman.
     * Satu barang dapat memiliki banyak peminjaman.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Relasi ke peminjaman aktif saja.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)->where('status', 'dipinjam');
    }
}