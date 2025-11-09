<?php

namespace App\Models\Transactions;

use App\Models\Master\Barang;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiRequest extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at', 'created_at'];

    public function header()
    {
        return $this->hasOne(MutasiRequest::class, 'kode_mutasi', 'kode_mutasi');
    }
    public function master()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode');
    }
    public function stok()
    {
        return $this->hasMany(Stok::class, 'kode_barang', 'kode_barang');
    }
    public function stokGudang()
    {
        return $this->hasMany(Stok::class, 'kode_barang', 'kode_barang');
    }
}
