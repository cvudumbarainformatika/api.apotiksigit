<?php

namespace App\Models\Transactions;

use App\Models\Master\Supplier;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerimaan_h extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function suplier()
    {
        return $this->hasOne(Supplier::class, 'kode', 'kode_suplier');
    }

    public function rincian()
    {
        return $this->hasMany(Penerimaan_r::class, 'nopenerimaan', 'nopenerimaan');
    }

    public function retur()
    {
        return $this->hasMany(ReturPembelian_h::class, 'nopenerimaan', 'nopenerimaan');
    }
}
