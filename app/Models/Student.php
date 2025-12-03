<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'nim',
        'name',
        'program_studi',
        'tahun_masuk',
        'angkatan',
    ];
}
