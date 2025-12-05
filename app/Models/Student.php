<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk data mahasiswa.
 * Menyimpan informasi mahasiswa yang dapat mengunjungi lab.
 */
class Student extends Model
{
    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nim',
        'name',
        'program_studi',
        'tahun_masuk',
        'angkatan',
    ];

    /**
     * Relasi ke kunjungan berdasarkan NIM.
     * Satu mahasiswa dapat memiliki banyak kunjungan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits()
    {
        return $this->hasMany(Visit::class, 'visitor_id', 'nim');
    }
}
