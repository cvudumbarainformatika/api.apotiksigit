<?php

namespace App\Models\OldApp\Transaktions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $connection = 'eachy';
}
