<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranHutangRinci extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];
}
