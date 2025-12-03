<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image', 
        'total_stock', 
        'current_stock'
    ];
    use SoftDeletes;
}