<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSetoran extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];
    public function rinci()
    {
        return $this->hasMany(PembayaranSetoranRinci::class, 'pembayaran_setoran_id', 'id');
    }
}
