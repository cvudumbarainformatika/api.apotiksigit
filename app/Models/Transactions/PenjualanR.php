<?php

namespace App\Models\Transactions;

use App\Models\Master\Barang;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanR extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    public function header()
    {
        return $this->hasOne(PenjualanH::class, 'nopenjualan', 'nopenjualan');
    }
    public function master()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode');
    }
}
