<?php

namespace App\Models\Setting;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileToko extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $casts = [
        'header' => 'array',
        'footer' => 'array',
    ];
}
