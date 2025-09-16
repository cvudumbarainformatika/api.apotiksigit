<?php

namespace App\Models\Transactions;

use App\Models\Master\Barang;
use App\Models\Transactions\Penerimaan_r;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;


class ReturPenjualan_r extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function master()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode');
    }

    public function returPenjualan_h()
    {
        return $this->belongsTo(ReturPenjualan_h::class, 'noretur', 'noretur');
    }

    public function penerimaan_r()
    {
        return $this->belongsTo(Penerimaan_r::class, 'id_penerimaan_rinci', 'id');
    }
}
