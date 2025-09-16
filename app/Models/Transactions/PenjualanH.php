<?php

namespace App\Models\Transactions;

use App\Models\Master\Dokter;
use App\Models\Master\Pelanggan;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanH extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function rinci()
    {
        return $this->hasMany(PenjualanR::class, 'nopenjualan', 'nopenjualan');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode');
    }
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kode_dokter', 'kode');
    }
}
