<?php

namespace App\Models\OldApp\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;
    protected $connection = 'eachy';
    protected $casts = [
        'infos' => 'array',
        'themes' => 'array',
        'menus' => 'array',
        'levels' => 'array',
    ];
}
