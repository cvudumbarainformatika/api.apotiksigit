<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSetoranRinci extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];
    public function header()
    {
        $this->belongsTo(PembayaranSetoran::class);
    }
}
