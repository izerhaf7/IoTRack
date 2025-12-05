<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk kunjungan lab.
 * Mencatat setiap sesi kunjungan mahasiswa ke lab.
 */
class Visit extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'visitor_name', 
        'visitor_id', 
        'purpose',
        'tapped_out_at',
    ];

    /**
     * Cast atribut ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tapped_out_at' => 'datetime',
    ];

    /**
     * Relasi ke peminjaman.
     * Satu kunjungan dapat memiliki banyak peminjaman.
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

    /**
     * Relasi ke mahasiswa berdasarkan NIM.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'visitor_id', 'nim');
    }

    /**
     * Cek apakah kunjungan sudah di-tap out.
     *
     * @return bool
     */
    public function isTappedOut(): bool
    {
        return $this->tapped_out_at !== null;
    }

    /**
     * Cek apakah kunjungan memiliki peminjaman aktif.
     *
     * @return bool
     */
    public function hasActiveBorrowings(): bool
    {
        return $this->borrowings()->where('status', 'dipinjam')->exists();
    }
}