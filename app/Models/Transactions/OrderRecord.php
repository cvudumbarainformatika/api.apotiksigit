<?php

namespace App\Models\Transactions;

use App\Models\Master\Barang;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRecord extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    // protect pemanggilan data pasti dengan order_header
    public function master()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode');
    }
    public function orderHeader()
    {
        return $this->belongsTo(OrderHeader::class, 'nomor_order', 'nomor_order');
    }
}
