<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiApproved extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
}
