<?php

namespace App\Models\Master;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at', 'created_at'];
    protected $casts = [
        'identitas' => 'array',
    ];
}
