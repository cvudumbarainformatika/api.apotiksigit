<?php

namespace App\Models\Transactions;

use App\Models\Master\Supplier;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelian_h extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function rincian()
    {
        return $this->hasMany(ReturPembelian_r::class, 'noretur', 'noretur');
    }

    public function suplier()
    {
        return $this->hasOne(Supplier::class, 'kode', 'kode_supplier');
    }
}
