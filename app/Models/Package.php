<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'duration',
        'total_photos',
        'edited_photos',
        'includes',
        'suitable_for',
    ];
    protected $casts = [
        'includes' => 'array',
    ];
}
