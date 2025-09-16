<?php

namespace App\Models\Transactions;

use App\Models\Master\Supplier;
use App\Models\Transactions\Penerimaan_h;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan_h extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function returPenjualan_r()
    {
        return $this->hasMany(ReturPenjualan_r::class, 'noretur', 'noretur');
    }

    public function penerimaan_h()
    {
        return $this->belongsTo(Penerimaan_h::class, 'nopenerimaan', 'nopenerimaan');
    }
}
