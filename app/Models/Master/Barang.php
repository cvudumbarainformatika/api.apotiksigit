<?php

namespace App\Models\Master;

use App\Models\Transactions\MutasiRequest;
use App\Models\Transactions\Penerimaan_r;
use App\Models\Transactions\PenjualanR;
use App\Models\Transactions\Penyesuaian;
use App\Models\Transactions\ReturPembelian_r;
use App\Models\Transactions\ReturPenjualan_r;
use App\Models\Transactions\Stok;
use App\Models\Transactions\StokOpname;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function penjualanRinci()
    {
        return $this->hasMany(PenjualanR::class, 'kode_barang', 'kode');
    }
    public function returPenjualanRinci()
    {
        return $this->hasMany(ReturPenjualan_r::class, 'kode_barang', 'kode');
    }
    public function penerimaanRinci()
    {
        return $this->hasMany(Penerimaan_r::class, 'kode_barang', 'kode');
    }

    public function penyesuaian()
    {
        return $this->hasMany(Penyesuaian::class, 'kode_barang', 'kode');
    }
    public function stok()
    {
        return $this->hasOne(Stok::class, 'kode_barang', 'kode');
    }
    public function stoks()
    {
        return $this->hasMany(Stok::class, 'kode_barang', 'kode');
    }
    public function stokOpname()
    {
        return $this->hasMany(StokOpname::class, 'kode_barang', 'kode');
    }
    public function stokAwal()
    {
        return $this->hasMany(StokOpname::class, 'kode_barang', 'kode');
    }
    public function returPembelianRinci()
    {
        return $this->hasMany(ReturPembelian_r::class, 'kode_barang', 'kode');
    }
    public function mutasiMasuk()
    {
        return $this->hasMany(MutasiRequest::class, 'kode_barang', 'kode');
    }
    public function mutasiKeluar()
    {
        return $this->hasMany(MutasiRequest::class, 'kode_barang', 'kode');
    }
}
