<?php

namespace App\Models\OldApp\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldUser extends Model
{
    use HasFactory;
    protected $connection = 'eachy';
}
